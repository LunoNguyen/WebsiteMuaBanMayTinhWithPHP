<?php

namespace Database\Seeders;

use App\Models\LoaiSanPham;
use Illuminate\Database\Seeder;

class LoaiSanPhamSeeder extends Seeder
{
    /**
     * Dữ liệu bảng LOAISANPHAM (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        LoaiSanPham::query()->insertOrIgnore([
            ['MALOAI' => 'LSP001', 'TENLOAI' => 'Laptop'],
            ['MALOAI' => 'LSP002', 'TENLOAI' => 'PC - Máy tính bộ'],
            ['MALOAI' => 'LSP003', 'TENLOAI' => 'Máy tính All-in-One'],
            ['MALOAI' => 'LSP004', 'TENLOAI' => 'Màn hình'],
            ['MALOAI' => 'LSP005', 'TENLOAI' => 'Bàn phím'],
            ['MALOAI' => 'LSP006', 'TENLOAI' => 'Chuột'],
            ['MALOAI' => 'LSP007', 'TENLOAI' => 'Tai nghe'],
            ['MALOAI' => 'LSP008', 'TENLOAI' => 'RAM'],
            ['MALOAI' => 'LSP009', 'TENLOAI' => 'Ổ cứng SSD'],
            ['MALOAI' => 'LSP010', 'TENLOAI' => 'VGA - Card màn hình'],
        ]);
    }
}
