<?php

namespace App\Support;

use App\Models\HoaDon;

/**
 * Nút thao tác trên trang chi tiết đơn, theo trạng thái đơn và khu (admin, banhang, kho).
 * Thứ tự từ trái sang: In hoá đơn, Huỷ đơn, Đã nhận tiền, bước kế tiếp (nút chính, đứng cuối).
 */
class NutDonHang
{
    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: ?string, 5: ?string}>
     */
    public static function cho(HoaDon $hd, string $khu): array
    {
        $nut = [];

        if ($khu !== 'kho') {
            $nut[] = ['In hoá đơn', route("{$khu}.donhang.in", $hd->MAHD), 'phu', 'GET', null, 'printer'];

            if (in_array($hd->TRANGTHAI, HoaDon::HUY_DUOC, true)) {
                $nut[] = ['Huỷ đơn', route("{$khu}.donhang.huy", $hd->MAHD), 'huy', 'PATCH',
                    "Huỷ đơn {$hd->MAHD}? Tồn kho và lượt dùng mã sẽ được hoàn lại.", null];
            }

            if ($hd->thanhToan?->TRANGTHAI === 'ChoThanhToan' && $hd->TRANGTHAI !== 'DaHuy') {
                $nut[] = ['Đã nhận tiền', route("{$khu}.donhang.thanh-toan", $hd->MAHD), 'phu', 'PATCH',
                    'Xác nhận đã nhận '.formatVND($hd->TONGTIEN_HD)." cho đơn {$hd->MAHD}?", 'banknote'];
            }

            if (isset(HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI])) {
                $nut[] = [HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI], route("{$khu}.donhang.buoc-tiep-theo", $hd->MAHD), 'chinh', 'PATCH', null, 'check'];
            }
        } elseif ($hd->TRANGTHAI === 'DaXacNhan') {
            $nut[] = ['Xuất kho & bàn giao', route('kho.donhang.xuat-kho', $hd->MAHD), 'chinh', 'PATCH',
                "Xác nhận xuất kho đơn {$hd->MAHD}?", 'truck'];
        }

        return $nut;
    }
}
