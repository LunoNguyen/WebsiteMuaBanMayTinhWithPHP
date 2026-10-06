<?php

namespace App\Services;

use App\Events\DonHangThayDoi;
use App\Events\SanPhamCapNhat;
use App\Models\HoaDon;
use App\Models\SanPham;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Phát sự kiện realtime qua Reverb sau khi dữ liệu đã lưu xong (sau commit).
 * Reverb không chạy thì chỉ ghi log, không làm hỏng thao tác của người dùng.
 */
class Realtime
{
    /**
     * Khoá cache đánh dấu Reverb vừa không gửi được.
     */
    private const TAM_NGUNG = 'realtime:tam-ngung';

    private const TAM_NGUNG_GIAY = 30;

    /**
     * Báo giá / tồn kho mới của các sản phẩm.
     *
     * @param  iterable<string>|string  $maSanPham
     */
    public static function sanPham(iterable|string $maSanPham): void
    {
        $ma = collect(is_string($maSanPham) ? [$maSanPham] : $maSanPham)->unique()->values();

        DB::afterCommit(function () use ($ma): void {
            SanPham::query()->whereKey($ma)->get()
                ->each(fn (SanPham $sp) => self::phat(new SanPhamCapNhat($sp)));
        });
    }

    /**
     * Báo đơn mới / đơn đổi trạng thái.
     */
    public static function donHang(HoaDon $hoaDon, bool $laDonMoi = false): void
    {
        DB::afterCommit(function () use ($hoaDon, $laDonMoi): void {
            self::phat(new DonHangThayDoi($hoaDon->fresh() ?? $hoaDon, $laDonMoi));
        });
    }

    private static function phat(object $suKien): void
    {
        // Vừa gửi lỗi (Reverb tắt) thì tạm bỏ qua một lúc, không bắt mỗi đơn hàng chờ hết thời gian kết nối
        if (Cache::has(self::TAM_NGUNG)) {
            return;
        }

        try {
            event($suKien);
        } catch (Throwable $exception) {
            Cache::put(self::TAM_NGUNG, true, self::TAM_NGUNG_GIAY);
            Log::warning('Không gửi được sự kiện realtime (Reverb có đang chạy không?), tạm ngừng gửi '.self::TAM_NGUNG_GIAY.' giây', [
                'su_kien' => $suKien::class,
                'loi' => $exception->getMessage(),
            ]);
        }
    }
}
