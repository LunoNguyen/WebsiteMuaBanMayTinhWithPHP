<?php

namespace App\Enums;

/**
 * Khu vực làm việc trong hệ thống quản lý, suy ra từ loại tài khoản và chức vụ.
 */
enum VaiTro: string
{
    case Admin = 'Admin';
    case NhanVienBan = 'NhanVienBan';
    case NhanVienKho = 'NhanVienKho';

    /**
     * Tên route trang chính của vai trò.
     */
    public function trangChu(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::NhanVienBan => 'banhang.donhang',
            self::NhanVienKho => 'kho.nhaphang',
        };
    }

    /**
     * Tên hiển thị của vai trò.
     */
    public function nhan(): string
    {
        return match ($this) {
            self::Admin => 'Quản trị viên',
            self::NhanVienBan => 'Nhân viên bán hàng',
            self::NhanVienKho => 'Nhân viên kho',
        };
    }
}
