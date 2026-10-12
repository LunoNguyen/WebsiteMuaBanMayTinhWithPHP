<?php

namespace Database\Seeders;

use App\Models\CtPhieuNhapHang;
use Illuminate\Database\Seeder;

class CtPhieuNhapHangSeeder extends Seeder
{
    /**
     * Dữ liệu bảng CT_PHIEUNHAPHANG (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        CtPhieuNhapHang::query()->insertOrIgnore([
            ['MAPNH' => 'PNH001', 'MASP' => 'SP001', 'SOLUONG' => 20, 'DONGIA_NHAP' => '22000000.00', 'THANHTIEN' => '440000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH001', 'MASP' => 'SP002', 'SOLUONG' => 10, 'DONGIA_NHAP' => '38000000.00', 'THANHTIEN' => '380000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH002', 'MASP' => 'SP003', 'SOLUONG' => 30, 'DONGIA_NHAP' => '26000000.00', 'THANHTIEN' => '780000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH003', 'MASP' => 'SP004', 'SOLUONG' => 50, 'DONGIA_NHAP' => '12000000.00', 'THANHTIEN' => '600000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH004', 'MASP' => 'SP005', 'SOLUONG' => 100, 'DONGIA_NHAP' => '7000000.00', 'THANHTIEN' => '700000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH005', 'MASP' => 'SP006', 'SOLUONG' => 20, 'DONGIA_NHAP' => '32000000.00', 'THANHTIEN' => '640000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH006', 'MASP' => 'SP007', 'SOLUONG' => 50, 'DONGIA_NHAP' => '11000000.00', 'THANHTIEN' => '550000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH007', 'MASP' => 'SP008', 'SOLUONG' => 20, 'DONGIA_NHAP' => '44000000.00', 'THANHTIEN' => '880000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH008', 'MASP' => 'SP009', 'SOLUONG' => 10, 'DONGIA_NHAP' => '37000000.00', 'THANHTIEN' => '370000000.00', 'GHI_CHU' => null],
            ['MAPNH' => 'PNH009', 'MASP' => 'SP010', 'SOLUONG' => 10, 'DONGIA_NHAP' => '36000000.00', 'THANHTIEN' => '360000000.00', 'GHI_CHU' => null],
        ]);
    }
}
