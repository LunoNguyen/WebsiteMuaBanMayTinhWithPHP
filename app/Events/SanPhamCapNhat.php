<?php

namespace App\Events;

use App\Models\SanPham;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Giá, tồn kho hoặc trạng thái bán của một sản phẩm vừa đổi (đặt hàng, huỷ đơn, nhập kho, admin sửa).
 * Gửi lên kênh công khai "cua-hang" cho khách và kênh "quan-tri" cho dashboard.
 */
class SanPhamCapNhat implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public SanPham $sanPham) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('cua-hang'), new PrivateChannel('quan-tri')];
    }

    public function broadcastAs(): string
    {
        return 'san-pham.cap-nhat';
    }

    /**
     * @return array{ma: string, ten: ?string, gia: float, gia_text: string, ton: int, ton_text: string, con_hang: bool, trang_thai: string}
     */
    public function broadcastWith(): array
    {
        $sp = $this->sanPham;
        $conHang = $sp->conHang();

        return [
            'ma' => $sp->MASP,
            'ten' => $sp->TENSP,
            'gia' => (float) $sp->DONGIA_SP,
            'gia_text' => formatVND($sp->DONGIA_SP),
            'ton' => (int) $sp->SOLUONGTON,
            'ton_text' => $conHang ? 'Còn '.formatNum($sp->SOLUONGTON).' sản phẩm' : 'Tạm hết hàng',
            'con_hang' => $conHang,
            'trang_thai' => $sp->TRANGTHAI,
        ];
    }
}
