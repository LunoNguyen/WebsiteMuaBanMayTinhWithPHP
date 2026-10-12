<?php

namespace Database\Seeders;

use App\Models\HoaDonKhuyenMai;
use Illuminate\Database\Seeder;

class HoaDonKhuyenMaiSeeder extends Seeder
{
    /**
     * Dữ liệu bảng HOADON_KHUYENMAI (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        HoaDonKhuyenMai::query()->insertOrIgnore([
            ['MAHD' => 'HD002', 'MAKM' => 'KM002', 'SOTIEN_GIAM' => '2500000.00'],
            ['MAHD' => 'HD006', 'MAKM' => 'KM004', 'SOTIEN_GIAM' => '1000000.00'],
            ['MAHD' => 'HD007', 'MAKM' => 'KM004', 'SOTIEN_GIAM' => '1000000.00'],
            ['MAHD' => 'HD011', 'MAKM' => 'KM006', 'SOTIEN_GIAM' => '10000000.00'],
            ['MAHD' => 'HD012', 'MAKM' => 'KM011', 'SOTIEN_GIAM' => '2000000.00'],
            ['MAHD' => 'HD013', 'MAKM' => 'KM011', 'SOTIEN_GIAM' => '2000000.00'],
        ]);
    }
}
