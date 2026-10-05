<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Hoá đơn bán hàng.
 */
#[Table(name: 'HOADON', key: 'MAHD', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAHD', 'MATK', 'MANV', 'MAKH', 'NGAYLAP', 'TRANGTHAI', 'TONGTIEN_TRUOCGIAM', 'TONGTIEN_GIAM', 'TONGTIEN_HD', 'TEN_NGUOINHAN', 'SDT_NGUOINHAN', 'DIACHI_GIAOHANG', 'PHUONG_THUC_GH', 'GHI_CHU'])]
class HoaDon extends Model
{
    /**
     * Trạng thái kế tiếp của đơn theo quy trình xử lý.
     *
     * @var array<string, string>
     */
    public const BUOC_TIEP_THEO = [
        'ChoXacNhan' => 'DaXacNhan',
        'DaXacNhan' => 'DangGiao',
        'DangGiao' => 'DaGiao',
        'DaGiao' => 'HoanThanh',
    ];

    /**
     * Nhãn nút chuyển sang bước kế tiếp.
     *
     * @var array<string, string>
     */
    public const NHAN_BUOC_TIEP_THEO = [
        'ChoXacNhan' => 'Xác nhận',
        'DaXacNhan' => 'Bắt đầu giao',
        'DangGiao' => 'Đã giao',
        'DaGiao' => 'Hoàn thành',
    ];

    /**
     * Trạng thái còn huỷ được.
     *
     * @var list<string>
     */
    public const HUY_DUOC = ['ChoXacNhan', 'DaXacNhan'];

    /**
     * Chuyển đơn sang bước kế tiếp. Khi đơn đã giao, thanh toán COD đang chờ được ghi nhận đã trả.
     */
    public function chuyenBuocTiepTheo(): bool
    {
        $tiepTheo = self::BUOC_TIEP_THEO[$this->TRANGTHAI] ?? null;

        if ($tiepTheo === null) {
            return false;
        }

        DB::transaction(function () use ($tiepTheo): void {
            $this->update(['TRANGTHAI' => $tiepTheo]);

            if ($tiepTheo === 'DaGiao') {
                $this->thanhToan()
                    ->where('PHUONG_THUC', 'COD')
                    ->where('TRANGTHAI', 'ChoThanhToan')
                    ->update(['TRANGTHAI' => 'DaThanhToan', 'NGAY_THANHTOAN' => now()]);
            }
        });

        return true;
    }

    /**
     * Huỷ đơn khi đơn còn ở bước chờ / đã xác nhận.
     */
    public function huy(): bool
    {
        if (! in_array($this->TRANGTHAI, self::HUY_DUOC, true)) {
            return false;
        }

        return $this->update(['TRANGTHAI' => 'DaHuy']);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAYLAP' => 'datetime',
            'NGAY_CAPNHAT' => 'datetime',
            'TONGTIEN_TRUOCGIAM' => 'decimal:2',
            'TONGTIEN_GIAM' => 'decimal:2',
            'TONGTIEN_HD' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<KhachHang, $this>
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    /**
     * @return BelongsTo<NhanVien, $this>
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MANV', 'MANV');
    }

    /**
     * @return BelongsTo<TaiKhoan, $this>
     */
    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'MATK', 'MATK');
    }

    /**
     * @return HasMany<ChiTietHoaDon, $this>
     */
    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietHoaDon::class, 'MAHD', 'MAHD');
    }

    /**
     * @return HasOne<ThanhToan, $this>
     */
    public function thanhToan(): HasOne
    {
        return $this->hasOne(ThanhToan::class, 'MAHD', 'MAHD');
    }
}
