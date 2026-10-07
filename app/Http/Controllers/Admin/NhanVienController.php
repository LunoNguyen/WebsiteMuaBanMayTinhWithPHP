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

        $trangthai = (string) $request->query('trangthai', '');

        $coSo = NhanVien::query()->toBase()
            ->from('NHANVIEN as nv')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nv.TENNV', 'like', "%{$search}%")
                ->orWhere('nv.MANV', 'like', "%{$search}%")
                ->orWhere('nv.EMAIL_NV', 'like', "%{$search}%")))
            ->when($macv !== '', fn ($q) => $q->where('nv.MACV', $macv));

        $query = (clone $coSo)
            ->leftJoin('CHUCVU as cv', 'nv.MACV', '=', 'cv.MACV')
            ->select('nv.*', 'cv.TENCV')
            ->selectSub('SELECT COUNT(*) FROM HOADON WHERE MANV = nv.MANV', 'so_hd')
            ->when($trangthai !== '', fn ($q) => $q->where('nv.TRANGTHAI', (int) $trangthai))
            ->orderByDesc('nv.NGAYVAOLAM')
            ->orderBy('nv.MANV');

        $perPage = 10;
        $trang = $this->phanTrang($query, $perPage);

        return view('admin.nhanvien', [
            'nhanvien' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'macv' => $macv,
            'trangthai' => $trangthai,
            'dem' => $this->hangDemTheoCot($coSo, 'nv.TRANGTHAI', ['1' => 'Đang làm việc', '0' => 'Đã nghỉ']),
            'chucvuList' => ChucVu::query()->orderBy('MACV')->pluck('TENCV', 'MACV')->all(),
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
