<?php

namespace Database\Seeders;

use App\Models\PhienChatbot;
use Illuminate\Database\Seeder;

class PhienChatbotSeeder extends Seeder
{
    /**
     * Dữ liệu bảng PHIEN_CHATBOT (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        PhienChatbot::query()->insertOrIgnore([
            ['MAPHIEN' => 1, 'MAKH' => null, 'MATK' => null, 'THOIGIAN_BD' => '2026-10-05 20:47:57', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
            ['MAPHIEN' => 2, 'MAKH' => null, 'MATK' => null, 'THOIGIAN_BD' => '2026-10-05 22:23:23', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
            ['MAPHIEN' => 3, 'MAKH' => null, 'MATK' => null, 'THOIGIAN_BD' => '2026-10-07 15:55:26', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
            ['MAPHIEN' => 4, 'MAKH' => 'KH0012', 'MATK' => 'TK0112', 'THOIGIAN_BD' => '2026-10-07 16:04:11', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
            ['MAPHIEN' => 5, 'MAKH' => null, 'MATK' => null, 'THOIGIAN_BD' => '2026-10-07 16:12:23', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
            ['MAPHIEN' => 6, 'MAKH' => null, 'MATK' => null, 'THOIGIAN_BD' => '2026-10-07 16:24:40', 'THOIGIAN_KT' => null, 'TRANGTHAI' => 'DangChat'],
        ]);
    }
}
