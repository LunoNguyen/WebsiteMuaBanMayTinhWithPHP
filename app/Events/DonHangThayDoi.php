<?php

namespace App\Events;

use App\Models\HoaDon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Đơn hàng vừa được đặt hoặc đổi trạng thái / thanh toán.
 * Gửi cho nhân viên (kênh "quan-tri") và cho chính khách đặt đơn (kênh "don-hang.{MATK}").
 */
class DonHangThayDoi implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public HoaDon $hoaDon, public bool $laDonMoi = false) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $kenh = [new PrivateChannel('quan-tri')];

        if ($this->hoaDon->MATK !== null) {
            $kenh[] = new PrivateChannel('don-hang.'.$this->hoaDon->MATK);
        }

        return $kenh;
    }

    public function broadcastAs(): string
    {
        return 'don-hang.thay-doi';
    }

    /**
     * @return array{ma: string, moi: bool, trang_thai: string, nhan: string, tong_text: string, nguoi_nhan: ?string}
     */
    public function broadcastWith(): array
    {
        $hd = $this->hoaDon;

        return [
            'ma' => $hd->MAHD,
            'moi' => $this->laDonMoi,
            'trang_thai' => $hd->TRANGTHAI,
            'nhan' => HoaDon::NHAN_TRANG_THAI[$hd->TRANGTHAI] ?? $hd->TRANGTHAI,
            'tong_text' => formatVND($hd->TONGTIEN_HD),
            'nguoi_nhan' => $hd->TEN_NGUOINHAN,
        ];
    }
}
