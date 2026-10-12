<?php

namespace Database\Seeders;

use App\Models\LichSuGia;
use Illuminate\Database\Seeder;

class LichSuGiaSeeder extends Seeder
{
    /**
     * Dữ liệu bảng LICHSUGIA (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        LichSuGia::query()->insertOrIgnore([
            ['MALSG' => 1, 'MASP' => 'SP001', 'DONGIA_MOI' => '28000000.00', 'DONGIA_CU' => '30000000.00', 'NGAY_CAPNHAT' => '2025-01-10 09:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Điều chỉnh giá khai trương'],
            ['MALSG' => 2, 'MASP' => 'SP002', 'DONGIA_MOI' => '45000000.00', 'DONGIA_CU' => '48000000.00', 'NGAY_CAPNHAT' => '2025-01-15 14:30:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Giảm giá theo NCC'],
            ['MALSG' => 3, 'MASP' => 'SP003', 'DONGIA_MOI' => '32000000.00', 'DONGIA_CU' => '32000000.00', 'NGAY_CAPNHAT' => '2025-02-01 08:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Giá nhập mới'],
            ['MALSG' => 4, 'MASP' => 'SP004', 'DONGIA_MOI' => '15000000.00', 'DONGIA_CU' => '16500000.00', 'NGAY_CAPNHAT' => '2025-02-05 11:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Điều chỉnh cạnh tranh'],
            ['MALSG' => 5, 'MASP' => 'SP005', 'DONGIA_MOI' => '8500000.00', 'DONGIA_CU' => '9000000.00', 'NGAY_CAPNHAT' => '2025-02-10 16:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Cập nhật giá Q1/2025'],
            ['MALSG' => 6, 'MASP' => 'SP006', 'DONGIA_MOI' => '38000000.00', 'DONGIA_CU' => '40000000.00', 'NGAY_CAPNHAT' => '2025-03-01 09:30:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Giảm giá tháng 3'],
            ['MALSG' => 7, 'MASP' => 'SP007', 'DONGIA_MOI' => '14000000.00', 'DONGIA_CU' => '14000000.00', 'NGAY_CAPNHAT' => '2025-03-02 10:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Giá nhập lần đầu'],
            ['MALSG' => 8, 'MASP' => 'SP008', 'DONGIA_MOI' => '52000000.00', 'DONGIA_CU' => '55000000.00', 'NGAY_CAPNHAT' => '2025-03-03 11:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Cập nhật giá workstation'],
            ['MALSG' => 9, 'MASP' => 'SP009', 'DONGIA_MOI' => '43000000.00', 'DONGIA_CU' => '45000000.00', 'NGAY_CAPNHAT' => '2025-03-04 14:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Điều chỉnh theo thị trường'],
            ['MALSG' => 10, 'MASP' => 'SP010', 'DONGIA_MOI' => '41000000.00', 'DONGIA_CU' => '43000000.00', 'NGAY_CAPNHAT' => '2025-03-05 15:00:00', 'MANV_CAPNHAT' => 'NV003', 'GHI_CHU' => 'Cập nhật giá Legion 5 Pro'],
        ]);
    }
}
