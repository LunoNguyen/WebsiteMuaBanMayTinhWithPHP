<?php

namespace Database\Seeders;

use App\Models\ThanhToan;
use Illuminate\Database\Seeder;

class ThanhToanSeeder extends Seeder
{
    /**
     * Dữ liệu bảng THANHTOAN (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        ThanhToan::query()->insertOrIgnore([
            ['MATHANHTOAN' => 1, 'MAHD' => 'HD001', 'PHUONG_THUC' => 'COD', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '28000000.00', 'NGAY_THANHTOAN' => '2025-02-02 10:30:00', 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 2, 'MAHD' => 'HD002', 'PHUONG_THUC' => 'QR', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '42500000.00', 'NGAY_THANHTOAN' => '2025-02-03 12:00:00', 'MA_GIAODICH' => 'GD20250203A', 'NGAN_HANG' => 'Vietcombank', 'NOI_DUNG_CK' => 'TT HD002 TRANTHIB', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 3, 'MAHD' => 'HD003', 'PHUONG_THUC' => 'QR', 'TRANGTHAI' => 'ChoThanhToan', 'SOTIEN' => '32000000.00', 'NGAY_THANHTOAN' => null, 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 4, 'MAHD' => 'HD004', 'PHUONG_THUC' => 'TienMat', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '15000000.00', 'NGAY_THANHTOAN' => '2025-02-10 09:30:00', 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 5, 'MAHD' => 'HD005', 'PHUONG_THUC' => 'TienMat', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '8500000.00', 'NGAY_THANHTOAN' => '2025-02-15 16:30:00', 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 6, 'MAHD' => 'HD006', 'PHUONG_THUC' => 'ChuyenKhoan', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '37000000.00', 'NGAY_THANHTOAN' => '2025-03-01 11:00:00', 'MA_GIAODICH' => 'GD20250301B', 'NGAN_HANG' => 'Techcombank', 'NOI_DUNG_CK' => 'TT HD006 HUYNHTHIE', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 7, 'MAHD' => 'HD007', 'PHUONG_THUC' => 'QR', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '13000000.00', 'NGAY_THANHTOAN' => '2025-03-05 11:30:00', 'MA_GIAODICH' => 'GD20250305C', 'NGAN_HANG' => 'BIDV', 'NOI_DUNG_CK' => 'TT HD007 VOVANT', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 8, 'MAHD' => 'HD008', 'PHUONG_THUC' => 'COD', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '52000000.00', 'NGAY_THANHTOAN' => '2026-10-06 19:05:53', 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 9, 'MAHD' => 'HD009', 'PHUONG_THUC' => 'QR', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '43000000.00', 'NGAY_THANHTOAN' => '2025-03-12 15:30:00', 'MA_GIAODICH' => 'GD20250312D', 'NGAN_HANG' => 'ACB', 'NOI_DUNG_CK' => 'TT HD009 PHANMINHH', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 10, 'MAHD' => 'HD010', 'PHUONG_THUC' => 'ChuyenKhoan', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '41000000.00', 'NGAY_THANHTOAN' => '2025-03-15 17:30:00', 'MA_GIAODICH' => 'GD20250315E', 'NGAN_HANG' => 'Vietinbank', 'NOI_DUNG_CK' => 'TT HD010 TRANTHIB', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 11, 'MAHD' => 'HD011', 'PHUONG_THUC' => 'QR', 'TRANGTHAI' => 'DaThanhToan', 'SOTIEN' => '485000000.00', 'NGAY_THANHTOAN' => '2026-10-05 22:28:25', 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => 'TT HD011', 'GHI_CHU' => null],
            ['MATHANHTOAN' => 12, 'MAHD' => 'HD012', 'PHUONG_THUC' => 'COD', 'TRANGTHAI' => 'ChoThanhToan', 'SOTIEN' => '39000000.00', 'NGAY_THANHTOAN' => null, 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 13, 'MAHD' => 'HD013', 'PHUONG_THUC' => 'COD', 'TRANGTHAI' => 'ChoThanhToan', 'SOTIEN' => '39000000.00', 'NGAY_THANHTOAN' => null, 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
            ['MATHANHTOAN' => 14, 'MAHD' => 'HD014', 'PHUONG_THUC' => 'COD', 'TRANGTHAI' => 'ChoThanhToan', 'SOTIEN' => '32000000.00', 'NGAY_THANHTOAN' => null, 'MA_GIAODICH' => null, 'NGAN_HANG' => null, 'NOI_DUNG_CK' => null, 'GHI_CHU' => null],
        ]);
    }
}
