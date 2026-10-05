<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nhà sản xuất (Apple, Dell, Asus...). Tên bảng trong CSDL là NHASANXUA.
 */
#[Table(name: 'NHASANXUA', key: 'MANSX', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MANSX', 'TENNSX', 'QUOCGIA'])]
class NhaSanXuat extends Model
{
    /**
     * @return HasMany<SanPham, $this>
     */
    public function sanPhams(): HasMany
    {
        return $this->hasMany(SanPham::class, 'MANSX', 'MANSX');
    }
}
