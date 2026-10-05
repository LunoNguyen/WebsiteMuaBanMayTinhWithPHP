<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DangKyRequest;
use App\Models\KhachHang;
use App\Models\TaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DangKyController extends Controller
{
    /**
     * Màn đăng ký tài khoản khách hàng.
     */
    public function create(): View
    {
        return view('auth.dang-ky');
    }

    /**
     * Tạo khách hàng và tài khoản (mật khẩu mã hoá bcrypt qua cast "hashed" của model).
     */
    public function store(DangKyRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $soMoi = $this->soThuTuKhachHangMoi();

            $khachHang = KhachHang::create([
                'MAKH' => 'KH'.str_pad((string) $soMoi, 4, '0', STR_PAD_LEFT),
                'TENKH' => $request->string('hoten')->trim()->toString(),
                'SDT_KH' => $request->filled('sdt') ? $request->string('sdt')->trim()->toString() : null,
                'EMAIL_KH' => $request->string('email')->toString(),
            ]);

            TaiKhoan::create([
                'MATK' => 'TK'.str_pad((string) ($soMoi + 100), 4, '0', STR_PAD_LEFT),
                'MAKH' => $khachHang->MAKH,
                'EMAIL_TK' => $khachHang->EMAIL_KH,
                'MATKHAU' => (string) $request->input('password'),
                'LOAI_TAIKHOAN' => 'KhachHang',
                'TRANGTHAI' => 'HoatDong',
            ]);
        });

        return redirect()->route('register')->with('dang_ky_email', $request->string('email')->toString());
    }

    /**
     * Số thứ tự cho mã khách hàng mới: lớn nhất theo giá trị số (không theo chuỗi, vì "KH010" < "KH0011"
     * khi so chuỗi), khoá các dòng để hai lượt đăng ký cùng lúc không trùng mã.
     */
    private function soThuTuKhachHangMoi(): int
    {
        $lonNhat = (int) KhachHang::query()
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(MAKH, 3) AS UNSIGNED)) AS so')
            ->value('so');

        $so = $lonNhat + 1;

        while (KhachHang::query()->whereKey('KH'.str_pad((string) $so, 4, '0', STR_PAD_LEFT))->exists()
            || TaiKhoan::query()->whereKey('TK'.str_pad((string) ($so + 100), 4, '0', STR_PAD_LEFT))->exists()) {
            $so++;
        }

        return $so;
    }
}
