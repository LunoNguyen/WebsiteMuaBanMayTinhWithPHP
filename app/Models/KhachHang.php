<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Khách hàng.
 */
#[Table(name: 'KHACHHANG', key: 'MAKH', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAKH', 'TENKH', 'SDT_KH', 'DIACHI_KH', 'EMAIL_KH', 'NGAYSINH', 'GIOITINH'])]
class KhachHang extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAYSINH' => 'date',
            'GIOITINH' => 'boolean',
        ];
    }

    /**
     * @return HasOne<TaiKhoan, $this>
     */
    public function taiKhoan(): HasOne
    {
        return $this->hasOne(TaiKhoan::class, 'MAKH', 'MAKH');
    }

    /**
     * @return HasMany<HoaDon, $this>
     */
    public function hoaDons(): HasMany
    {
        return $this->hasMany(HoaDon::class, 'MAKH', 'MAKH');
    }
}
