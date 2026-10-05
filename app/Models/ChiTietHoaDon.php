<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Chi tiết hoá đơn. Khoá chính ghép (MAHD, MASP).
 */
#[Table(name: 'CHITIETHOADON', key: 'MAHD', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAHD', 'MASP', 'SOLUONG', 'DONGIA_LUCAT', 'THANHTIEN'])]
class ChiTietHoaDon extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'SOLUONG' => 'integer',
            'DONGIA_LUCAT' => 'decimal:2',
            'THANHTIEN' => 'decimal:2',
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
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
