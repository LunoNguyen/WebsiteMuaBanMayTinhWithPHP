<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VaiTro;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DangNhapRequest;
use App\Models\TaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class DangNhapController extends Controller
{
    /**
     * Số lần đăng nhập sai tối đa trong một phút cho mỗi email + IP.
     */
    private const SO_LAN_THU = 5;

    /**
     * Màn đăng nhập.
     */
    public function create(): View
    {
        return view('auth.dang-nhap');
    }

    /**
     * Đăng nhập bằng tài khoản trong bảng TAIKHOAN, rồi chuyển tới trang theo vai trò (loại tài khoản + chức vụ).
     */
    public function store(DangNhapRequest $request): RedirectResponse
    {
        if (RateLimiter::tooManyAttempts($request->throttleKey(), self::SO_LAN_THU)) {
            $giay = RateLimiter::availableIn($request->throttleKey());

            return back()->onlyInput('email')
                ->withErrors(['email' => "Đăng nhập sai quá nhiều lần. Thử lại sau {$giay} giây."]);
        }

        $taiKhoan = TaiKhoan::query()
            ->with('nhanVien.chucVu', 'khachHang')
            ->where('EMAIL_TK', $request->string('email'))
            ->first();

        // Cùng một câu báo cho sai email và sai mật khẩu, để không lộ email nào có trong hệ thống
        if ($taiKhoan === null || ! $taiKhoan->kiemTraMatKhau((string) $request->input('password'))) {
            RateLimiter::hit($request->throttleKey());

            return back()->onlyInput('email')->withErrors(['email' => 'Email hoặc mật khẩu không đúng!']);
        }

        RateLimiter::clear($request->throttleKey());

        if (! $taiKhoan->dangHoatDong()) {
            return back()->onlyInput('email')
                ->withErrors(['email' => 'Tài khoản đang bị khoá. Vui lòng liên hệ quản trị viên.']);
        }

        $vaiTro = $taiKhoan->vaiTro();

        if ($vaiTro === null) {
            return back()->onlyInput('email')->withErrors(['email' => $taiKhoan->lyDoKhongVaoDuoc()]);
        }

        if ($taiKhoan->canMaHoaLai()) {
            $taiKhoan->update(['MATKHAU' => Hash::make((string) $request->input('password'))]);
        }

        Auth::login($taiKhoan);
        $request->session()->regenerate();

        // Khách hàng quay lại trang đang xem trước khi đăng nhập (chỉ nhận đường dẫn nội bộ)
        $tiep = (string) $request->input('tiep', '');
        if ($vaiTro === VaiTro::KhachHang && str_starts_with($tiep, '/') && ! str_starts_with($tiep, '//')) {
            return redirect()->to($tiep);
        }

        return redirect()->intended(route($vaiTro->trangChu()));
    }

    /**
     * Đăng xuất.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
