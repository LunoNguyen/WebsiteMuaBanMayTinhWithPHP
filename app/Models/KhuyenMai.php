<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Chương trình khuyến mãi / voucher.
 */
#[Table(name: 'KHUYENMAI', key: 'MAKM', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAKM', 'TENKM', 'MA_CODE', 'LOAI_KM', 'GIATRI_KM', 'SOTIENTOIDA_KM', 'SOTIENTOITHIEU_NHANKM', 'SOLUONG_MA', 'DA_SUDUNG', 'NGAYBD', 'NGAYKT', 'TRANGTHAI'])]
class KhuyenMai extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'GIATRI_KM' => 'decimal:2',
            'SOTIENTOIDA_KM' => 'decimal:2',
            'SOTIENTOITHIEU_NHANKM' => 'decimal:2',
            'SOLUONG_MA' => 'integer',
            'DA_SUDUNG' => 'integer',
            'NGAYBD' => 'datetime',
            'NGAYKT' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<SanPham, $this>
     */
    public function sanPhams(): BelongsToMany
    {
        return $this->belongsToMany(SanPham::class, 'CT_KHUYENMAI', 'MAKM', 'MASP');
    }
}
