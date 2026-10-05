<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CapNhatHoSoRequest;
use App\Http\Requests\Shop\DoiMatKhauRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Hồ sơ của quản trị viên đang đăng nhập.
 */
class HoSoController extends Controller
{
    /**
     * Thông tin cá nhân và đổi mật khẩu.
     */
    public function edit(Request $request): View
    {
        return view('admin.hoso', [
            'taiKhoan' => $request->user(),
            'nv' => $request->user()->nhanVien?->load('chucVu'),
        ]);
    }

    /**
     * Cập nhật thông tin nhân viên gắn với tài khoản.
     */
    public function update(CapNhatHoSoRequest $request): RedirectResponse
    {
        abort_if($request->user()->nhanVien === null, 404);

        $request->user()->nhanVien->update($request->validated());

        return back()->with('thong_bao', 'Đã cập nhật hồ sơ.');
    }

    /**
     * Đổi mật khẩu (phải nhập đúng mật khẩu hiện tại).
     */
    public function doiMatKhau(DoiMatKhauRequest $request): RedirectResponse
    {
        $taiKhoan = $request->user();

        if (! $taiKhoan->kiemTraMatKhau((string) $request->input('mat_khau_cu'))) {
            return back()->withErrors(['mat_khau_cu' => 'Mật khẩu hiện tại không đúng.'], 'doiMatKhau');
        }

        $taiKhoan->update(['MATKHAU' => (string) $request->input('password')]);
        $request->session()->regenerate();

        return back()->with('thong_bao', 'Đã đổi mật khẩu.');
    }
}
