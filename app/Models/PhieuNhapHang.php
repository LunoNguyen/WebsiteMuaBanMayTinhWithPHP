<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phiếu nhập hàng từ nhà cung cấp.
 */
#[Table(name: 'PHIEUNHAPHANG', key: 'MAPNH', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAPNH', 'MANCC', 'MANV', 'NGAY_DATMUA', 'NGAYGIAO', 'NGAYNHAN', 'TRANGTHAI', 'TRANGTHAI_THANHTOAN', 'THUE_VAT', 'CHIETKHAU', 'TONGCONG_PNH', 'GHI_CHU', 'NGAYTAO'])]
class PhieuNhapHang extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAY_DATMUA' => 'datetime',
            'NGAYGIAO' => 'datetime',
            'NGAYNHAN' => 'datetime',
            'NGAYTAO' => 'datetime',
            'THUE_VAT' => 'decimal:2',
            'CHIETKHAU' => 'decimal:2',
            'TONGCONG_PNH' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<NhaCungCap, $this>
     */
    public function nhaCungCap(): BelongsTo
    {
        return $this->belongsTo(NhaCungCap::class, 'MANCC', 'MANCC');
    }

    /**
     * @return BelongsTo<NhanVien, $this>
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MANV', 'MANV');
    }

    /**
     * @return HasMany<CtPhieuNhapHang, $this>
     */
    public function chiTiets(): HasMany
    {
        return $this->hasMany(CtPhieuNhapHang::class, 'MAPNH', 'MAPNH');
    }
}
