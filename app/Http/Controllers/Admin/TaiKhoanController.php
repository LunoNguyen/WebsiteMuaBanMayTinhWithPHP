<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaiKhoanController extends Controller
{
    /**
     * Danh sách tài khoản (lọc theo từ khoá, loại, trạng thái).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $loaifil = (string) $request->query('loai', '');
        $ttfil = (string) $request->query('trangthai', '');

        $query = TaiKhoan::query()->toBase()
            ->from('TAIKHOAN as tk')
            ->leftJoin('KHACHHANG as kh', 'tk.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'tk.MANV', '=', 'nv.MANV')
            ->leftJoin('CHUCVU as cv', 'nv.MACV', '=', 'cv.MACV')
            ->select('tk.MATK', 'tk.MANV', 'tk.MAKH', 'tk.EMAIL_TK', 'tk.LOAI_TAIKHOAN', 'tk.TRANGTHAI', 'tk.NGAYTAO', 'tk.NGAY_CAPNHAT',
                'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV', 'cv.TENCV')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('tk.MATK', 'like', "%{$search}%")
                ->orWhere('tk.EMAIL_TK', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('nv.TENNV', 'like', "%{$search}%")))
            ->when($loaifil !== '', fn ($q) => $q->where('tk.LOAI_TAIKHOAN', $loaifil))
            ->when($ttfil !== '', fn ($q) => $q->where('tk.TRANGTHAI', $ttfil))
            ->orderByDesc('tk.NGAYTAO')
            ->orderBy('tk.MATK');

        $trang = $this->phanTrang($query, 12);

        return view('admin.taikhoan', [
            'taikhoan' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'loaifil' => $loaifil,
            'ttfil' => $ttfil,
            'typeMap' => TaiKhoan::query()->toBase()->selectRaw('LOAI_TAIKHOAN, COUNT(*) AS cnt')->groupBy('LOAI_TAIKHOAN')->pluck('cnt', 'LOAI_TAIKHOAN')->all(),
            'loaiIcon' => ['Admin' => 'star', 'NhanVien' => 'briefcase', 'KhachHang' => 'user'],
            'loaiColor' => ['Admin' => 'var(--orange)', 'NhanVien' => 'var(--blue)', 'KhachHang' => 'var(--green)'],
            'ttColor' => ['HoatDong' => 'var(--green)', 'KhoaTamThoi' => 'var(--orange)', 'KhoaVinhVien' => 'var(--red)'],
            'ttLabel' => ['HoatDong' => 'Hoạt động', 'KhoaTamThoi' => 'Khoá tạm', 'KhoaVinhVien' => 'Khoá vĩnh viễn'],
        ]);
    }

    /**
     * Khoá tạm / mở khoá tài khoản. Không cho tự khoá tài khoản đang đăng nhập.
     */
    public function doiTrangThai(Request $request, TaiKhoan $taiKhoan): RedirectResponse
    {
        if ($taiKhoan->is($request->user())) {
            return back()->with('thong_bao', 'Không thể tự khoá tài khoản đang đăng nhập!')->with('loai', 'danger');
        }

        $taiKhoan->update(['TRANGTHAI' => $taiKhoan->TRANGTHAI === 'HoatDong' ? 'KhoaTamThoi' : 'HoatDong']);

        return back()->with('thong_bao', 'Đã cập nhật trạng thái tài khoản!');
    }
}
