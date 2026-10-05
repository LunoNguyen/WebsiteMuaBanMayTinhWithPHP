<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Khuyến mãi đã áp vào hoá đơn. Khoá chính ghép (MAHD, MAKM).
 */
#[Table(name: 'HOADON_KHUYENMAI', key: 'MAHD', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAHD', 'MAKM', 'SOTIEN_GIAM'])]
class HoaDonKhuyenMai extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'SOTIEN_GIAM' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<HoaDon, $this>
     */
    public function hoaDon(): BelongsTo
    {
        return $this->belongsTo(HoaDon::class, 'MAHD', 'MAHD');
    }

    /**
     * @return BelongsTo<KhuyenMai, $this>
     */
    public function khuyenMai(): BelongsTo
    {
        return $this->belongsTo(KhuyenMai::class, 'MAKM', 'MAKM');
    }
}
