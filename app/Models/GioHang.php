<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Giỏ hàng của tài khoản.
 */
#[Table(name: 'GIOHANG', key: 'MAGIOHANG', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MATK', 'NGAYTAO', 'TRANGTHAI'])]
class GioHang extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAYTAO' => 'datetime',
            'NGAY_CAPNHAT' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TaiKhoan, $this>
     */
    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'MATK', 'MATK');
    }

    /**
     * @return HasMany<CtGioHang, $this>
     */
    public function chiTiets(): HasMany
    {
        return $this->hasMany(CtGioHang::class, 'MAGIOHANG', 'MAGIOHANG');
    }
}
