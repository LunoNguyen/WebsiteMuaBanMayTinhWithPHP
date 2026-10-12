<?php

namespace Database\Seeders;

use App\Models\KhuyenMai;
use Illuminate\Database\Seeder;

class KhuyenMaiSeeder extends Seeder
{
    /**
     * Dữ liệu bảng KHUYENMAI (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        KhuyenMai::query()->insertOrIgnore([
            ['MAKM' => 'KM001', 'TENKM' => 'Giảm giá khai trương', 'MA_CODE' => 'KHAITUONG10', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '10.00', 'SOTIENTOIDA_KM' => '500000.00', 'SOTIENTOITHIEU_NHANKM' => '1000000.00', 'SOLUONG_MA' => 500, 'DA_SUDUNG' => 0, 'NGAYBD' => '2025-01-01 00:00:00', 'NGAYKT' => '2025-01-10 23:59:59', 'TRANGTHAI' => 'HetHan'],
            ['MAKM' => 'KM002', 'TENKM' => 'Giảm giá tháng 2', 'MA_CODE' => 'FEB2025', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '10.00', 'SOTIENTOIDA_KM' => '500000.00', 'SOTIENTOITHIEU_NHANKM' => '3000000.00', 'SOLUONG_MA' => null, 'DA_SUDUNG' => 0, 'NGAYBD' => '2025-02-01 00:00:00', 'NGAYKT' => '2025-02-28 23:59:59', 'TRANGTHAI' => 'HetHan'],
            ['MAKM' => 'KM003', 'TENKM' => 'Free ship', 'MA_CODE' => 'FREESHIP', 'LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => '40000.00', 'SOTIENTOIDA_KM' => null, 'SOTIENTOITHIEU_NHANKM' => '500000.00', 'SOLUONG_MA' => null, 'DA_SUDUNG' => 0, 'NGAYBD' => '2025-02-01 00:00:00', 'NGAYKT' => '2025-02-28 23:59:59', 'TRANGTHAI' => 'HetHan'],
            ['MAKM' => 'KM004', 'TENKM' => 'Giảm giá 8/3', 'MA_CODE' => 'NGAY8_3', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '15.00', 'SOTIENTOIDA_KM' => '1000000.00', 'SOTIENTOITHIEU_NHANKM' => '5000000.00', 'SOLUONG_MA' => 200, 'DA_SUDUNG' => 0, 'NGAYBD' => '2025-03-01 00:00:00', 'NGAYKT' => '2025-03-10 23:59:59', 'TRANGTHAI' => 'HetHan'],
            ['MAKM' => 'KM006', 'TENKM' => 'Black Friday', 'MA_CODE' => 'BLACKFRI50', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '50.00', 'SOTIENTOIDA_KM' => '10000000.00', 'SOTIENTOITHIEU_NHANKM' => '20000000.00', 'SOLUONG_MA' => 100, 'DA_SUDUNG' => 1, 'NGAYBD' => '2026-10-01 00:00:00', 'NGAYKT' => '2026-11-25 23:59:00', 'TRANGTHAI' => 'HoatDong'],
            ['MAKM' => 'KM009', 'TENKM' => 'Flash Sale 3/3', 'MA_CODE' => 'FLASH3_3', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '30.00', 'SOTIENTOIDA_KM' => '300000.00', 'SOTIENTOITHIEU_NHANKM' => '500000.00', 'SOLUONG_MA' => 50, 'DA_SUDUNG' => 0, 'NGAYBD' => '2025-03-03 10:00:00', 'NGAYKT' => '2025-03-03 12:00:00', 'TRANGTHAI' => 'HetHan'],
            ['MAKM' => 'KM011', 'TENKM' => 'Mừng RiCha rời đội tuyển', 'MA_CODE' => 'RICON', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => '5.00', 'SOTIENTOIDA_KM' => '2000000.00', 'SOTIENTOITHIEU_NHANKM' => '25000000.00', 'SOLUONG_MA' => 5, 'DA_SUDUNG' => 1, 'NGAYBD' => '2026-10-05 00:00:00', 'NGAYKT' => '2026-12-31 23:59:00', 'TRANGTHAI' => 'HoatDong'],
        ]);
    }
}
