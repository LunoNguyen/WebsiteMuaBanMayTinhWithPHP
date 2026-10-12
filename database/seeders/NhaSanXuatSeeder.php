<?php

namespace Database\Seeders;

use App\Models\NhaSanXuat;
use Illuminate\Database\Seeder;

class NhaSanXuatSeeder extends Seeder
{
    /**
     * Dữ liệu bảng NHASANXUA (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        NhaSanXuat::query()->insertOrIgnore([
            ['MANSX' => 'NSX001', 'TENNSX' => 'Apple', 'QUOCGIA' => 'Mỹ'],
            ['MANSX' => 'NSX002', 'TENNSX' => 'Dell', 'QUOCGIA' => 'Mỹ'],
            ['MANSX' => 'NSX003', 'TENNSX' => 'HP', 'QUOCGIA' => 'Mỹ'],
            ['MANSX' => 'NSX004', 'TENNSX' => 'Asus', 'QUOCGIA' => 'Đài Loan'],
            ['MANSX' => 'NSX005', 'TENNSX' => 'Lenovo', 'QUOCGIA' => 'Trung Quốc'],
            ['MANSX' => 'NSX006', 'TENNSX' => 'Samsung', 'QUOCGIA' => 'Hàn Quốc'],
            ['MANSX' => 'NSX007', 'TENNSX' => 'LG', 'QUOCGIA' => 'Hàn Quốc'],
            ['MANSX' => 'NSX008', 'TENNSX' => 'Logitech', 'QUOCGIA' => 'Thụy Sĩ'],
            ['MANSX' => 'NSX009', 'TENNSX' => 'Kingston', 'QUOCGIA' => 'Mỹ'],
            ['MANSX' => 'NSX010', 'TENNSX' => 'Gigabyte', 'QUOCGIA' => 'Đài Loan'],
        ]);
    }
}
