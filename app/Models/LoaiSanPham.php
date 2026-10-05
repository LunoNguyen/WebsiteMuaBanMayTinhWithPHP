<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Loại sản phẩm (Laptop, PC, Màn hình...).
 */
#[Table(name: 'LOAISANPHAM', key: 'MALOAI', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MALOAI', 'TENLOAI'])]
class LoaiSanPham extends Model
{
    /**
     * @return HasMany<SanPham, $this>
     */
    public function sanPhams(): HasMany
    {
        return $this->hasMany(SanPham::class, 'MALOAI', 'MALOAI');
    }
}
