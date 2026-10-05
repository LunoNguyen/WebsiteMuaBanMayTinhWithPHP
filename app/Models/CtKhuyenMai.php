<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sản phẩm được áp dụng khuyến mãi. Khoá chính ghép (MASP, MAKM).
 */
#[Table(name: 'CT_KHUYENMAI', key: 'MAKM', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MASP', 'MAKM'])]
class CtKhuyenMai extends Model
{
    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }

    /**
     * @return BelongsTo<KhuyenMai, $this>
     */
    public function khuyenMai(): BelongsTo
    {
        return $this->belongsTo(KhuyenMai::class, 'MAKM', 'MAKM');
    }
}
