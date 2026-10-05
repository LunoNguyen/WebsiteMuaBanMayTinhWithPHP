<?php

namespace App\Enums;

/**
 * Khu vực của tài khoản trong hệ thống, suy ra từ loại tài khoản và chức vụ.
 */
enum VaiTro: string
{
    case Admin = 'Admin';
    case NhanVienBan = 'NhanVienBan';
    case NhanVienKho = 'NhanVienKho';
    case KhachHang = 'KhachHang';

    /**
     * Tên route trang chính của vai trò.
     */
    public function trangChu(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::NhanVienBan => 'banhang.donhang',
            self::NhanVienKho => 'kho.nhaphang',
            self::KhachHang => 'home',
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
            self::KhachHang => 'Khách hàng',
        };
    }
}
