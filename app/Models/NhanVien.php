<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Nhân viên. TRANGTHAI: 1 = đang làm việc, 0 = nghỉ việc.
 */
#[Table(name: 'NHANVIEN', key: 'MANV', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MANV', 'MACV', 'TENNV', 'SDT_NV', 'DIACHI_NV', 'EMAIL_NV', 'NGAYVAOLAM', 'TRANGTHAI'])]
class NhanVien extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAYVAOLAM' => 'date',
            'TRANGTHAI' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ChucVu, $this>
     */
    public function chucVu(): BelongsTo
    {
        return $this->belongsTo(ChucVu::class, 'MACV', 'MACV');
    }

    /**
     * @return HasOne<TaiKhoan, $this>
     */
    public function taiKhoan(): HasOne
    {
        return $this->hasOne(TaiKhoan::class, 'MANV', 'MANV');
    }

    /**
     * @return HasMany<HoaDon, $this>
     */
    public function hoaDons(): HasMany
    {
        return $this->hasMany(HoaDon::class, 'MANV', 'MANV');
    }

    /**
     * @return HasMany<PhieuNhapHang, $this>
     */
    public function phieuNhapHangs(): HasMany
    {
        return $this->hasMany(PhieuNhapHang::class, 'MANV', 'MANV');
    }
}
