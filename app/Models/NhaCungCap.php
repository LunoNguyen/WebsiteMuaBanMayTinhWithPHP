<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nhà cung cấp (FPT Synnex, Digiworld...).
 */
#[Table(name: 'NHACUNGCAP', key: 'MANCC', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MANCC', 'TENNCC', 'DIACHI_NCC', 'SDT_NCC', 'EMAIL_NCC'])]
class NhaCungCap extends Model
{
    /**
     * @return HasMany<SanPham, $this>
     */
    public function sanPhams(): HasMany
    {
        return $this->hasMany(SanPham::class, 'MANCC', 'MANCC');
    }

    /**
     * @return HasMany<PhieuNhapHang, $this>
     */
    public function phieuNhapHangs(): HasMany
    {
        return $this->hasMany(PhieuNhapHang::class, 'MANCC', 'MANCC');
    }
}
