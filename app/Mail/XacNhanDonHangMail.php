<?php

namespace App\Mail;

use App\Models\HoaDon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email xác nhận đặt hàng thành công gửi cho khách hàng.
 */
class XacNhanDonHangMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public HoaDon $hoaDon,
    ) {
        $this->hoaDon->loadMissing([
            'chiTiets.sanPham.moTa',
            'chiTiets.sanPham.anhs',
            'thanhToan',
            'khachHang',
            'taiKhoan',
            'khuyenMais',
        ]);
    }

    /**
     * Tiêu đề bức thư.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Xác nhận đặt hàng thành công đơn hàng #{$this->hoaDon->MAHD} từ NEXUS Computer",
        );
    }

    /**
     * Giao diện bức thư.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.xac-nhan-don-hang',
            with: [
                'hd' => $this->hoaDon,
            ],
        );
    }
}
