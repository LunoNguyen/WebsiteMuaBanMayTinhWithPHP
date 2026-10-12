<?php

namespace Database\Seeders;

use App\Models\PhieuNhapHang;
use Illuminate\Database\Seeder;

class PhieuNhapHangSeeder extends Seeder
{
    /**
     * Dữ liệu bảng PHIEUNHAPHANG (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        PhieuNhapHang::query()->insertOrIgnore([
            ['MAPNH' => 'PNH001', 'MANCC' => 'NCC001', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-01-03 09:00:00', 'NGAYGIAO' => '2025-01-05 14:00:00', 'NGAYNHAN' => '2025-01-06 09:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '5.00', 'TONGCONG_PNH' => '53000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH002', 'MANCC' => 'NCC002', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-01-08 10:00:00', 'NGAYGIAO' => '2025-01-10 15:00:00', 'NGAYNHAN' => '2025-01-11 10:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '3.00', 'TONGCONG_PNH' => '85000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH003', 'MANCC' => 'NCC003', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-01-13 09:00:00', 'NGAYGIAO' => '2025-01-15 10:00:00', 'NGAYNHAN' => '2025-01-16 08:00:00', 'TRANGTHAI' => 'DaNhan', 'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '2.00', 'TONGCONG_PNH' => '43000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH004', 'MANCC' => 'NCC004', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-01-30 08:00:00', 'NGAYGIAO' => '2025-02-01 11:00:00', 'NGAYNHAN' => '2025-02-02 14:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '4.00', 'TONGCONG_PNH' => '127000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH005', 'MANCC' => 'NCC005', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-02-03 11:00:00', 'NGAYGIAO' => '2025-02-05 09:00:00', 'NGAYNHAN' => '2025-02-06 11:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '5.00', 'TONGCONG_PNH' => '31500000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH006', 'MANCC' => 'NCC001', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-02-08 09:00:00', 'NGAYGIAO' => '2025-02-10 16:00:00', 'NGAYNHAN' => '2025-02-11 09:00:00', 'TRANGTHAI' => 'DaNhan', 'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '3.00', 'TONGCONG_PNH' => '75000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH007', 'MANCC' => 'NCC002', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-02-13 14:00:00', 'NGAYGIAO' => '2025-02-15 14:00:00', 'NGAYNHAN' => '2025-02-16 10:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '0.00', 'TONGCONG_PNH' => '66000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH008', 'MANCC' => 'NCC003', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-02-27 09:00:00', 'NGAYGIAO' => '2025-03-01 10:00:00', 'NGAYNHAN' => '2025-03-02 08:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '4.00', 'TONGCONG_PNH' => '95000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH009', 'MANCC' => 'NCC004', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-03-03 08:00:00', 'NGAYGIAO' => '2025-03-05 11:00:00', 'NGAYNHAN' => '2025-03-06 14:00:00', 'TRANGTHAI' => 'DaNhan', 'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '2.00', 'TONGCONG_PNH' => '21500000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
            ['MAPNH' => 'PNH010', 'MANCC' => 'NCC005', 'MANV' => 'NV003', 'NGAY_DATMUA' => '2025-03-08 10:00:00', 'NGAYGIAO' => '2025-03-10 09:00:00', 'NGAYNHAN' => '2025-03-11 11:00:00', 'TRANGTHAI' => 'HoanThanh', 'TRANGTHAI_THANHTOAN' => 'DaThanhToan', 'THUE_VAT' => '10.00', 'CHIETKHAU' => '0.00', 'TONGCONG_PNH' => '11000000.00', 'GHI_CHU' => null, 'NGAYTAO' => '2026-10-04 21:18:24'],
        ]);
    }
}
