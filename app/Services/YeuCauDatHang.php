<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Theo dõi một lần bấm "Đặt hàng" (mã yêu cầu gửi kèm form) từ lúc vào hàng đợi tới khi có kết quả.
 * Cùng một mã chỉ được nhận một lần, nên bấm hai lần hay trình duyệt gửi lại không tạo đơn trùng.
 */
class YeuCauDatHang
{
    /**
     * Giữ trạng thái một ngày, đủ để khách quay lại xem kết quả.
     */
    private const GIU_TRONG_GIAY = 86400;

    public const CHO = 'cho';

    public const XONG = 'xong';

    public const LOI = 'loi';

    /**
     * Ghi nhận yêu cầu mới ở trạng thái "đang chờ". Trả về false nếu mã này đã được nhận trước đó (gửi trùng).
     * Khoá ngắn bao quanh bước kiểm tra + ghi để hai request đến cùng lúc không cùng lọt qua.
     */
    public function nhan(string $maTaiKhoan, string $maYeuCau): bool
    {
        return (bool) Cache::lock($this->khoa($maTaiKhoan, $maYeuCau).':khoa', 10)->block(5, function () use ($maTaiKhoan, $maYeuCau): bool {
            if (Cache::has($this->khoa($maTaiKhoan, $maYeuCau))) {
                return false;
            }

            Cache::put($this->khoa($maTaiKhoan, $maYeuCau), ['trang_thai' => self::CHO], self::GIU_TRONG_GIAY);

            return true;
        });
    }

    /**
     * @return array{trang_thai: string, ma_hd?: string, loi?: string, truong?: string}|null
     */
    public function trangThai(string $maTaiKhoan, string $maYeuCau): ?array
    {
        return Cache::get($this->khoa($maTaiKhoan, $maYeuCau));
    }

    public function xong(string $maTaiKhoan, string $maYeuCau, string $maHoaDon): void
    {
        Cache::put($this->khoa($maTaiKhoan, $maYeuCau), ['trang_thai' => self::XONG, 'ma_hd' => $maHoaDon], self::GIU_TRONG_GIAY);
    }

    public function loi(string $maTaiKhoan, string $maYeuCau, string $thongBao, string $truong = 'gio_hang'): void
    {
        Cache::put($this->khoa($maTaiKhoan, $maYeuCau), ['trang_thai' => self::LOI, 'loi' => $thongBao, 'truong' => $truong], self::GIU_TRONG_GIAY);
    }

    private function khoa(string $maTaiKhoan, string $maYeuCau): string
    {
        return "dat-hang:{$maTaiKhoan}:{$maYeuCau}";
    }
}
