<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuTaiKhoanRequest;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\TaiKhoan;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $coSo = TaiKhoan::query()->toBase()
            ->from('TAIKHOAN as tk')
            ->leftJoin('KHACHHANG as kh', 'tk.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'tk.MANV', '=', 'nv.MANV')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('tk.MATK', 'like', "%{$search}%")
                ->orWhere('tk.MANV', $search)
                ->orWhere('tk.MAKH', $search)
                ->orWhere('tk.EMAIL_TK', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('nv.TENNV', 'like', "%{$search}%")))
            ->when($ttfil !== '', fn ($q) => $q->where('tk.TRANGTHAI', $ttfil));

        $query = (clone $coSo)
            ->leftJoin('CHUCVU as cv', 'nv.MACV', '=', 'cv.MACV')
            ->select('tk.MATK', 'tk.MANV', 'tk.MAKH', 'tk.EMAIL_TK', 'tk.LOAI_TAIKHOAN', 'tk.TRANGTHAI', 'tk.NGAYTAO', 'tk.NGAY_CAPNHAT',
                'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV', 'cv.TENCV')
            ->when($loaifil !== '', fn ($q) => $q->where('tk.LOAI_TAIKHOAN', $loaifil))
            ->orderByDesc('tk.NGAYTAO')
            ->orderBy('tk.MATK');

        $perPage = 12;
        $trang = $this->phanTrang($query, $perPage);
        $loaiNhan = ['Admin' => 'Quản trị', 'NhanVien' => 'Nhân viên', 'KhachHang' => 'Khách hàng'];

        return view('admin.taikhoan', [
            'taikhoan' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'dem' => $this->hangDemTheoCot($coSo, 'tk.LOAI_TAIKHOAN', $loaiNhan),
            'loaifil' => $loaifil,
            'ttfil' => $ttfil,
            'loaiIcon' => ['Admin' => 'star', 'NhanVien' => 'briefcase', 'KhachHang' => 'user'],
            'loaiNhan' => $loaiNhan,
            'loaiColor' => ['Admin' => 'var(--orange)', 'NhanVien' => 'var(--blue)', 'KhachHang' => 'var(--green)'],
            'ttColor' => ['HoatDong' => 'var(--green)', 'KhoaTamThoi' => 'var(--orange)', 'KhoaVinhVien' => 'var(--red)'],
            'ttLabel' => ['HoatDong' => 'Hoạt động', 'KhoaTamThoi' => 'Khoá tạm', 'KhoaVinhVien' => 'Khoá vĩnh viễn'],
        ]);
    }

    /**
     * Form tạo tài khoản cho nhân viên hoặc khách hàng chưa có tài khoản.
     */
    public function create(Request $request): View
    {
        return view('admin.taikhoan-form', [
            'tk' => new TaiKhoan([
                'LOAI_TAIKHOAN' => $request->query('makh') ? 'KhachHang' : 'NhanVien',
                'MANV' => $request->query('manv'),
                'MAKH' => $request->query('makh'),
                'TRANGTHAI' => 'HoatDong',
            ]),
            'nhanVienChuaCo' => NhanVien::query()->whereDoesntHave('taiKhoan')->orderBy('MANV')->get(),
            'khachHangChuaCo' => KhachHang::query()->whereDoesntHave('taiKhoan')->orderBy('MAKH')->get(),
        ]);
    }

    /**
     * Lưu tài khoản mới (mật khẩu được mã hoá bcrypt qua cast "hashed").
     */
    public function store(LuuTaiKhoanRequest $request): RedirectResponse
    {
        $loai = $request->validated('LOAI_TAIKHOAN');
        $laKhach = $loai === 'KhachHang';

        $taiKhoan = DB::transaction(fn (): TaiKhoan => TaiKhoan::create([
            'MATK' => $laKhach
                ? MaTuDong::tiepTheo('TAIKHOAN', 'MATK', 'TK_KH')
                : MaTuDong::tiepTheo('TAIKHOAN', 'MATK', $loai === 'Admin' ? 'TK_ADMIN' : 'TK_NV', $loai === 'Admin' ? 1 : 3),
            'MANV' => $laKhach ? null : $request->validated('MANV'),
            'MAKH' => $laKhach ? $request->validated('MAKH') : null,
            'EMAIL_TK' => $request->validated('EMAIL_TK'),
            'MATKHAU' => $request->validated('password'),
            'LOAI_TAIKHOAN' => $loai,
            'TRANGTHAI' => $request->validated('TRANGTHAI'),
        ]));

        return redirect()->route('admin.taikhoan')->with('thong_bao', "Đã tạo tài khoản {$taiKhoan->MATK} ({$taiKhoan->EMAIL_TK}).");
    }

    /**
     * Form sửa tài khoản.
     */
    public function edit(TaiKhoan $taiKhoan): View
    {
        return view('admin.taikhoan-form', [
            'tk' => $taiKhoan->load(['nhanVien.chucVu', 'khachHang']),
            'nhanVienChuaCo' => collect(),
            'khachHangChuaCo' => collect(),
        ]);
    }

    /**
     * Cập nhật email, trạng thái; nhập mật khẩu mới thì đặt lại mật khẩu.
     */
    public function update(LuuTaiKhoanRequest $request, TaiKhoan $taiKhoan): RedirectResponse
    {
        if ($taiKhoan->is($request->user()) && $request->validated('TRANGTHAI') !== 'HoatDong') {
            return back()->withInput()->with('thong_bao', 'Không thể tự khoá tài khoản đang đăng nhập!')->with('loai', 'danger');
        }

        $taiKhoan->fill($request->safe()->only(['EMAIL_TK', 'TRANGTHAI']));

        if ($request->filled('password')) {
            $taiKhoan->MATKHAU = $request->validated('password');
        }

        $taiKhoan->save();

        return redirect()->route('admin.taikhoan')->with('thong_bao', "Đã cập nhật tài khoản {$taiKhoan->MATK}.");
    }

    /**
     * Xoá tài khoản chưa phát sinh đơn hàng (giỏ hàng và phiên chat bị xoá theo); có đơn thì chỉ khoá được.
     */
    public function destroy(Request $request, TaiKhoan $taiKhoan): RedirectResponse
    {
        if ($taiKhoan->is($request->user())) {
            return back()->with('thong_bao', 'Không thể xoá tài khoản đang đăng nhập!')->with('loai', 'danger');
        }

        if (DB::table('HOADON')->where('MATK', $taiKhoan->MATK)->exists()) {
            return back()->with('thong_bao', "Tài khoản {$taiKhoan->MATK} đã có đơn hàng, không xoá được. Hãy khoá tài khoản.")->with('loai', 'danger');
        }

        DB::transaction(function () use ($taiKhoan): void {
            $gioHang = DB::table('GIOHANG')->where('MATK', $taiKhoan->MATK)->pluck('MAGIOHANG');
            DB::table('CT_GIOHANG')->whereIn('MAGIOHANG', $gioHang)->delete();
            DB::table('GIOHANG')->whereIn('MAGIOHANG', $gioHang)->delete();
            DB::table('PHIEN_CHATBOT')->where('MATK', $taiKhoan->MATK)->update(['MATK' => null]);
            $taiKhoan->delete();
        });

        return back()->with('thong_bao', "Đã xoá tài khoản {$taiKhoan->MATK}.");
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
