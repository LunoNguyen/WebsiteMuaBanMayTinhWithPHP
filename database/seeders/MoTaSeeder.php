<?php

namespace Database\Seeders;

use App\Models\MoTa;
use Illuminate\Database\Seeder;

class MoTaSeeder extends Seeder
{
    /**
     * Dữ liệu bảng MOTA (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        MoTa::query()->insertOrIgnore([
            ['MAMT' => 'MT001', 'MASP' => 'SP001', 'RAM' => '16GB LPDDR5', 'CPU' => 'Core i7-1360P', 'ROM' => '1TB SSD NVMe', 'MANHINH' => '13.4" 3.5K OLED', 'VGA' => 'Intel Iris Xe', 'PIN' => '55Wh', 'KHAC' => 'Vỏ nhôm, 1.2kg'],
            ['MAMT' => 'MT002', 'MASP' => 'SP002', 'RAM' => '18GB Unified', 'CPU' => 'Apple M3 Pro', 'ROM' => '512GB SSD', 'MANHINH' => '14.2" Liquid Retina XDR', 'VGA' => 'M3 Pro 14-core GPU', 'PIN' => '70Wh', 'KHAC' => 'Space Black'],
            ['MAMT' => 'MT003', 'MASP' => 'SP003', 'RAM' => '16GB DDR5', 'CPU' => 'Ryzen 9 7940HS', 'ROM' => '1TB SSD NVMe', 'MANHINH' => '14" QHD+ 165Hz', 'VGA' => 'RTX 4060 8GB', 'PIN' => '73Wh', 'KHAC' => 'AniMe Matrix LED'],
            ['MAMT' => 'MT004', 'MASP' => 'SP004', 'RAM' => '8GB DDR4', 'CPU' => 'Core i5-1235U', 'ROM' => '512GB SSD', 'MANHINH' => '15.6" FHD IPS', 'VGA' => 'Intel Iris Xe', 'PIN' => '41Wh', 'KHAC' => 'Vỏ nhựa, 1.74kg'],
            ['MAMT' => 'MT005', 'MASP' => 'SP005', 'RAM' => '8GB DDR4', 'CPU' => 'Core i3-12100', 'ROM' => '256GB SSD', 'MANHINH' => null, 'VGA' => 'Intel UHD 730', 'PIN' => null, 'KHAC' => 'Case SFF, WiFi6'],
            ['MAMT' => 'MT006', 'MASP' => 'SP006', 'RAM' => '16GB DDR5', 'CPU' => 'Core i7-12700F', 'ROM' => '1TB SSD NVMe', 'MANHINH' => null, 'VGA' => 'RTX 3070 8GB', 'PIN' => null, 'KHAC' => 'Case full ATX RGB'],
            ['MAMT' => 'MT007', 'MASP' => 'SP007', 'RAM' => '8GB DDR4', 'CPU' => 'Core i5-1235U', 'ROM' => '512GB SSD', 'MANHINH' => '23.8" FHD IPS Touch', 'VGA' => 'Intel Iris Xe', 'PIN' => '65Wh', 'KHAC' => 'Cảm ứng 10 điểm'],
            ['MAMT' => 'MT008', 'MASP' => 'SP008', 'RAM' => '32GB DDR5', 'CPU' => 'Core i7-12800H', 'ROM' => '1TB SSD NVMe', 'MANHINH' => '15.6" FHD+ IPS', 'VGA' => 'NVIDIA RTX A2000 8GB', 'PIN' => '86Wh', 'KHAC' => 'Chuyên đồ họa 3D/CAD'],
            ['MAMT' => 'MT009', 'MASP' => 'SP009', 'RAM' => '32GB DDR5', 'CPU' => 'Core i9-13900K', 'ROM' => '1TB SSD NVMe', 'MANHINH' => null, 'VGA' => 'NVIDIA Quadro T1000', 'PIN' => null, 'KHAC' => 'Workstation chuyên dụng'],
            ['MAMT' => 'MT010', 'MASP' => 'SP010', 'RAM' => '32GB DDR5', 'CPU' => 'Ryzen 7 7745HX', 'ROM' => '1TB SSD NVMe', 'MANHINH' => '16" WQXGA 240Hz', 'VGA' => 'RTX 4070 8GB', 'PIN' => '80Wh', 'KHAC' => 'Tản nhiệt Legion Coldfront 5.0'],
        ]);
    }
}
