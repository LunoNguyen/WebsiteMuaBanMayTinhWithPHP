<?php

use App\Http\Middleware\YeuCauVaiTro;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'vaitro' => YeuCauVaiTro::class,
        ]);

        // Cookie "theme" do JavaScript ghi (sáng / tối) nên không mã hoá
        $middleware->encryptCookies(except: ['theme']);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function (Request $request): string {
            $vaiTro = $request->user()?->vaiTro();

            if ($vaiTro === null) {
                // Đã đăng nhập nhưng không còn quyền: đăng xuất để trang đăng nhập không chuyển vòng
                Auth::logout();

                return route('login');
            }

            return route($vaiTro->trangChu());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
