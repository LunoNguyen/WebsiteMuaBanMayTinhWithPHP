<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AnhController;
use App\Http\Controllers\Auth\DangKyController;
use App\Http\Controllers\Auth\DangNhapController;
use App\Http\Controllers\BanHang;
use App\Http\Controllers\Kho;
use App\Http\Controllers\Shop;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cửa hàng: ai cũng xem được; mua hàng phải đăng nhập tài khoản khách hàng
|--------------------------------------------------------------------------
*/

Route::get('/', [Shop\TrangChuController::class, 'index'])->name('home');
Route::get('/anh/{duongDan}', AnhController::class)->where('duongDan', '.+')->name('anh');
Route::get('/san-pham', [Shop\SanPhamController::class, 'index'])->name('sanpham.index');
Route::get('/san-pham/{sanPham}', [Shop\SanPhamController::class, 'show'])->name('sanpham.show');
Route::post('/chatbot', [Shop\ChatbotController::class, 'store'])->middleware('throttle:20,1')->name('chatbot');

Route::middleware(['auth', 'vaitro:KhachHang'])->group(function () {
    Route::get('/gio-hang', [Shop\GioHangController::class, 'index'])->name('giohang.index');
    Route::post('/gio-hang/{sanPham}', [Shop\GioHangController::class, 'store'])->name('giohang.store');
    Route::patch('/gio-hang/dong/{ctGioHang}', [Shop\GioHangController::class, 'update'])->name('giohang.update');
    Route::delete('/gio-hang/dong/{ctGioHang}', [Shop\GioHangController::class, 'destroy'])->name('giohang.destroy');
    Route::post('/gio-hang-ma-giam-gia', [Shop\GioHangController::class, 'apMa'])->name('giohang.ap-ma');
    Route::delete('/gio-hang-ma-giam-gia', [Shop\GioHangController::class, 'boMa'])->name('giohang.bo-ma');

    Route::get('/thanh-toan', [Shop\ThanhToanController::class, 'create'])->name('thanhtoan.create');
    Route::post('/thanh-toan', [Shop\ThanhToanController::class, 'store'])->name('thanhtoan.store');
    Route::get('/thanh-toan/dang-xu-ly/{maYeuCau}', [Shop\ThanhToanController::class, 'dangXuLy'])->whereUuid('maYeuCau')->name('thanhtoan.dang-xu-ly');
    Route::get('/thanh-toan/trang-thai/{maYeuCau}', [Shop\ThanhToanController::class, 'trangThai'])->whereUuid('maYeuCau')->name('thanhtoan.trang-thai');
    Route::get('/thanh-toan/ket-qua/{maYeuCau}', [Shop\ThanhToanController::class, 'ketQua'])->whereUuid('maYeuCau')->name('thanhtoan.ket-qua');

    Route::get('/don-hang-cua-toi', [Shop\DonHangController::class, 'index'])->name('donhang.index');
    Route::get('/don-hang-cua-toi/{hoaDon}', [Shop\DonHangController::class, 'show'])->name('donhang.show');
    Route::patch('/don-hang-cua-toi/{hoaDon}/huy', [Shop\DonHangController::class, 'huy'])->name('donhang.huy');

    Route::get('/tai-khoan', [Shop\TaiKhoanController::class, 'edit'])->name('taikhoan.edit');
    Route::put('/tai-khoan', [Shop\TaiKhoanController::class, 'update'])->name('taikhoan.update');
    Route::put('/tai-khoan/mat-khau', [Shop\TaiKhoanController::class, 'doiMatKhau'])->name('taikhoan.mat-khau');
});

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
    Route::get('/nhan-vien/them', [Admin\NhanVienController::class, 'create'])->name('nhanvien.create');
    Route::post('/nhan-vien', [Admin\NhanVienController::class, 'store'])->name('nhanvien.store');
    Route::get('/nhan-vien/{nhanVien}/sua', [Admin\NhanVienController::class, 'edit'])->name('nhanvien.edit');
    Route::put('/nhan-vien/{nhanVien}', [Admin\NhanVienController::class, 'update'])->name('nhanvien.update');
    Route::delete('/nhan-vien/{nhanVien}', [Admin\NhanVienController::class, 'destroy'])->name('nhanvien.destroy');
    Route::patch('/nhan-vien/{nhanVien}/trang-thai', [Admin\NhanVienController::class, 'doiTrangThai'])->name('nhanvien.trang-thai');

    Route::get('/tai-khoan', [Admin\TaiKhoanController::class, 'index'])->name('taikhoan');
    Route::get('/tai-khoan/them', [Admin\TaiKhoanController::class, 'create'])->name('taikhoan.create');
    Route::post('/tai-khoan', [Admin\TaiKhoanController::class, 'store'])->name('taikhoan.store');
    Route::get('/tai-khoan/{taiKhoan}/sua', [Admin\TaiKhoanController::class, 'edit'])->name('taikhoan.edit');
    Route::put('/tai-khoan/{taiKhoan}', [Admin\TaiKhoanController::class, 'update'])->name('taikhoan.update');
    Route::delete('/tai-khoan/{taiKhoan}', [Admin\TaiKhoanController::class, 'destroy'])->name('taikhoan.destroy');
    Route::patch('/tai-khoan/{taiKhoan}/trang-thai', [Admin\TaiKhoanController::class, 'doiTrangThai'])->name('taikhoan.trang-thai');

    Route::get('/san-pham', [Admin\SanPhamController::class, 'index'])->name('sanpham');
    Route::get('/san-pham/them', [Admin\SanPhamController::class, 'create'])->name('sanpham.create');
    Route::post('/san-pham', [Admin\SanPhamController::class, 'store'])->name('sanpham.store');
    Route::get('/san-pham/{sanPham}/sua', [Admin\SanPhamController::class, 'edit'])->name('sanpham.edit');
    Route::put('/san-pham/{sanPham}', [Admin\SanPhamController::class, 'update'])->name('sanpham.update');
    Route::patch('/san-pham/{sanPham}/trang-thai', [Admin\SanPhamController::class, 'doiTrangThai'])->name('sanpham.trang-thai');
    Route::delete('/san-pham/{sanPham}', [Admin\SanPhamController::class, 'destroy'])->name('sanpham.destroy');
    Route::post('/san-pham/{sanPham}/anh', [Admin\SanPhamController::class, 'taiAnh'])->name('sanpham.anh');
    Route::patch('/san-pham/{sanPham}/anh/{anh}/chinh', [Admin\SanPhamController::class, 'datAnhChinh'])->name('sanpham.anh.chinh');
    Route::delete('/san-pham/{sanPham}/anh/{anh}', [Admin\SanPhamController::class, 'xoaAnh'])->name('sanpham.anh.xoa');

    Route::get('/nhap-hang', [Admin\NhapHangController::class, 'index'])->name('nhaphang');
    Route::get('/nhap-hang/them', [Admin\NhapHangController::class, 'create'])->name('nhaphang.create');
    Route::post('/nhap-hang', [Admin\NhapHangController::class, 'store'])->name('nhaphang.store');
    Route::get('/nhap-hang/{phieuNhapHang}', [Admin\NhapHangController::class, 'show'])->name('nhaphang.show');
    Route::get('/nhap-hang/{phieuNhapHang}/sua', [Admin\NhapHangController::class, 'edit'])->name('nhaphang.edit');
    Route::put('/nhap-hang/{phieuNhapHang}', [Admin\NhapHangController::class, 'update'])->name('nhaphang.update');
    Route::patch('/nhap-hang/{phieuNhapHang}/duyet', [Admin\NhapHangController::class, 'duyet'])->name('nhaphang.duyet');
    Route::patch('/nhap-hang/{phieuNhapHang}/huy', [Admin\NhapHangController::class, 'huy'])->name('nhaphang.huy');
    Route::delete('/nhap-hang/{phieuNhapHang}', [Admin\NhapHangController::class, 'destroy'])->name('nhaphang.destroy');

    Route::get('/khuyen-mai', [Admin\KhuyenMaiController::class, 'index'])->name('khuyenmai');
    Route::get('/khuyen-mai/them', [Admin\KhuyenMaiController::class, 'create'])->name('khuyenmai.create');
    Route::post('/khuyen-mai', [Admin\KhuyenMaiController::class, 'store'])->name('khuyenmai.store');
    Route::get('/khuyen-mai/{khuyenMai}/sua', [Admin\KhuyenMaiController::class, 'edit'])->name('khuyenmai.edit');
    Route::put('/khuyen-mai/{khuyenMai}', [Admin\KhuyenMaiController::class, 'update'])->name('khuyenmai.update');
    Route::patch('/khuyen-mai/{khuyenMai}/trang-thai', [Admin\KhuyenMaiController::class, 'doiTrangThai'])->name('khuyenmai.trang-thai');
    Route::delete('/khuyen-mai/{khuyenMai}', [Admin\KhuyenMaiController::class, 'destroy'])->name('khuyenmai.destroy');

    Route::get('/don-hang', [Admin\DonHangController::class, 'index'])->name('donhang');
    Route::get('/don-hang/them', [Admin\DonHangController::class, 'create'])->name('donhang.create');
    Route::post('/don-hang', [Admin\DonHangController::class, 'store'])->name('donhang.store');
    Route::get('/don-hang/{hoaDon}', [Admin\DonHangController::class, 'show'])->name('donhang.show');
    Route::patch('/don-hang/{hoaDon}/buoc-tiep-theo', [Admin\DonHangController::class, 'buocTiepTheo'])->name('donhang.buoc-tiep-theo');
    Route::patch('/don-hang/{hoaDon}/thanh-toan', [Admin\DonHangController::class, 'thanhToan'])->name('donhang.thanh-toan');
    Route::patch('/don-hang/{hoaDon}/huy', [Admin\DonHangController::class, 'huy'])->name('donhang.huy');

    Route::get('/khach-hang', [Admin\KhachHangController::class, 'index'])->name('khachhang');
    Route::get('/khach-hang/them', [Admin\KhachHangController::class, 'create'])->name('khachhang.create');
    Route::post('/khach-hang', [Admin\KhachHangController::class, 'store'])->name('khachhang.store');
    Route::get('/khach-hang/{khachHang}', [Admin\KhachHangController::class, 'show'])->name('khachhang.show');
    Route::get('/khach-hang/{khachHang}/sua', [Admin\KhachHangController::class, 'edit'])->name('khachhang.edit');
    Route::put('/khach-hang/{khachHang}', [Admin\KhachHangController::class, 'update'])->name('khachhang.update');
    Route::delete('/khach-hang/{khachHang}', [Admin\KhachHangController::class, 'destroy'])->name('khachhang.destroy');

    Route::get('/danh-muc', [Admin\DanhMucController::class, 'index'])->name('danhmuc');
    Route::post('/danh-muc/{bang}', [Admin\DanhMucController::class, 'store'])->name('danhmuc.store');
    Route::put('/danh-muc/{bang}/{ma}', [Admin\DanhMucController::class, 'update'])->name('danhmuc.update');
    Route::delete('/danh-muc/{bang}/{ma}', [Admin\DanhMucController::class, 'destroy'])->name('danhmuc.destroy');

    Route::get('/ho-so', [Admin\HoSoController::class, 'edit'])->name('hoso');
    Route::put('/ho-so', [Admin\HoSoController::class, 'update'])->name('hoso.update');
    Route::put('/ho-so/mat-khau', [Admin\HoSoController::class, 'doiMatKhau'])->name('hoso.mat-khau');

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
    Route::get('/nhap-hang/them', [Kho\NhapHangController::class, 'create'])->name('nhaphang.create');
    Route::post('/nhap-hang', [Kho\NhapHangController::class, 'store'])->name('nhaphang.store');
    Route::post('/phieu-nhap/{phieuNhapHang}/hoan-tat', [Kho\NhapHangController::class, 'hoanTat'])->name('nhaphang.hoan-tat');

    Route::get('/don-hang', [Kho\DonHangController::class, 'index'])->name('donhang');
    Route::get('/don-hang/{hoaDon}', [Kho\DonHangController::class, 'show'])->name('donhang.show');
    Route::patch('/don-hang/{hoaDon}/xuat-kho', [Kho\DonHangController::class, 'xuatKho'])->name('donhang.xuat-kho');
});

/*
|--------------------------------------------------------------------------
| Bán hàng (Admin cũng xem được)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'vaitro:NhanVienBan,Admin'])->prefix('ban-hang')->name('banhang.')->group(function () {
    Route::get('/', [BanHang\DonHangController::class, 'index'])->name('donhang');
    Route::get('/don-hang/{hoaDon}', [BanHang\DonHangController::class, 'show'])->name('donhang.show');
    Route::patch('/don-hang/{hoaDon}/buoc-tiep-theo', [BanHang\DonHangController::class, 'buocTiepTheo'])->name('donhang.buoc-tiep-theo');
    Route::patch('/don-hang/{hoaDon}/huy', [BanHang\DonHangController::class, 'huy'])->name('donhang.huy');

    Route::get('/khach-hang', [BanHang\KhachHangController::class, 'index'])->name('khachhang');
    Route::get('/khach-hang/{khachHang}', [BanHang\KhachHangController::class, 'show'])->name('khachhang.show');
});
