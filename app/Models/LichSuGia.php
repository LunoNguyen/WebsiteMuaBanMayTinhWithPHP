<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lịch sử thay đổi giá bán của sản phẩm.
 */
#[Table(name: 'LICHSUGIA', key: 'MALSG', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MASP', 'DONGIA_MOI', 'DONGIA_CU', 'NGAY_CAPNHAT', 'MANV_CAPNHAT', 'GHI_CHU'])]
class LichSuGia extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'DONGIA_MOI' => 'decimal:2',
            'DONGIA_CU' => 'decimal:2',
            'NGAY_CAPNHAT' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }

    /**
     * @return BelongsTo<NhanVien, $this>
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MANV_CAPNHAT', 'MANV');
    }
}
