<?php

namespace App\Services;

use App\Models\HoaDon;
use App\Models\PhienChatbot;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Support\Str;

/**
 * Chatbot trả lời theo luật dựa trên dữ liệu cửa hàng; lưu hội thoại vào PHIEN_CHATBOT / LICHSU_CHATBOT.
 */
class ChatbotService
{
    /**
     * Tên trạng thái đơn hàng dễ đọc.
     *
     * @var array<string, string>
     */
    private const TRANG_THAI_DON = [
        'ChoXacNhan' => 'đang chờ xác nhận',
        'DaXacNhan' => 'đã được xác nhận, đang chuẩn bị hàng',
        'DangGiao' => 'đang được giao',
        'DaGiao' => 'đã giao thành công',
        'HoanThanh' => 'đã hoàn thành',
        'DaHuy' => 'đã bị huỷ',
    ];

    public function __construct(private KhuyenMaiService $khuyenMai) {}

    /**
     * Phiên chat hiện tại (theo mã phiên lưu trong session), tạo mới nếu chưa có hoặc đã kết thúc.
     */
    public function phien(?int $maPhien, ?TaiKhoan $taiKhoan): PhienChatbot
    {
        $phien = $maPhien ? PhienChatbot::query()->find($maPhien) : null;

        if ($phien === null || $phien->TRANGTHAI !== 'DangChat' || $phien->MATK !== $taiKhoan?->MATK) {
            $phien = PhienChatbot::create([
                'MATK' => $taiKhoan?->MATK,
                'MAKH' => $taiKhoan?->MAKH,
                'THOIGIAN_BD' => now(),
                'TRANGTHAI' => 'DangChat',
            ]);
        }

        return $phien;
    }

    /**
     * Ghi câu hỏi, sinh câu trả lời và ghi lại; trả về câu trả lời kèm gợi ý sản phẩm.
     *
     * @return array{tra_loi: string, san_pham: list<array{ma: string, ten: string, gia: string, url: string}>}
     */
    public function hoi(PhienChatbot $phien, string $cauHoi, ?TaiKhoan $taiKhoan): array
    {
        $phien->tinNhans()->create(['NGUOI_GUI' => 'KhachHang', 'NOI_DUNG' => $cauHoi, 'THOIGIAN' => now()]);

        $ketQua = $this->traLoi($cauHoi, $taiKhoan);

        $phien->tinNhans()->create(['NGUOI_GUI' => 'Bot', 'NOI_DUNG' => $ketQua['tra_loi'], 'THOIGIAN' => now()]);

        return $ketQua;
    }

    /**
     * @return array{tra_loi: string, san_pham: list<array{ma: string, ten: string, gia: string, url: string}>}
     */
    private function traLoi(string $cauHoi, ?TaiKhoan $taiKhoan): array
    {
        $cau = Str::lower(Str::ascii($cauHoi));
        $tra = fn (string $text, array $sp = []): array => ['tra_loi' => $text, 'san_pham' => $sp];

        if (preg_match('/\b(xin chao|chao|hello|hi|alo)\b/', $cau)) {
            return $tra('Xin chào! Mình là trợ lý của NEXUS. Bạn có thể hỏi mình về sản phẩm (ví dụ "laptop gaming dưới 40 triệu"), mã giảm giá, đơn hàng, giao hàng hoặc bảo hành.');
        }

        if (preg_match('/(don hang|don cua toi|kiem tra don|trang thai don)/', $cau)) {
            if ($taiKhoan === null) {
                return $tra('Bạn vui lòng đăng nhập để mình kiểm tra đơn hàng của bạn nhé.');
            }
            $don = HoaDon::query()->where('MATK', $taiKhoan->MATK)->latest('NGAYLAP')->first();

            return $tra($don === null
                ? 'Bạn chưa có đơn hàng nào.'
                : "Đơn gần nhất của bạn là {$don->MAHD} (".formatVND($don->TONGTIEN_HD).'), '.(self::TRANG_THAI_DON[$don->TRANGTHAI] ?? $don->TRANGTHAI).'.');
        }

        if (preg_match('/(voucher|ma giam|khuyen mai|giam gia|uu dai|coupon)/', $cau)) {
            $ds = $this->khuyenMai->dangApDung();

            return $tra($ds->isEmpty()
                ? 'Hiện chưa có mã giảm giá nào đang áp dụng. Bạn theo dõi trang chủ để nhận ưu đãi mới nhé.'
                : 'Các mã đang áp dụng: '.$ds->map(fn ($km) => "{$km->MA_CODE} ({$km->TENKM})")->implode(', ').'. Nhập mã ở bước giỏ hàng để được giảm.');
        }

        if (preg_match('/(giao hang|ship|van chuyen|bao lau)/', $cau)) {
            return $tra('NEXUS giao hàng toàn quốc, nội thành 1–2 ngày, tỉnh khác 2–5 ngày. Bạn cũng có thể chọn nhận tại cửa hàng ở bước thanh toán.');
        }

        if (preg_match('/(bao hanh|doi tra|hoan tien|loi)/', $cau)) {
            return $tra('Sản phẩm được bảo hành chính hãng theo hãng sản xuất. Trong 7 ngày đầu nếu có lỗi từ nhà sản xuất, bạn được đổi sản phẩm mới.');
        }

        if (preg_match('/(thanh toan|tra gop|chuyen khoan|cod|qr)/', $cau)) {
            return $tra('Bạn có thể thanh toán khi nhận hàng (COD), chuyển khoản hoặc quét mã QR ở bước thanh toán.');
        }

        $sanPham = $this->timSanPham($cau);
        if ($sanPham !== []) {
            return $tra('Mình tìm được '.count($sanPham).' sản phẩm phù hợp:', $sanPham);
        }

        return $tra('Mình chưa hiểu câu hỏi. Bạn thử hỏi về một sản phẩm (ví dụ "MacBook", "laptop dưới 30 triệu", "RTX 4060"), mã giảm giá, đơn hàng, giao hàng hoặc bảo hành nhé.');
    }

    /**
     * Tìm sản phẩm theo từ khoá (tên, hãng, loại, cấu hình) và mức giá "dưới / trên X triệu".
     *
     * @return list<array{ma: string, ten: string, gia: string, url: string}>
     */
    private function timSanPham(string $cau): array
    {
        $query = SanPham::query()->with('nhaSanXuat', 'loaiSanPham', 'moTa')->where('TRANGTHAI', 'DangBan');

        if (preg_match('/duoi\s*(\d+)\s*(trieu|tr)/', $cau, $m)) {
            $query->where('DONGIA_SP', '<=', (int) $m[1] * 1_000_000);
        }
        if (preg_match('/tren\s*(\d+)\s*(trieu|tr)/', $cau, $m)) {
            $query->where('DONGIA_SP', '>=', (int) $m[1] * 1_000_000);
        }

        $boQua = ['toi', 'muon', 'mua', 'can', 'tim', 'co', 'khong', 'nao', 'gia', 'bao', 'nhieu', 'duoi', 'tren', 'trieu', 'cho', 'minh', 'ban', 'the', 've', 'loai', 'may', 'tinh', 'hay', 'nhat', 'tot', 'dang'];
        $tuKhoa = array_values(array_filter(
            preg_split('/[^a-z0-9]+/', $cau) ?: [],
            fn (string $t): bool => strlen($t) >= 2 && ! in_array($t, $boQua, true) && ! ctype_digit($t),
        ));

        $ketQua = $query->orderBy('DONGIA_SP')->get()->filter(function (SanPham $sp) use ($tuKhoa): bool {
            if ($tuKhoa === []) {
                return true;
            }
            $noiDung = Str::lower(Str::ascii(implode(' ', [
                $sp->TENSP, $sp->nhaSanXuat?->TENNSX, $sp->loaiSanPham?->TENLOAI,
                $sp->moTa?->CPU, $sp->moTa?->RAM, $sp->moTa?->VGA,
                $sp->moTa?->VGA && preg_match('/rtx|gtx/i', $sp->moTa->VGA) ? 'gaming' : '',
            ])));

            return collect($tuKhoa)->contains(fn (string $t): bool => str_contains($noiDung, $t));
        });

        // Không có từ khoá lẫn mức giá thì không coi là câu tìm sản phẩm
        if ($tuKhoa === [] && ! preg_match('/(duoi|tren)\s*\d+/', $cau)) {
            return [];
        }

        return $ketQua->take(4)->map(fn (SanPham $sp): array => [
            'ma' => $sp->MASP,
            'ten' => $sp->TENSP,
            'gia' => formatVND($sp->DONGIA_SP),
            'url' => route('sanpham.show', $sp->MASP),
        ])->values()->all();
    }
}
