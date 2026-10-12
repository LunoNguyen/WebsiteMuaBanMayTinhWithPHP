<?php

namespace Database\Seeders;

use App\Models\NhanVien;
use Illuminate\Database\Seeder;

class NhanVienSeeder extends Seeder
{
    /**
     * Dữ liệu bảng NHANVIEN (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        NhanVien::query()->insertOrIgnore([
            ['MANV' => 'NV001', 'MACV' => 'CV001', 'TENNV' => 'Nguyễn Ngân Lượng', 'SDT_NV' => '0903111111', 'DIACHI_NV' => '1 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'admin@phongvu.com', 'NGAYVAOLAM' => '2020-01-01', 'TRANGTHAI' => 1],
            ['MANV' => 'NV002', 'MACV' => 'CV002', 'TENNV' => 'Trần Hữu Quản', 'SDT_NV' => '0903222222', 'DIACHI_NV' => '2 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'qly.banhang@phongvu.com', 'NGAYVAOLAM' => '2020-06-01', 'TRANGTHAI' => 1],
            ['MANV' => 'NV003', 'MACV' => 'CV003', 'TENNV' => 'Nguyễn Thị Kho', 'SDT_NV' => '0903333333', 'DIACHI_NV' => '3 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'qly.kho@phongvu.com', 'NGAYVAOLAM' => '2020-06-01', 'TRANGTHAI' => 1],
            ['MANV' => 'NV004', 'MACV' => 'CV004', 'TENNV' => 'Phạm Văn Bán', 'SDT_NV' => '0903444444', 'DIACHI_NV' => '4 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'banhang01@phongvu.com', 'NGAYVAOLAM' => '2021-01-05', 'TRANGTHAI' => 1],
            ['MANV' => 'NV005', 'MACV' => 'CV005', 'TENNV' => 'Võ Thị Ngân', 'SDT_NV' => '0903555555', 'DIACHI_NV' => '5 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'thungan01@phongvu.com', 'NGAYVAOLAM' => '2021-03-10', 'TRANGTHAI' => 1],
            ['MANV' => 'NV006', 'MACV' => 'CV006', 'TENNV' => 'Lý Thị Kế', 'SDT_NV' => '0903666666', 'DIACHI_NV' => '6 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'ketoan@phongvu.com', 'NGAYVAOLAM' => '2021-01-05', 'TRANGTHAI' => 1],
            ['MANV' => 'NV007', 'MACV' => 'CV007', 'TENNV' => 'Hồ Văn Giao', 'SDT_NV' => '0903777777', 'DIACHI_NV' => '7 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'giaohang01@phongvu.com', 'NGAYVAOLAM' => '2021-05-01', 'TRANGTHAI' => 1],
            ['MANV' => 'NV008', 'MACV' => 'CV008', 'TENNV' => 'Đinh Văn IT', 'SDT_NV' => '0903888888', 'DIACHI_NV' => '8 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'it.support@phongvu.com', 'NGAYVAOLAM' => '2021-01-05', 'TRANGTHAI' => 1],
            ['MANV' => 'NV009', 'MACV' => 'CV009', 'TENNV' => 'Mai Thị Chăm', 'SDT_NV' => '0903999999', 'DIACHI_NV' => '9 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'cskh@phongvu.com', 'NGAYVAOLAM' => '2022-01-10', 'TRANGTHAI' => 1],
            ['MANV' => 'NV010', 'MACV' => 'CV010', 'TENNV' => 'Bùi Văn Vệ', 'SDT_NV' => '0903000000', 'DIACHI_NV' => '10 Võ Văn Ngân, TP.Thủ Đức', 'EMAIL_NV' => 'baove@phongvu.com', 'NGAYVAOLAM' => '2022-03-01', 'TRANGTHAI' => 1],
        ]);
    }
}
