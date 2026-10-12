<?php

namespace Database\Seeders;

use App\Models\GioHang;
use Illuminate\Database\Seeder;

class GioHangSeeder extends Seeder
{
    /**
     * Dữ liệu bảng GIOHANG (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        GioHang::query()->insertOrIgnore([
            ['MAGIOHANG' => 1, 'MATK' => 'TK_KH003', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 2, 'MATK' => 'TK_KH004', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 3, 'MATK' => 'TK_KH005', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 4, 'MATK' => 'TK_KH006', 'NGAYTAO' => '2026-10-04 21:18:24', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 5, 'MATK' => 'TK_KH001', 'NGAYTAO' => '2026-10-05 20:31:56', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 6, 'MATK' => 'TK0111', 'NGAYTAO' => '2026-10-05 22:23:56', 'NGAY_CAPNHAT' => '2026-10-05 22:27:43', 'TRANGTHAI' => 'DaThanhToan'],
            ['MAGIOHANG' => 7, 'MATK' => 'TK0111', 'NGAYTAO' => '2026-10-05 22:28:21', 'NGAY_CAPNHAT' => null, 'TRANGTHAI' => 'DangMua'],
            ['MAGIOHANG' => 8, 'MATK' => 'TK0112', 'NGAYTAO' => '2026-10-06 19:18:40', 'NGAY_CAPNHAT' => '2026-10-06 19:27:51', 'TRANGTHAI' => 'DaThanhToan'],
            ['MAGIOHANG' => 9, 'MATK' => 'TK0112', 'NGAYTAO' => '2026-10-06 19:29:25', 'NGAY_CAPNHAT' => '2026-10-06 19:34:35', 'TRANGTHAI' => 'DaThanhToan'],
            ['MAGIOHANG' => 10, 'MATK' => 'TK0112', 'NGAYTAO' => '2026-10-07 16:56:43', 'NGAY_CAPNHAT' => '2026-10-07 16:56:58', 'TRANGTHAI' => 'DaThanhToan'],
        ]);
    }
}
