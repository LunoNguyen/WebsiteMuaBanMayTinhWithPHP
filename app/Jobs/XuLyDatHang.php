<?php

namespace App\Jobs;

use App\Models\TaiKhoan;
use App\Services\DatHangService;
use App\Services\YeuCauDatHang;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Xử lý một lần đặt hàng trong hàng đợi "dat-hang".
 * Chạy một worker cho hàng đợi này thì các đơn được xử lý lần lượt, không tranh nhau tồn kho hay lượt dùng mã;
 * khoá dòng trong DatHangService vẫn giữ an toàn khi có thêm worker hoặc đơn tại quầy chạy song song.
 */
class XuLyDatHang implements ShouldQueue
{
    use Queueable;

    /**
     * Không tự chạy lại: lỗi sau khi đã ghi đơn mà chạy lại có thể tạo đơn trùng.
     */
    public int $tries = 1;

    public int $timeout = 60;

    /**
     * @param  list<array{MASP: string, SOLUONG: int}>  $sanPham  giỏ hàng chụp lúc khách bấm "Đặt hàng"
     * @param  array<string, string|null>  $thongTin  thông tin nhận hàng, thanh toán đã qua kiểm tra
     */
    public function __construct(
        public string $maTaiKhoan,
        public string $maYeuCau,
        public array $sanPham,
        public array $thongTin,
        public ?string $maCode,
    ) {
        $this->onQueue('dat-hang');
    }

    public function handle(DatHangService $datHang, YeuCauDatHang $yeuCau): void
    {
        if (($yeuCau->trangThai($this->maTaiKhoan, $this->maYeuCau)['trang_thai'] ?? null) !== YeuCauDatHang::CHO) {
            return;
        }

        $taiKhoan = TaiKhoan::query()->find($this->maTaiKhoan);

        if ($taiKhoan === null) {
            $yeuCau->loi($this->maTaiKhoan, $this->maYeuCau, 'Tài khoản không còn tồn tại.');

            return;
        }

        try {
            $hoaDon = $datHang->datHangTheoDanhSach($taiKhoan, $this->sanPham, $this->thongTin, $this->maCode);
            $yeuCau->xong($this->maTaiKhoan, $this->maYeuCau, $hoaDon->MAHD);
        } catch (ValidationException $e) {
            $truong = array_key_first($e->errors()) ?? 'gio_hang';
            $yeuCau->loi($this->maTaiKhoan, $this->maYeuCau, $e->validator->errors()->first(), $truong);
        }
    }

    /**
     * Lỗi ngoài dự kiến (mất kết nối DB...): transaction đã rollback, báo khách đặt lại.
     */
    public function failed(?Throwable $exception): void
    {
        app(YeuCauDatHang::class)->loi($this->maTaiKhoan, $this->maYeuCau, 'Hệ thống chưa xử lý được đơn hàng, vui lòng thử đặt lại.');
    }
}
