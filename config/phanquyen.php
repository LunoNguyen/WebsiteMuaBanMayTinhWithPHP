<?php

use App\Enums\VaiTro;

return [

    /*
    |--------------------------------------------------------------------------
    | Chức vụ được vào hệ thống
    |--------------------------------------------------------------------------
    |
    | NHANVIEN.MACV -> khu vực làm việc. Tài khoản loại "Admin" luôn vào trang
    | quản trị. Chức vụ không có trong danh sách thì không đăng nhập được.
    |
    */

    'chuc_vu' => [
        'CV001' => VaiTro::Admin,        // Giám đốc
        'CV002' => VaiTro::NhanVienBan,  // Quản lý bán hàng
        'CV004' => VaiTro::NhanVienBan,  // Nhân viên bán hàng
        'CV005' => VaiTro::NhanVienBan,  // Nhân viên thu ngân
        'CV009' => VaiTro::NhanVienBan,  // Chăm sóc khách hàng
        'CV003' => VaiTro::NhanVienKho,  // Quản lý kho
        'CV007' => VaiTro::NhanVienKho,  // Nhân viên giao hàng
    ],

];
