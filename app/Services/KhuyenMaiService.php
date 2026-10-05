<?php

namespace App\Services;

use App\Models\CtGioHang;
use App\Models\KhuyenMai;
use App\Models\TaiKhoan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Kiểm tra và tính tiền giảm của mã khuyến mãi cho một giỏ hàng.
 */
class KhuyenMaiService
{
    /**
     * Khuyến mãi còn hiệu lực ở thời điểm hiện tại (trạng thái, thời hạn, số lượt).
     * Có tài khoản thì bỏ các mã khách đó đã dùng.
     *
     * @return Collection<int, KhuyenMai>
     */
    public function dangApDung(?TaiKhoan $taiKhoan = null): Collection
    {
        $ds = KhuyenMai::query()
            ->where('TRANGTHAI', 'HoatDong')
            ->where(fn ($q) => $q->whereNull('NGAYBD')->orWhere('NGAYBD', '<=', now()))
            ->where(fn ($q) => $q->whereNull('NGAYKT')->orWhere('NGAYKT', '>=', now()))
            ->where(fn ($q) => $q->whereNull('SOLUONG_MA')->orWhereColumn('DA_SUDUNG', '<', 'SOLUONG_MA'))
            ->orderBy('NGAYKT')
            ->get();

        return $taiKhoan === null
            ? $ds
            : $ds->reject(fn (KhuyenMai $khuyenMai): bool => $this->daDung($khuyenMai, $taiKhoan->MATK, $taiKhoan->MAKH))->values();
    }

    /**
     * Tìm mã và tính số tiền giảm cho các dòng giỏ. Sai điều kiện thì báo lỗi theo từng trường hợp.
     * Mỗi khách chỉ dùng một mã một lần (đơn đã huỷ thì được dùng lại); truyền tài khoản hoặc mã khách để kiểm tra.
     *
     * @param  Collection<int, CtGioHang>  $dong
     * @return array{khuyenMai: KhuyenMai, giam: float}
     */
    public function apDung(string $maCode, Collection $dong, ?TaiKhoan $taiKhoan = null, ?string $maKhachHang = null): array
    {
        $khuyenMai = KhuyenMai::query()->with('sanPhams:MASP')->where('MA_CODE', trim($maCode))->first();

        $loi = match (true) {
            $khuyenMai === null => 'Mã giảm giá không tồn tại.',
            $khuyenMai->TRANGTHAI !== 'HoatDong' => 'Mã giảm giá đang tạm dừng hoặc đã hết hạn.',
            $khuyenMai->NGAYBD !== null && $khuyenMai->NGAYBD->isFuture() => 'Mã giảm giá chưa đến thời gian áp dụng.',
            $khuyenMai->NGAYKT !== null && $khuyenMai->NGAYKT->isPast() => 'Mã giảm giá đã hết hạn.',
            $khuyenMai->SOLUONG_MA !== null && $khuyenMai->DA_SUDUNG >= $khuyenMai->SOLUONG_MA => 'Mã giảm giá đã hết lượt sử dụng.',
            $this->daDung($khuyenMai, $taiKhoan?->MATK, $maKhachHang ?? $taiKhoan?->MAKH) => 'Bạn đã dùng mã này cho một đơn trước đó. Mỗi khách chỉ được dùng mỗi mã một lần.',
            default => null,
        };

        if ($loi !== null) {
            throw ValidationException::withMessages(['ma_code' => $loi]);
        }

        // Mã gắn với sản phẩm cụ thể (CT_KHUYENMAI) chỉ tính trên các sản phẩm đó
        $apDungCho = $khuyenMai->sanPhams->pluck('MASP');
        $dongApDung = $apDungCho->isEmpty() ? $dong : $dong->whereIn('MASP', $apDungCho->all());
        $tienApDung = (float) $dongApDung->sum(fn (CtGioHang $ct): float => $ct->SOLUONG * (float) $ct->sanPham->DONGIA_SP);

        if ($tienApDung <= 0) {
            throw ValidationException::withMessages(['ma_code' => 'Giỏ hàng không có sản phẩm được áp dụng mã này.']);
        }

        if ($tienApDung < (float) $khuyenMai->SOTIENTOITHIEU_NHANKM) {
            throw ValidationException::withMessages([
                'ma_code' => 'Đơn hàng cần tối thiểu '.formatVND($khuyenMai->SOTIENTOITHIEU_NHANKM).' để dùng mã này.',
            ]);
        }

        $giam = $khuyenMai->LOAI_KM === 'PhanTram'
            ? $tienApDung * (float) $khuyenMai->GIATRI_KM / 100
            : (float) $khuyenMai->GIATRI_KM;

        if ($khuyenMai->SOTIENTOIDA_KM !== null) {
            $giam = min($giam, (float) $khuyenMai->SOTIENTOIDA_KM);
        }

        return ['khuyenMai' => $khuyenMai, 'giam' => round(min($giam, $tienApDung))];
    }

    /**
     * Khách (theo tài khoản hoặc mã khách hàng) đã có đơn chưa huỷ dùng mã này chưa.
     */
    public function daDung(KhuyenMai $khuyenMai, ?string $maTaiKhoan, ?string $maKhachHang): bool
    {
        if ($maTaiKhoan === null && $maKhachHang === null) {
            return false;
        }

        return DB::table('HOADON_KHUYENMAI as hk')
            ->join('HOADON as hd', 'hd.MAHD', '=', 'hk.MAHD')
            ->where('hk.MAKM', $khuyenMai->MAKM)
            ->where('hd.TRANGTHAI', '!=', 'DaHuy')
            ->where(fn ($q) => $q
                ->when($maTaiKhoan !== null, fn ($w) => $w->orWhere('hd.MATK', $maTaiKhoan))
                ->when($maKhachHang !== null, fn ($w) => $w->orWhere('hd.MAKH', $maKhachHang)))
            ->exists();
    }

    /**
     * Mô tả điều kiện của mã để hiện cho khách: đơn tối thiểu, giảm tối đa, sản phẩm áp dụng, hạn dùng.
     *
     * @return list<string>
     */
    public function dieuKien(KhuyenMai $khuyenMai): array
    {
        $soSanPham = $khuyenMai->relationLoaded('sanPhams') ? $khuyenMai->sanPhams->count() : $khuyenMai->sanPhams()->count();

        return array_values(array_filter([
            (float) $khuyenMai->SOTIENTOITHIEU_NHANKM > 0 ? 'Đơn từ '.formatVND($khuyenMai->SOTIENTOITHIEU_NHANKM) : null,
            $khuyenMai->SOTIENTOIDA_KM !== null ? 'Giảm tối đa '.formatVND($khuyenMai->SOTIENTOIDA_KM) : null,
            $soSanPham > 0 ? "Áp dụng cho {$soSanPham} sản phẩm" : null,
            $khuyenMai->NGAYKT ? 'HSD '.$khuyenMai->NGAYKT->format('d/m/Y') : null,
            'Mỗi khách dùng 1 lần',
        ]));
    }
}
