<?php

namespace Database\Seeders;

use App\Models\CtGioHang;
use Illuminate\Database\Seeder;

class CtGioHangSeeder extends Seeder
{
    /**
     * Dữ liệu bảng CT_GIOHANG (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        CtGioHang::query()->insertOrIgnore([
            ['MACTGH' => 1, 'MAGIOHANG' => 1, 'MASP' => 'SP001', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '28000000.00', 'NGAYTHEM' => '2026-10-04 21:18:24'],
            ['MACTGH' => 2, 'MAGIOHANG' => 2, 'MASP' => 'SP003', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '32000000.00', 'NGAYTHEM' => '2026-10-04 21:18:24'],
            ['MACTGH' => 3, 'MAGIOHANG' => 3, 'MASP' => 'SP010', 'SOLUONG' => 2, 'DONGIA_LUCAT' => '41000000.00', 'NGAYTHEM' => '2026-10-04 21:18:24'],
            ['MACTGH' => 4, 'MAGIOHANG' => 4, 'MASP' => 'SP002', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '45000000.00', 'NGAYTHEM' => '2026-10-04 21:18:24'],
            ['MACTGH' => 7, 'MAGIOHANG' => 6, 'MASP' => 'SP002', 'SOLUONG' => 11, 'DONGIA_LUCAT' => '45000000.00', 'NGAYTHEM' => '2026-10-05 22:23:56'],
            ['MACTGH' => 8, 'MAGIOHANG' => 7, 'MASP' => 'SP002', 'SOLUONG' => 19, 'DONGIA_LUCAT' => '45000000.00', 'NGAYTHEM' => '2026-10-05 22:29:56'],
        ]);
    }
}
