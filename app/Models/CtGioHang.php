<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sản phẩm trong giỏ hàng.
 */
#[Table(name: 'CT_GIOHANG', key: 'MACTGH', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MAGIOHANG', 'MASP', 'SOLUONG', 'DONGIA_LUCAT', 'NGAYTHEM'])]
class CtGioHang extends Model
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
            'NGAYTHEM' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<GioHang, $this>
     */
    public function gioHang(): BelongsTo
    {
        return $this->belongsTo(GioHang::class, 'MAGIOHANG', 'MAGIOHANG');
    }

    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
