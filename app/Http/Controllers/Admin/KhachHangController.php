<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KhachHangController extends Controller
{
    /**
     * Danh sách khách hàng, xếp theo tổng chi tiêu.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = KhachHang::query()->toBase()
            ->from('KHACHHANG as kh')
            ->select('kh.*')
            ->selectSub('SELECT COUNT(*) FROM HOADON WHERE MAKH = kh.MAKH', 'so_hd')
            ->selectSub("SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH = kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')", 'tong_chi_tieu')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('kh.MAKH', 'like', "%{$search}%")
                ->orWhere('kh.SDT_KH', 'like', "%{$search}%")
                ->orWhere('kh.EMAIL_KH', 'like', "%{$search}%")))
            ->orderByDesc('tong_chi_tieu')
            ->orderBy('kh.MAKH');

        $trang = $this->phanTrang($query, 10);

        return view('admin.khachhang', [
            'khachhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'offset' => $trang['offset'],
            'search' => $search,
            'avatarColors' => ['#3a56e4', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'],
        ]);
    }
}
