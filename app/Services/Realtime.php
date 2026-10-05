<?php

namespace App\Services;

use App\Events\DonHangThayDoi;
use App\Events\SanPhamCapNhat;
use App\Models\HoaDon;
use App\Models\SanPham;
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
        try {
            event($suKien);
        } catch (Throwable $exception) {
            Log::warning('Không gửi được sự kiện realtime (Reverb có đang chạy không?)', [
                'su_kien' => $suKien::class,
                'loi' => $exception->getMessage(),
            ]);
        }
    }
}
