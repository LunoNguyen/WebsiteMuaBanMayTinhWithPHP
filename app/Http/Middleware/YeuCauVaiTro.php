<?php

namespace App\Http\Middleware;

use App\Models\TaiKhoan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chỉ cho tài khoản có vai trò phù hợp vào khu vực. Sai vai trò thì đưa về trang của mình;
 * tài khoản bị khoá hoặc mất quyền giữa chừng thì đăng xuất.
 */
class YeuCauVaiTro
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$vaiTros): Response
    {
        /** @var TaiKhoan|null $taiKhoan */
        $taiKhoan = $request->user();
        $vaiTro = $taiKhoan?->dangHoatDong() ? $taiKhoan->vaiTro() : null;

        if ($vaiTro === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $taiKhoan?->lyDoKhongVaoDuoc() ?? 'Tài khoản đang bị khoá hoặc không còn quyền truy cập.',
            ]);
        }

        if (! in_array($vaiTro->value, $vaiTros, true)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'error' => 'Bạn không có quyền thực hiện thao tác này.'], 403)
                : redirect()->route($vaiTro->trangChu());
        }

        return $next($request);
    }
}
