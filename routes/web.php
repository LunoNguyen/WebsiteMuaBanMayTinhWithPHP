<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\DangKyController;
use App\Http\Controllers\Auth\DangNhapController;
use App\Http\Controllers\BanHang;
use App\Http\Controllers\Kho;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dang-nhap');

/*
|--------------------------------------------------------------------------
| Đăng nhập, đăng ký
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [DangNhapController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [DangNhapController::class, 'store'])->name('login.store');
    Route::get('/dang-ky', [DangKyController::class, 'create'])->name('register');
    Route::post('/dang-ky', [DangKyController::class, 'store'])->name('register.store');
});

Route::post('/dang-xuat', [DangNhapController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Quản trị
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'vaitro:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/nhan-vien', [Admin\NhanVienController::class, 'index'])->name('nhanvien');
    Route::patch('/nhan-vien/{nhanVien}/trang-thai', [Admin\NhanVienController::class, 'doiTrangThai'])->name('nhanvien.trang-thai');

    Route::get('/tai-khoan', [Admin\TaiKhoanController::class, 'index'])->name('taikhoan');
    Route::patch('/tai-khoan/{taiKhoan}/trang-thai', [Admin\TaiKhoanController::class, 'doiTrangThai'])->name('taikhoan.trang-thai');

    Route::get('/san-pham', [Admin\SanPhamController::class, 'index'])->name('sanpham');
    Route::patch('/san-pham/{sanPham}/trang-thai', [Admin\SanPhamController::class, 'doiTrangThai'])->name('sanpham.trang-thai');
    Route::delete('/san-pham/{sanPham}', [Admin\SanPhamController::class, 'destroy'])->name('sanpham.destroy');
    Route::post('/san-pham/{sanPham}/anh', [Admin\SanPhamController::class, 'taiAnh'])->name('sanpham.anh');

    Route::get('/nhap-hang', [Admin\NhapHangController::class, 'index'])->name('nhaphang');

    Route::get('/khuyen-mai', [Admin\KhuyenMaiController::class, 'index'])->name('khuyenmai');
    Route::patch('/khuyen-mai/{khuyenMai}/trang-thai', [Admin\KhuyenMaiController::class, 'doiTrangThai'])->name('khuyenmai.trang-thai');
    Route::delete('/khuyen-mai/{khuyenMai}', [Admin\KhuyenMaiController::class, 'destroy'])->name('khuyenmai.destroy');

    Route::get('/don-hang', [Admin\DonHangController::class, 'index'])->name('donhang');
    Route::patch('/don-hang/{hoaDon}/buoc-tiep-theo', [Admin\DonHangController::class, 'buocTiepTheo'])->name('donhang.buoc-tiep-theo');
    Route::patch('/don-hang/{hoaDon}/huy', [Admin\DonHangController::class, 'huy'])->name('donhang.huy');

    Route::get('/khach-hang', [Admin\KhachHangController::class, 'index'])->name('khachhang');
    Route::get('/chatbot', [Admin\ChatbotController::class, 'index'])->name('chatbot');
    Route::get('/bao-cao', [Admin\BaoCaoController::class, 'index'])->name('baocao');

    Route::get('/thong-bao', [Admin\ThongBaoController::class, 'index'])->name('thongbao');
});

/*
|--------------------------------------------------------------------------
| Kho (Admin cũng xem được)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'vaitro:NhanVienKho,Admin'])->prefix('kho')->name('kho.')->group(function () {
    Route::get('/', [Kho\NhapHangController::class, 'index'])->name('nhaphang');
    Route::post('/phieu-nhap/{phieuNhapHang}/hoan-tat', [Kho\NhapHangController::class, 'hoanTat'])->name('nhaphang.hoan-tat');

    Route::get('/don-hang', [Kho\DonHangController::class, 'index'])->name('donhang');
    Route::patch('/don-hang/{hoaDon}/xuat-kho', [Kho\DonHangController::class, 'xuatKho'])->name('donhang.xuat-kho');
});

/*
|--------------------------------------------------------------------------
| Bán hàng (Admin cũng xem được)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'vaitro:NhanVienBan,Admin'])->prefix('ban-hang')->name('banhang.')->group(function () {
    Route::get('/', [BanHang\DonHangController::class, 'index'])->name('donhang');
    Route::patch('/don-hang/{hoaDon}/buoc-tiep-theo', [BanHang\DonHangController::class, 'buocTiepTheo'])->name('donhang.buoc-tiep-theo');
    Route::patch('/don-hang/{hoaDon}/huy', [BanHang\DonHangController::class, 'huy'])->name('donhang.huy');

    Route::get('/khach-hang', [BanHang\KhachHangController::class, 'index'])->name('khachhang');
});
