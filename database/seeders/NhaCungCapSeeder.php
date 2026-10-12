<?php

namespace Database\Seeders;

use App\Models\NhaCungCap;
use Illuminate\Database\Seeder;

class NhaCungCapSeeder extends Seeder
{
    /**
     * Dữ liệu bảng NHACUNGCAP (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        NhaCungCap::query()->insertOrIgnore([
            ['MANCC' => 'NCC001', 'TENNCC' => 'FPT Synnex', 'DIACHI_NCC' => 'Số 28, Đường 6, KCN VSIP, Bắc Ninh', 'SDT_NCC' => '02473000911', 'EMAIL_NCC' => 'sales@fptsynnex.com.vn'],
            ['MANCC' => 'NCC002', 'TENNCC' => 'Digiworld (DGW)', 'DIACHI_NCC' => '195-197 Nguyễn Thái Bình, Q.1, TP.HCM', 'SDT_NCC' => '02839141581', 'EMAIL_NCC' => 'info@digiworld.com.vn'],
            ['MANCC' => 'NCC003', 'TENNCC' => 'Petrosetco (PSD)', 'DIACHI_NCC' => '1-5 Lê Duẩn, Q.1, TP.HCM', 'SDT_NCC' => '02839117788', 'EMAIL_NCC' => 'supply@petrosetco.com.vn'],
            ['MANCC' => 'NCC004', 'TENNCC' => 'Viettel Distribution', 'DIACHI_NCC' => 'Số 1, Giang Văn Minh, Ba Đình, Hà Nội', 'SDT_NCC' => '19008067', 'EMAIL_NCC' => 'vd@viettel.com.vn'],
            ['MANCC' => 'NCC005', 'TENNCC' => 'ADG Distribution', 'DIACHI_NCC' => 'Số 42-44 Ngụy Như Kon Tum, Thanh Xuân, Hà Nội', 'SDT_NCC' => '02435553111', 'EMAIL_NCC' => 'order@adgdist.vn'],
            ['MANCC' => 'NCC006', 'TENNCC' => 'Công ty TNHH Ánh Minh', 'DIACHI_NCC' => '123 Hai Bà Trưng, Q.3, TP.HCM', 'SDT_NCC' => '02838123456', 'EMAIL_NCC' => 'contact@anhminh.vn'],
            ['MANCC' => 'NCC007', 'TENNCC' => 'Công ty Tin học Sao Việt', 'DIACHI_NCC' => '456 Lý Thường Kiệt, Q.10, TP.HCM', 'SDT_NCC' => '02838654321', 'EMAIL_NCC' => 'info@saoviet.vn'],
            ['MANCC' => 'NCC008', 'TENNCC' => 'Công ty Máy tính An Phát', 'DIACHI_NCC' => '789 Lê Hồng Phong, Q.5, TP.HCM', 'SDT_NCC' => '02839234567', 'EMAIL_NCC' => 'sales@anphat.vn'],
            ['MANCC' => 'NCC009', 'TENNCC' => 'Công ty Vĩnh Xuân (SPC)', 'DIACHI_NCC' => '364 Cộng Hòa, Tân Bình, TP.HCM', 'SDT_NCC' => '02838101111', 'EMAIL_NCC' => 'info@vinhxuan.vn'],
            ['MANCC' => 'NCC010', 'TENNCC' => 'Công ty Thuận Phát', 'DIACHI_NCC' => '987 Cách Mạng Tháng 8, Q.3, TP.HCM', 'SDT_NCC' => '02839876543', 'EMAIL_NCC' => 'thuanphat@gmail.com'],
        ]);
    }
}
