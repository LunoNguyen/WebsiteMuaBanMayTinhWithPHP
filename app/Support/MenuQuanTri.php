<?php

namespace App\Support;

use App\Enums\VaiTro;
use App\Models\HoaDon;
use App\Models\PhieuNhapHang;
use Closure;

/**
 * Menu sidebar chung của khu quản trị, lọc theo vai trò.
 * Quản trị viên xem mọi khu; NV bán hàng chỉ Bán hàng; NV kho chỉ Kho.
 */
class MenuQuanTri
{
    /**
     * Nhóm => danh sách mục; mỗi mục ghi vai trò được thấy và route sẽ tô "đang chọn".
     *
     * @return array<string, list<array{ten: string, icon: string, route: string, dang_chon: list<string>, dem?: int}>>
     */
    public static function cho(VaiTro $vaiTro): array
    {
        $laAdmin = $vaiTro === VaiTro::Admin;

        $nhom = [
            'Tổng quan' => [
                self::muc(VaiTro::Admin, 'Tổng quan', 'dashboard', 'admin.dashboard'),
            ],
            'Bán hàng' => [
                $laAdmin
                    ? self::muc(VaiTro::Admin, 'Đơn hàng', 'cart', 'admin.donhang', ['admin.donhang*'], fn () => self::soDonChoXacNhan())
                    : self::muc(VaiTro::NhanVienBan, 'Đơn hàng', 'cart', 'banhang.donhang', ['banhang.donhang*'], fn () => self::soDonChoXacNhan()),
                $laAdmin
                    ? self::muc(VaiTro::Admin, 'Khách hàng', 'users', 'admin.khachhang', ['admin.khachhang*'])
                    : self::muc(VaiTro::NhanVienBan, 'Khách hàng', 'users', 'banhang.khachhang', ['banhang.khachhang*']),
                self::muc(VaiTro::Admin, 'Khuyến mãi', 'tag', 'admin.khuyenmai', ['admin.khuyenmai*']),
            ],
            'Kho' => [
                $laAdmin
                    ? self::muc(VaiTro::Admin, 'Nhập hàng', 'package', 'admin.nhaphang', ['admin.nhaphang*'], fn () => self::soPhieuCho())
                    : self::muc(VaiTro::NhanVienKho, 'Nhập hàng', 'package', 'kho.nhaphang', ['kho.nhaphang*'], fn () => self::soPhieuCho()),
                [...self::muc(VaiTro::NhanVienKho, 'Xuất kho', 'truck', 'kho.donhang', ['kho.donhang*'], fn () => self::soDonChoXuat()), 'cho_admin' => true],
            ],
            'Danh mục' => [
                self::muc(VaiTro::Admin, 'Sản phẩm', 'laptop', 'admin.sanpham', ['admin.sanpham*']),
                self::muc(VaiTro::Admin, 'Danh mục', 'folder', 'admin.danhmuc', ['admin.danhmuc*']),
            ],
            'Hệ thống' => [
                self::muc(VaiTro::Admin, 'Nhân viên', 'id-card', 'admin.nhanvien', ['admin.nhanvien*']),
                self::muc(VaiTro::Admin, 'Tài khoản', 'key', 'admin.taikhoan', ['admin.taikhoan*']),
                self::muc(VaiTro::Admin, 'Chatbot', 'bot', 'admin.chatbot', ['admin.chatbot*']),
                self::muc(VaiTro::Admin, 'Báo cáo', 'chart', 'admin.baocao', ['admin.baocao*']),
            ],
        ];

        $ketQua = [];
        foreach ($nhom as $ten => $mucs) {
            $duocThay = array_values(array_filter($mucs, fn (array $m): bool => $m['vai_tro'] === $vaiTro || ($laAdmin && ($m['cho_admin'] ?? false))));

            if ($duocThay !== []) {
                $ketQua[$ten] = array_map(function (array $m): array {
                    if (isset($m['dem'])) {
                        $m['dem'] = ($m['dem'])();
                    }
                    unset($m['vai_tro'], $m['cho_admin']);

                    return $m;
                }, $duocThay);
            }
        }

        return $ketQua;
    }

    /**
     * @param  list<string>  $dangChon
     * @return array<string, mixed>
     */
    private static function muc(VaiTro $vaiTro, string $ten, string $icon, string $route, array $dangChon = [], ?Closure $dem = null): array
    {
        return array_filter([
            'vai_tro' => $vaiTro,
            'ten' => $ten,
            'icon' => $icon,
            'route' => $route,
            'dang_chon' => $dangChon ?: [$route],
            'dem' => $dem,
        ], fn ($giaTri) => $giaTri !== null);
    }

    private static function soDonChoXacNhan(): int
    {
        return HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->count();
    }

    private static function soDonChoXuat(): int
    {
        return HoaDon::query()->where('TRANGTHAI', 'DaXacNhan')->count();
    }

    private static function soPhieuCho(): int
    {
        return PhieuNhapHang::query()->whereIn('TRANGTHAI', ['ChoDuyet', 'DaDuyet'])->count();
    }
}
