<?php

namespace Database\Seeders;

use App\Models\ChucVu;
use Illuminate\Database\Seeder;

class ChucVuSeeder extends Seeder
{
    /**
     * Dữ liệu bảng CHUCVU (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        ChucVu::query()->insertOrIgnore([
            ['MACV' => 'CV001', 'TENCV' => 'Giám đốc', 'MOTA_CV' => 'Điều hành toàn bộ công ty'],
            ['MACV' => 'CV002', 'TENCV' => 'Quản lý bán hàng', 'MOTA_CV' => 'Quản lý nhân viên bán và chiến lược kinh doanh'],
            ['MACV' => 'CV003', 'TENCV' => 'Quản lý kho', 'MOTA_CV' => 'Quản lý nhập xuất kho, kiểm kê hàng hóa'],
            ['MACV' => 'CV004', 'TENCV' => 'Nhân viên bán hàng', 'MOTA_CV' => 'Tư vấn và bán hàng trực tiếp'],
            ['MACV' => 'CV005', 'TENCV' => 'Nhân viên thu ngân', 'MOTA_CV' => 'Thu tiền và lập hóa đơn'],
            ['MACV' => 'CV006', 'TENCV' => 'Kế toán', 'MOTA_CV' => 'Quản lý tài chính, công nợ'],
            ['MACV' => 'CV007', 'TENCV' => 'Nhân viên giao hàng', 'MOTA_CV' => 'Vận chuyển hàng đến khách'],
            ['MACV' => 'CV008', 'TENCV' => 'Nhân viên IT', 'MOTA_CV' => 'Quản trị hệ thống'],
            ['MACV' => 'CV009', 'TENCV' => 'Chăm sóc khách hàng', 'MOTA_CV' => 'Hỗ trợ sau bán hàng'],
            ['MACV' => 'CV010', 'TENCV' => 'Bảo vệ', 'MOTA_CV' => 'An ninh cơ sở'],
        ]);
    }
}
