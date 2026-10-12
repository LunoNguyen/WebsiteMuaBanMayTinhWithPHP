<?php

namespace Database\Seeders;

use App\Models\ChiTietHoaDon;
use Illuminate\Database\Seeder;

class ChiTietHoaDonSeeder extends Seeder
{
    /**
     * Dữ liệu bảng CHITIETHOADON (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        ChiTietHoaDon::query()->insertOrIgnore([
            ['MAHD' => 'HD001', 'MASP' => 'SP001', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '28000000.00', 'THANHTIEN' => '28000000.00'],
            ['MAHD' => 'HD002', 'MASP' => 'SP002', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '45000000.00', 'THANHTIEN' => '45000000.00'],
            ['MAHD' => 'HD003', 'MASP' => 'SP003', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '32000000.00', 'THANHTIEN' => '32000000.00'],
            ['MAHD' => 'HD004', 'MASP' => 'SP004', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '15000000.00', 'THANHTIEN' => '15000000.00'],
            ['MAHD' => 'HD005', 'MASP' => 'SP005', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '8500000.00', 'THANHTIEN' => '8500000.00'],
            ['MAHD' => 'HD006', 'MASP' => 'SP006', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '38000000.00', 'THANHTIEN' => '38000000.00'],
            ['MAHD' => 'HD007', 'MASP' => 'SP007', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '14000000.00', 'THANHTIEN' => '14000000.00'],
            ['MAHD' => 'HD008', 'MASP' => 'SP008', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '52000000.00', 'THANHTIEN' => '52000000.00'],
            ['MAHD' => 'HD009', 'MASP' => 'SP009', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '43000000.00', 'THANHTIEN' => '43000000.00'],
            ['MAHD' => 'HD010', 'MASP' => 'SP010', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '41000000.00', 'THANHTIEN' => '41000000.00'],
            ['MAHD' => 'HD011', 'MASP' => 'SP002', 'SOLUONG' => 11, 'DONGIA_LUCAT' => '45000000.00', 'THANHTIEN' => '495000000.00'],
            ['MAHD' => 'HD012', 'MASP' => 'SP010', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '41000000.00', 'THANHTIEN' => '41000000.00'],
            ['MAHD' => 'HD013', 'MASP' => 'SP010', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '41000000.00', 'THANHTIEN' => '41000000.00'],
            ['MAHD' => 'HD014', 'MASP' => 'SP003', 'SOLUONG' => 1, 'DONGIA_LUCAT' => '32000000.00', 'THANHTIEN' => '32000000.00'],
        ]);
    }
}
