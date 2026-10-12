<?php

namespace Database\Seeders;

use App\Models\DanhSachAnh;
use Illuminate\Database\Seeder;

class DanhSachAnhSeeder extends Seeder
{
    /**
     * Dữ liệu bảng DANHSACHANH (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        DanhSachAnh::query()->insertOrIgnore([
            ['MAANH' => 12, 'MASP' => 'SP010', 'URL_ANH' => 'sanpham/SP010/20261005-214922-fwipji0p.webp', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 13, 'MASP' => 'SP009', 'URL_ANH' => 'sanpham/SP009/20261005-215443-3r8nznsa.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 14, 'MASP' => 'SP008', 'URL_ANH' => 'sanpham/SP008/20261005-215512-yc7aa4hp.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 15, 'MASP' => 'SP007', 'URL_ANH' => 'sanpham/SP007/20261005-215547-vysdtsei.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 16, 'MASP' => 'SP006', 'URL_ANH' => 'sanpham/SP006/20261005-215620-0eaecctt.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 17, 'MASP' => 'SP005', 'URL_ANH' => 'sanpham/SP005/20261005-215650-tuwznsuy.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 18, 'MASP' => 'SP004', 'URL_ANH' => 'sanpham/SP004/20261005-215726-ygk1k7wh.webp', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 19, 'MASP' => 'SP003', 'URL_ANH' => 'sanpham/SP003/20261005-215758-wjxmx5w3.jpg', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 20, 'MASP' => 'SP002', 'URL_ANH' => 'sanpham/SP002/20261005-215825-whh5igos.webp', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
            ['MAANH' => 21, 'MASP' => 'SP001', 'URL_ANH' => 'sanpham/SP001/20261005-215904-tuxczzx6.webp', 'LA_ANH_CHINH' => 1, 'THU_TU' => 1],
        ]);
    }
}
