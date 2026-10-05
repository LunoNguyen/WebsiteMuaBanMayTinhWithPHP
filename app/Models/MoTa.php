<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Thông số kỹ thuật của sản phẩm.
 */
#[Table(name: 'MOTA', key: 'MAMT', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAMT', 'MASP', 'RAM', 'CPU', 'ROM', 'MANHINH', 'VGA', 'PIN', 'KHAC'])]
class MoTa extends Model
{
    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
