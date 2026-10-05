<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CapNhatTaiKhoanRequest;
use App\Http\Requests\Shop\DoiMatKhauRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaiKhoanController extends Controller
{
    /**
     * Thông tin cá nhân và đổi mật khẩu.
     */
    public function edit(Request $request): View
    {
        return view('shop.tai-khoan', [
            'taiKhoan' => $request->user(),
            'khachHang' => $request->user()->khachHang,
        ]);
    }

    /**
     * Cập nhật thông tin khách hàng (email đăng nhập giữ nguyên).
     */
    public function update(CapNhatTaiKhoanRequest $request): RedirectResponse
    {
        $request->user()->khachHang->update($request->validated());

        return back()->with('thong_bao', 'Đã cập nhật thông tin cá nhân.');
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
