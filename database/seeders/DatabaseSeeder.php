<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Dữ liệu mẫu của hệ thống (lấy từ database ql_banmt), theo thứ tự khoá ngoại: bảng được tham chiếu nạp trước.
     * Mỗi seeder dùng insertOrIgnore nên chạy lại trên database đã có dữ liệu cũng không bị trùng.
     */
    public function run(): void
    {
        $this->call([
            KhachHangSeeder::class,
            ChucVuSeeder::class,
            NhanVienSeeder::class,
            TaiKhoanSeeder::class,
            HoaDonSeeder::class,
            LoaiSanPhamSeeder::class,
            NhaCungCapSeeder::class,
            NhaSanXuatSeeder::class,
            SanPhamSeeder::class,
            ChiTietHoaDonSeeder::class,
            GioHangSeeder::class,
            CtGioHangSeeder::class,
            KhuyenMaiSeeder::class,
            CtKhuyenMaiSeeder::class,
            PhieuNhapHangSeeder::class,
            CtPhieuNhapHangSeeder::class,
            DanhSachAnhSeeder::class,
            HoaDonKhuyenMaiSeeder::class,
            PhienChatbotSeeder::class,
            LichSuChatbotSeeder::class,
            LichSuGiaSeeder::class,
            MoTaSeeder::class,
            ThanhToanSeeder::class,
        ]);
    }
}
