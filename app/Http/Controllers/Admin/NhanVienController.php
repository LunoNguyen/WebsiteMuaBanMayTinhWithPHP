<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuNhanVienRequest;
use App\Models\ChucVu;
use App\Models\NhanVien;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Form thêm nhân viên.
     */
    public function create(): View
    {
        return view('admin.nhanvien-form', [
            'nv' => new NhanVien(['TRANGTHAI' => true, 'NGAYVAOLAM' => today()]),
            'chucvuList' => ChucVu::query()->orderBy('MACV')->get(),
        ]);
    }

    /**
     * Lưu nhân viên mới.
     */
    public function store(LuuNhanVienRequest $request): RedirectResponse
    {
        $nhanVien = DB::transaction(fn (): NhanVien => NhanVien::create([
            'MANV' => MaTuDong::tiepTheo('NHANVIEN', 'MANV', 'NV'),
            ...$request->validated(),
        ]));

        return redirect()->route('admin.nhanvien')
            ->with('thong_bao', "Đã thêm nhân viên {$nhanVien->MANV}. Tạo tài khoản đăng nhập ở mục Tài khoản.");
    }

    /**
     * Form sửa nhân viên.
     */
    public function edit(NhanVien $nhanVien): View
    {
        return view('admin.nhanvien-form', [
            'nv' => $nhanVien,
            'chucvuList' => ChucVu::query()->orderBy('MACV')->get(),
        ]);
    }

    /**
     * Cập nhật nhân viên.
     */
    public function update(LuuNhanVienRequest $request, NhanVien $nhanVien): RedirectResponse
    {
        $nhanVien->update($request->validated());

        return redirect()->route('admin.nhanvien')->with('thong_bao', "Đã cập nhật nhân viên {$nhanVien->MANV}.");
    }

    /**
     * Xoá nhân viên chưa có tài khoản, hoá đơn hay phiếu nhập; còn dữ liệu thì chuyển nghỉ việc.
     */
    public function destroy(NhanVien $nhanVien): RedirectResponse
    {
        $conLienKet = $nhanVien->taiKhoan()->exists()
            || $nhanVien->hoaDons()->exists()
            || $nhanVien->phieuNhapHangs()->exists()
            || DB::table('LICHSUGIA')->where('MANV_CAPNHAT', $nhanVien->MANV)->exists();

        if ($conLienKet) {
            return back()->with('thong_bao', "Nhân viên {$nhanVien->MANV} đã có tài khoản hoặc chứng từ, không xoá được. Hãy chuyển sang nghỉ việc.")
                ->with('loai', 'danger');
        }

        $nhanVien->delete();

        return back()->with('thong_bao', "Đã xoá nhân viên {$nhanVien->MANV}.");
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
