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
     * Còn bán và còn hàng.
     */
    public function conHang(): bool
    {
        return $this->TRANGTHAI === 'DangBan' && $this->SOLUONGTON > 0;
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
