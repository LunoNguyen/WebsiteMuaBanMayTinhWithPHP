<?php

namespace Database\Seeders;

use App\Models\TaiKhoan;
use Illuminate\Database\Seeder;

class TaiKhoanSeeder extends Seeder
{
    /**
     * Dữ liệu bảng TAIKHOAN (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        TaiKhoan::query()->insertOrIgnore([
            ['MATK' => 'TK_ADMIN1', 'MANV' => 'NV001', 'MAKH' => null, 'EMAIL_TK' => 'admin@gmail.com', 'MATKHAU' => '$2y$12$lEpCs2T1OOsX7DEvGHzVUO/wgSX6zeecD.6d4YeqbSQgxrD6TtMAO', 'LOAI_TAIKHOAN' => 'Admin', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:26:04'],
            ['MATK' => 'TK_KH001', 'MANV' => null, 'MAKH' => 'KH001', 'EMAIL_TK' => 'nguyenvana@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:49'],
            ['MATK' => 'TK_KH002', 'MANV' => null, 'MAKH' => 'KH002', 'EMAIL_TK' => 'tranthib@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:50'],
            ['MATK' => 'TK_KH003', 'MANV' => null, 'MAKH' => 'KH003', 'EMAIL_TK' => 'leminhc@yahoo.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:51'],
            ['MATK' => 'TK_KH004', 'MANV' => null, 'MAKH' => 'KH004', 'EMAIL_TK' => 'phamvand@outlook.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:52'],
            ['MATK' => 'TK_KH005', 'MANV' => null, 'MAKH' => 'KH005', 'EMAIL_TK' => 'huynhthie@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:53'],
            ['MATK' => 'TK_KH006', 'MANV' => null, 'MAKH' => 'KH006', 'EMAIL_TK' => 'vovant@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:54'],
            ['MATK' => 'TK_KH007', 'MANV' => null, 'MAKH' => 'KH007', 'EMAIL_TK' => 'dothig@yahoo.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:55'],
            ['MATK' => 'TK_KH008', 'MANV' => null, 'MAKH' => 'KH008', 'EMAIL_TK' => 'phanminhh@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:57'],
            ['MATK' => 'TK_KH009', 'MANV' => null, 'MAKH' => 'KH009', 'EMAIL_TK' => 'truongvank@gmail.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:57'],
            ['MATK' => 'TK_KH010', 'MANV' => null, 'MAKH' => 'KH010', 'EMAIL_TK' => 'buihoangl@outlook.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:20:58'],
            ['MATK' => 'TK_NV001', 'MANV' => 'NV002', 'MAKH' => null, 'EMAIL_TK' => 'qly.banhang@gmail.com', 'MATKHAU' => '$2y$12$lEpCs2T1OOsX7DEvGHzVUO/wgSX6zeecD.6d4YeqbSQgxrD6TtMAO', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-06 22:14:11'],
            ['MATK' => 'TK_NV002', 'MANV' => 'NV003', 'MAKH' => null, 'EMAIL_TK' => 'qly.kho@gmail.com', 'MATKHAU' => '$2y$12$lEpCs2T1OOsX7DEvGHzVUO/wgSX6zeecD.6d4YeqbSQgxrD6TtMAO', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-06 22:14:18'],
            ['MATK' => 'TK_NV003', 'MANV' => 'NV004', 'MAKH' => null, 'EMAIL_TK' => 'banhang01@phongvu.com', 'MATKHAU' => '$2y$12$oWx/CAYo8TGJPn1Rx4OaFeePdxbMjl.vEbKyAYm.l3Qk4MmtKnVNS', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-06 19:16:46'],
            ['MATK' => 'TK_NV004', 'MANV' => 'NV005', 'MAKH' => null, 'EMAIL_TK' => 'thungan01@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:02'],
            ['MATK' => 'TK_NV005', 'MANV' => 'NV006', 'MAKH' => null, 'EMAIL_TK' => 'ketoan@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:03'],
            ['MATK' => 'TK_NV006', 'MANV' => 'NV007', 'MAKH' => null, 'EMAIL_TK' => 'giaohang01@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:04'],
            ['MATK' => 'TK_NV007', 'MANV' => 'NV008', 'MAKH' => null, 'EMAIL_TK' => 'it.support@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:05'],
            ['MATK' => 'TK_NV008', 'MANV' => 'NV009', 'MAKH' => null, 'EMAIL_TK' => 'cskh@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:06'],
            ['MATK' => 'TK_NV009', 'MANV' => 'NV010', 'MAKH' => null, 'EMAIL_TK' => 'baove@phongvu.com', 'MATKHAU' => '$2a$12$ngBWdwMILHo3KLOVBGkezenYiQFY.l1R1wLocX/79mj0sd5qxJDfm', 'LOAI_TAIKHOAN' => 'NhanVien', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => '2026-10-05 16:21:07'],
            ['MATK' => 'TK0111', 'MANV' => null, 'MAKH' => 'KH0011', 'EMAIL_TK' => 'nvl@gmail.com', 'MATKHAU' => '$2y$12$Z9f4Qzowmri7jcESdaiN/urZQH4DZYXFI.4L2cuRpaszPsFJhmDrq', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-05 22:23:05', 'NGAY_CAPNHAT' => null],
            ['MATK' => 'TK0112', 'MANV' => null, 'MAKH' => 'KH0012', 'EMAIL_TK' => 'nnl@gmail.com', 'MATKHAU' => '$2y$12$49LhiyC3XfwpW3sDd6xi5eV83DsEbhct3WEMCwcS08Y7eM6fOA/0u', 'LOAI_TAIKHOAN' => 'KhachHang', 'TRANGTHAI' => 'HoatDong', 'NGAYTAO' => '2026-10-06 19:18:11', 'NGAY_CAPNHAT' => null],
        ]);
    }
}
