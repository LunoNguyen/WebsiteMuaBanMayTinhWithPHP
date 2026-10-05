<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChucVu;
use App\Models\NhanVien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NhanVienController extends Controller
{
    /**
     * Danh sách nhân viên (lọc theo tên/mã/email và chức vụ).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $macv = (string) $request->query('macv', '');

        $query = NhanVien::query()->toBase()
            ->from('NHANVIEN as nv')
            ->leftJoin('CHUCVU as cv', 'nv.MACV', '=', 'cv.MACV')
            ->select('nv.*', 'cv.TENCV')
            ->selectSub('SELECT COUNT(*) FROM HOADON WHERE MANV = nv.MANV', 'so_hd')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nv.TENNV', 'like', "%{$search}%")
                ->orWhere('nv.MANV', 'like', "%{$search}%")
                ->orWhere('nv.EMAIL_NV', 'like', "%{$search}%")))
            ->when($macv !== '', fn ($q) => $q->where('nv.MACV', $macv))
            ->orderByDesc('nv.NGAYVAOLAM')
            ->orderBy('nv.MANV');

        $trang = $this->phanTrang($query, 10);

        return view('admin.nhanvien', [
            'nhanvien' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'macv' => $macv,
            'chucvuList' => $this->mang(ChucVu::query()->toBase()->orderBy('MACV')->get()),
            'avatarColors' => ['#3a56e4', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'],
        ]);
    }

    /**
     * Đổi trạng thái đang làm việc / nghỉ việc.
     */
    public function doiTrangThai(NhanVien $nhanVien): RedirectResponse
    {
        $nhanVien->update(['TRANGTHAI' => ! $nhanVien->TRANGTHAI]);

        return back()->with('thong_bao', 'Đã cập nhật trạng thái nhân viên!');
    }
}
