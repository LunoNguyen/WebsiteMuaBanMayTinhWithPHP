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
     * Số đơn, tổng chi tiêu (đơn đã giao / hoàn thành) và lần mua gần nhất.
     *
     * @return array{so_don: int, tong_chi: float, lan_cuoi: ?string}
     */
    public function tomTatMuaHang(): array
    {
        $tong = $this->hoaDons()->toBase()
            ->selectRaw("COUNT(*) AS so_don, SUM(CASE WHEN TRANGTHAI IN ('DaGiao','HoanThanh') THEN TONGTIEN_HD ELSE 0 END) AS tong_chi, MAX(NGAYLAP) AS lan_cuoi")
            ->first();

        return [
            'so_don' => (int) $tong->so_don,
            'tong_chi' => (float) $tong->tong_chi,
            'lan_cuoi' => $tong->lan_cuoi,
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
