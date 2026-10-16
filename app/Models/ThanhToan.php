<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Thanh toán của hoá đơn (QR, COD, chuyển khoản, tiền mặt).
 */
#[Table(name: 'THANHTOAN', key: 'MATHANHTOAN', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MAHD', 'PHUONG_THUC', 'TRANGTHAI', 'SOTIEN', 'NGAY_THANHTOAN', 'MA_GIAODICH', 'NGAN_HANG', 'NOI_DUNG_CK', 'GHI_CHU'])]
class ThanhToan extends Model
{
    /**
     * Phương thức thanh toán hợp với hình thức nhận hàng: giao tận nơi trả khi nhận (COD),
     * nhận tại cửa hàng trả tại cửa hàng (TienMat); chuyển khoản / QR dùng được cho cả hai.
     *
     * @return list<string>
     */
    public static function phuongThucHopLe(?string $hinhThucNhan): array
    {
        return [$hinhThucNhan === 'TaiQuay' ? 'TienMat' : 'COD', 'ChuyenKhoan', 'QR'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'SOTIEN' => 'decimal:2',
            'NGAY_THANHTOAN' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<HoaDon, $this>
     */
    public function hoaDon(): BelongsTo
    {
        return $this->belongsTo(HoaDon::class, 'MAHD', 'MAHD');
    }
}
