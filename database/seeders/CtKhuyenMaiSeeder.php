<?php

namespace Database\Seeders;

use App\Models\CtKhuyenMai;
use Illuminate\Database\Seeder;

class CtKhuyenMaiSeeder extends Seeder
{
    /**
     * Dữ liệu bảng CT_KHUYENMAI (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        CtKhuyenMai::query()->insertOrIgnore([
            ['MASP' => 'SP001', 'MAKM' => 'KM011'],
            ['MASP' => 'SP002', 'MAKM' => 'KM006'],
            ['MASP' => 'SP002', 'MAKM' => 'KM011'],
            ['MASP' => 'SP003', 'MAKM' => 'KM002'],
            ['MASP' => 'SP003', 'MAKM' => 'KM011'],
            ['MASP' => 'SP004', 'MAKM' => 'KM011'],
            ['MASP' => 'SP005', 'MAKM' => 'KM011'],
            ['MASP' => 'SP006', 'MAKM' => 'KM011'],
            ['MASP' => 'SP007', 'MAKM' => 'KM011'],
            ['MASP' => 'SP008', 'MAKM' => 'KM006'],
            ['MASP' => 'SP008', 'MAKM' => 'KM011'],
            ['MASP' => 'SP009', 'MAKM' => 'KM006'],
            ['MASP' => 'SP009', 'MAKM' => 'KM011'],
            ['MASP' => 'SP010', 'MAKM' => 'KM011'],
        ]);
    }
}
