<?php

namespace App\Models;

use App\Services\MinioStorage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Sản phẩm (máy tính, linh kiện).
 */
#[Table(name: 'SANPHAM', key: 'MASP', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MASP', 'MANCC', 'MALOAI', 'MANSX', 'TENSP', 'DONVT', 'SOLUONGTON', 'DONGIA_SP', 'TRANGTHAI', 'NGAYTHEM'])]
class SanPham extends Model
{
    /**
     * Link xem ảnh đại diện trên MinIO (cần eager-load quan hệ "anhs"); rỗng nếu chưa có ảnh.
     */
    public function anhUrl(): string
    {
        $anh = $this->anhs->firstWhere('LA_ANH_CHINH', true) ?? $this->anhs->first();

        return app(MinioStorage::class)->url($anh?->URL_ANH);
    }

    /**
     * Cấu hình tóm tắt cho thẻ sản phẩm: CPU · RAM · ổ cứng · VGA (cần eager-load "moTa").
     *
     * @return list<string>
     */
    public function cauHinhTomTat(): array
    {
        return array_values(array_filter([$this->moTa?->CPU, $this->moTa?->RAM, $this->moTa?->ROM, $this->moTa?->VGA]));
    }

    /**
     * Đơn chưa xuất kho: hàng vẫn nằm trong kho (chưa trừ SOLUONGTON) nhưng đã được giữ cho khách.
     *
     * @var list<string>
     */
    public const GIU_HANG = ['ChoXacNhan', 'DaXacNhan'];

    /**
     * Số lượng đang giữ đã tính (không lưu vào attributes để save() không ghi cột không có thật).
     */
    private ?int $soLuongDangGiu = null;

    /**
     * Còn bán và còn hàng để bán (trừ phần đang giữ cho đơn chưa xuất kho).
     */
    public function conHang(): bool
    {
        return $this->TRANGTHAI === 'DangBan' && $this->soLuongCoTheBan() > 0;
    }

    /**
     * Số lượng đang giữ cho các đơn chưa xuất kho. Dùng giá trị nạp sẵn (scope coSoLuongDangGiu) nếu có.
     */
    public function soLuongDangGiu(): int
    {
        if (array_key_exists('so_luong_dang_giu', $this->attributes)) {
            return (int) $this->attributes['so_luong_dang_giu'];
        }

        return $this->soLuongDangGiu ??= (int) $this->chiTietHoaDons()
            ->whereHas('hoaDon', fn (Builder $q) => $q->whereIn('TRANGTHAI', self::GIU_HANG))
            ->sum('SOLUONG');
    }

    /**
     * Số lượng khách còn đặt được: tồn kho thực tế trừ phần đang giữ.
     */
    public function soLuongCoTheBan(): int
    {
        return max(0, (int) $this->SOLUONGTON - $this->soLuongDangGiu());
    }

    /**
     * Nạp sẵn số lượng đang giữ (so_luong_dang_giu) để danh sách không phải truy vấn từng sản phẩm.
     *
     * @param  Builder<SanPham>  $query
     */
    #[Scope]
    protected function coSoLuongDangGiu(Builder $query): void
    {
        $query->withSum(['chiTietHoaDons as so_luong_dang_giu' => fn (Builder $q) => $q
            ->whereHas('hoaDon', fn (Builder $h) => $h->whereIn('TRANGTHAI', self::GIU_HANG))], 'SOLUONG');
    }

    /**
     * @return HasMany<ChiTietHoaDon, $this>
     */
    public function chiTietHoaDons(): HasMany
    {
        return $this->hasMany(ChiTietHoaDon::class, 'MASP', 'MASP');
    }

    /**
     * Chỉ sản phẩm đang được bán.
     *
     * @param  Builder<SanPham>  $query
     */
    #[Scope]
    protected function dangBan(Builder $query): void
    {
        $query->where('TRANGTHAI', '!=', 'NgungBan');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'SOLUONGTON' => 'integer',
            'DONGIA_SP' => 'decimal:2',
            'NGAYTHEM' => 'datetime',
            'NGAY_CAPNHAT' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<LoaiSanPham, $this>
     */
    public function loaiSanPham(): BelongsTo
    {
        return $this->belongsTo(LoaiSanPham::class, 'MALOAI', 'MALOAI');
    }

    /**
     * @return BelongsTo<NhaSanXuat, $this>
     */
    public function nhaSanXuat(): BelongsTo
    {
        return $this->belongsTo(NhaSanXuat::class, 'MANSX', 'MANSX');
    }

    /**
     * @return BelongsTo<NhaCungCap, $this>
     */
    public function nhaCungCap(): BelongsTo
    {
        return $this->belongsTo(NhaCungCap::class, 'MANCC', 'MANCC');
    }

    /**
     * @return HasMany<DanhSachAnh, $this>
     */
    public function anhs(): HasMany
    {
        return $this->hasMany(DanhSachAnh::class, 'MASP', 'MASP');
    }

    /**
     * @return HasOne<MoTa, $this>
     */
    public function moTa(): HasOne
    {
        return $this->hasOne(MoTa::class, 'MASP', 'MASP');
    }

    /**
     * @return HasMany<LichSuGia, $this>
     */
    public function lichSuGias(): HasMany
    {
        return $this->hasMany(LichSuGia::class, 'MASP', 'MASP');
    }
}
