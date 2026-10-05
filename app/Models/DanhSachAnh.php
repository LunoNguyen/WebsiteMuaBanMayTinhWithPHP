<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ảnh sản phẩm. URL_ANH là khoá object trên MinIO.
 */
#[Table(name: 'DANHSACHANH', key: 'MAANH', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MASP', 'URL_ANH', 'LA_ANH_CHINH', 'THU_TU'])]
class DanhSachAnh extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'LA_ANH_CHINH' => 'boolean',
            'THU_TU' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
