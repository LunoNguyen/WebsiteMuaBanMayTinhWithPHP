<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Lock;
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

    /**
     * Khoá cache worker hàng đợi "dat-hang" ghi lại mỗi vòng lặp (xem AppServiceProvider).
     */
    public const NHIP_WORKER = 'hang-doi:dat-hang:nhip';

    /**
     * Worker im lặng quá lâu thì coi như không chạy.
     */
    public const NHIP_HET_HAN_GIAY = 30;

    /**
     * Yêu cầu nằm chờ quá lâu mà không có worker thì trang chờ tự xử lý.
     */
    public const CHO_TOI_DA_GIAY = 5;

    public const CHO = 'cho';

    public const XONG = 'xong';

    public const LOI = 'loi';

    /**
     * Ghi nhận yêu cầu mới ở trạng thái "đang chờ", kèm dữ liệu để xử lý lại nếu job trong hàng đợi không ai lấy.
     * Trả về false nếu mã này đã được nhận trước đó (gửi trùng).
     *
     * @param  array{san_pham: list<array{MASP: string, SOLUONG: int}>, thong_tin: array<string, string|null>, ma_code: ?string}  $duLieu
     */
    public function nhan(string $maTaiKhoan, string $maYeuCau, array $duLieu): bool
    {
        return (bool) Cache::lock($this->khoa($maTaiKhoan, $maYeuCau).':khoa', 10)->block(5, function () use ($maTaiKhoan, $maYeuCau, $duLieu): bool {
            if (Cache::has($this->khoa($maTaiKhoan, $maYeuCau))) {
                return false;
            }

            Cache::put($this->khoa($maTaiKhoan, $maYeuCau), [
                'trang_thai' => self::CHO,
                'luc' => now()->timestamp,
                'du_lieu' => $duLieu,
            ], self::GIU_TRONG_GIAY);

            return true;
        });
    }

    /**
     * @return array{trang_thai: string, luc?: int, du_lieu?: array<string, mixed>, ma_hd?: string, loi?: string, truong?: string}|null
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

    /**
     * Khoá chỉ một nơi được xử lý một yêu cầu tại một thời điểm (worker hoặc trang chờ tự xử lý).
     */
    public function khoaXuLy(string $maTaiKhoan, string $maYeuCau): Lock
    {
        return Cache::lock($this->khoa($maTaiKhoan, $maYeuCau).':xu-ly', 120);
    }

    /**
     * Có worker hàng đợi "dat-hang" đang chạy không (dựa vào nhịp worker ghi mỗi vòng lặp).
     */
    public function coWorker(): bool
    {
        return Cache::has(self::NHIP_WORKER);
    }

    public function ghiNhipWorker(): void
    {
        Cache::put(self::NHIP_WORKER, now()->timestamp, self::NHIP_HET_HAN_GIAY);
    }

    /**
     * Yêu cầu còn chờ, đã quá thời gian chờ, và không có worker nào để lấy nó ra xử lý.
     *
     * @param  array<string, mixed>|null  $trangThai
     */
    public function biKet(?array $trangThai): bool
    {
        return ($trangThai['trang_thai'] ?? null) === self::CHO
            && isset($trangThai['du_lieu'])
            && now()->timestamp - (int) ($trangThai['luc'] ?? 0) >= self::CHO_TOI_DA_GIAY
            && ! $this->coWorker();
    }

    private function khoa(string $maTaiKhoan, string $maYeuCau): string
    {
        return "dat-hang:{$maTaiKhoan}:{$maYeuCau}";
    }
}
