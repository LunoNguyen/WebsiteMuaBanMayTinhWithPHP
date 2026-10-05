<?php

namespace App\Http\Controllers\BanHang;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KhachHangController extends Controller
{
    /**
     * Tổng chi tiêu tối thiểu để là khách VIP.
     */
    private const MUC_VIP = 50_000_000;

    /**
     * Danh sách khách hàng cho nhân viên bán hàng.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $tongChi = "SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH = kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')";

        $query = KhachHang::query()->toBase()
            ->from('KHACHHANG as kh')
            ->select('kh.*')
            ->selectSub('SELECT COUNT(*) FROM HOADON WHERE MAKH = kh.MAKH', 'so_hd')
            ->selectSub($tongChi, 'tong_chi')
            ->selectSub('SELECT MAX(NGAYLAP) FROM HOADON WHERE MAKH = kh.MAKH', 'lan_mua_cuoi')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('kh.MAKH', 'like', "%{$search}%")
                ->orWhere('kh.SDT_KH', 'like', "%{$search}%")
                ->orWhere('kh.EMAIL_KH', 'like', "%{$search}%")))
            ->orderByDesc('tong_chi')
            ->orderByDesc('so_hd')
            ->orderBy('kh.MAKH');

        $trang = $this->phanTrang($query, 12);

        return view('banhang.khachhang', [
            'khachhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'kpiTotal' => ['c' => KhachHang::query()->count()],
            'kpiMoi' => ['c' => TaiKhoan::query()->where('LOAI_TAIKHOAN', 'KhachHang')
                ->whereMonth('NGAYTAO', now()->month)->whereYear('NGAYTAO', now()->year)->count()],
            'kpiVip' => ['c' => KhachHang::query()->toBase()->from('KHACHHANG as kh')
                ->whereRaw("({$tongChi}) >= ?", [self::MUC_VIP])->count()],
            'colors' => ['#15803d', '#3a56e4', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'],
        ]);
    }

    /**
     * Hồ sơ khách hàng và lịch sử mua.
     */
    public function show(KhachHang $khachHang): View
    {
        return view('banhang.chi-tiet-khach', [
            'kh' => $khachHang->load('taiKhoan'),
            'tomTat' => $khachHang->tomTatMuaHang(),
            'donHang' => $khachHang->hoaDons()->with('thanhToan')->withCount('chiTiets')->latest('NGAYLAP')->limit(20)->get(),
        ]);
    }
}
