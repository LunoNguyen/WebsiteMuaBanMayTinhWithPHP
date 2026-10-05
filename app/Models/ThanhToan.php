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
