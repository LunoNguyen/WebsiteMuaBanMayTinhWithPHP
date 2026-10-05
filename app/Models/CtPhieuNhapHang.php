<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Chi tiết phiếu nhập hàng. Khoá chính ghép (MAPNH, MASP): cập nhật qua query builder.
 */
#[Table(name: 'CT_PHIEUNHAPHANG', key: 'MAPNH', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAPNH', 'MASP', 'SOLUONG', 'DONGIA_NHAP', 'THANHTIEN', 'GHI_CHU'])]
class CtPhieuNhapHang extends Model
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
            'DONGIA_NHAP' => 'decimal:2',
            'THANHTIEN' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PhieuNhapHang, $this>
     */
    public function phieuNhapHang(): BelongsTo
    {
        return $this->belongsTo(PhieuNhapHang::class, 'MAPNH', 'MAPNH');
    }

    /**
     * @return BelongsTo<SanPham, $this>
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
