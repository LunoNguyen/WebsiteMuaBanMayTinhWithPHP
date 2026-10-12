<?php

namespace Database\Seeders;

use App\Models\SanPham;
use Illuminate\Database\Seeder;

class SanPhamSeeder extends Seeder
{
    /**
     * Dữ liệu bảng SANPHAM (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        SanPham::query()->insertOrIgnore([
            ['MASP' => 'SP001', 'MANCC' => 'NCC001', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX002', 'TENSP' => 'Laptop Dell XPS 13 9320', 'DONVT' => 'Cái', 'SOLUONGTON' => 50, 'DONGIA_SP' => '28000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-01-10 09:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP002', 'MANCC' => 'NCC001', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX001', 'TENSP' => 'MacBook Pro 14 inch M3', 'DONVT' => 'Cái', 'SOLUONGTON' => 19, 'DONGIA_SP' => '45000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-01-15 14:30:00', 'NGAY_CAPNHAT' => '2026-10-05 22:27:43'],
            ['MASP' => 'SP003', 'MANCC' => 'NCC002', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX004', 'TENSP' => 'Laptop ASUS ROG Zephyrus G14', 'DONVT' => 'Cái', 'SOLUONGTON' => 99, 'DONGIA_SP' => '32000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-02-01 08:00:00', 'NGAY_CAPNHAT' => '2026-10-07 16:56:58'],
            ['MASP' => 'SP004', 'MANCC' => 'NCC003', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX003', 'TENSP' => 'Laptop HP Pavilion 15', 'DONVT' => 'Cái', 'SOLUONGTON' => 200, 'DONGIA_SP' => '15000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-02-05 11:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP005', 'MANCC' => 'NCC004', 'MALOAI' => 'LSP002', 'MANSX' => 'NSX005', 'TENSP' => 'PC Lenovo IdeaCentre 3', 'DONVT' => 'Bộ', 'SOLUONGTON' => 300, 'DONGIA_SP' => '8500000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-02-10 16:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP006', 'MANCC' => 'NCC005', 'MALOAI' => 'LSP002', 'MANSX' => 'NSX004', 'TENSP' => 'PC Gaming ASUS ROG Strix G15', 'DONVT' => 'Bộ', 'SOLUONGTON' => 50, 'DONGIA_SP' => '38000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-03-01 09:30:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP007', 'MANCC' => 'NCC001', 'MALOAI' => 'LSP003', 'MANSX' => 'NSX003', 'TENSP' => 'AIO HP 24-cb1011d', 'DONVT' => 'Bộ', 'SOLUONGTON' => 150, 'DONGIA_SP' => '14000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-03-02 10:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP008', 'MANCC' => 'NCC002', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX002', 'TENSP' => 'Laptop Dell Precision 5570', 'DONVT' => 'Cái', 'SOLUONGTON' => 80, 'DONGIA_SP' => '52000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-03-03 11:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP009', 'MANCC' => 'NCC003', 'MALOAI' => 'LSP002', 'MANSX' => 'NSX003', 'TENSP' => 'PC Workstation HP Z2 G9', 'DONVT' => 'Bộ', 'SOLUONGTON' => 40, 'DONGIA_SP' => '43000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-03-04 14:00:00', 'NGAY_CAPNHAT' => null],
            ['MASP' => 'SP010', 'MANCC' => 'NCC004', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX005', 'TENSP' => 'Laptop Lenovo Legion 5 Pro', 'DONVT' => 'Cái', 'SOLUONGTON' => 19, 'DONGIA_SP' => '41000000.00', 'TRANGTHAI' => 'DangBan', 'NGAYTHEM' => '2025-03-05 15:00:00', 'NGAY_CAPNHAT' => '2026-10-06 19:34:35'],
        ]);
    }
}
