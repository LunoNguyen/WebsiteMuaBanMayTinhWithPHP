<?php

namespace App\Http\Controllers;

use App\Models\HoaDon;
use App\Services\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    /**
     * Tiếp nhận Webhook từ SePay khi có biến động số dư MBBank.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. KIỂM TRA BẢO MẬT API KEY
        $apiKey = env('SEPAY_API_KEY');
        $authHeader = (string) $request->header('Authorization');

        if ($apiKey && $authHeader !== "Apikey {$apiKey}" && $authHeader !== $apiKey) {
            Log::warning('SePay Webhook: Sai mã bảo mật API Key', ['header' => $authHeader]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // 2. LẤY THÔNG TIN GIAO DỊCH TỪ SEPAY
        $content = (string) ($request->input('content') ?? $request->input('description') ?? '');
        $transferAmount = (float) ($request->input('transferAmount') ?? $request->input('amountIn') ?? 0);
        $referenceCode = (string) ($request->input('referenceCode') ?? $request->input('id') ?? '');
        $gateway = (string) ($request->input('gateway') ?? 'MBBank');

        // 3. TÌM MÃ ĐƠN HÀNG TRONG NỘI DUNG CHUYỂN KHOẢN (ví dụ: "TT HD015" -> lấy "HD015")
        if (! preg_match('/(HD\d+)/i', $content, $matches)) {
            Log::info("SePay Webhook: Nội dung không chứa mã đơn hàng: {$content}");
            return response()->json(['success' => false, 'message' => 'Không tìm thấy mã đơn hàng'], 200);
        }

        $maHd = strtoupper($matches[1]);
        $hoaDon = HoaDon::query()->with('thanhToan')->where('MAHD', $maHd)->first();

        if (! $hoaDon) {
            Log::warning("SePay Webhook: Không tìm thấy đơn hàng {$maHd} trong CSDL");
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Nếu đơn đã được xác nhận và thanh toán xong rồi thì bỏ qua không xử lý lại
        if ($hoaDon->TRANGTHAI !== 'ChoXacNhan' && $hoaDon->thanhToan?->TRANGTHAI === 'DaThanhToan') {
            return response()->json(['success' => true, 'message' => 'Đơn hàng đã được xác nhận trước đó']);
        }

        // 4. KIỂM TRA ĐÚNG SỐ TIỀN CỦA ĐƠN HÀNG
        $tongTienCanThanhToan = (float) $hoaDon->TONGTIEN_HD;

        if ($transferAmount < $tongTienCanThanhToan) {
            Log::warning("SePay Webhook: Đơn {$maHd} chuyển thiếu tiền. Cần: {$tongTienCanThanhToan}, Thực nhận: {$transferAmount}");
            $hoaDon->thanhToan?->update([
                'GHI_CHU' => "Chuyển thiếu: nhận " . number_format($transferAmount) . "đ / cần " . number_format($tongTienCanThanhToan) . "đ",
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Số tiền chuyển không đủ cho đơn hàng',
            ], 400);
        }

        // 5. ĐÚNG SỐ TIỀN -> TỰ ĐỘNG CHUYỂN TRẠNG THÁI "ĐÃ XÁC NHẬN" & "ĐÃ THANH TOÁN"
        DB::transaction(function () use ($hoaDon, $referenceCode, $gateway) {
            // Đổi trạng thái hoá đơn sang Đã Xác Nhận
            $hoaDon->update([
                'TRANGTHAI' => 'DaXacNhan',
            ]);

            // Đổi trạng thái thanh toán sang Đã Thanh Toán
            $hoaDon->thanhToan?->update([
                'TRANGTHAI' => 'DaThanhToan',
                'NGAY_THANHTOAN' => now(),
                'MA_GIAODICH' => $referenceCode,
                'NGAN_HANG' => $gateway,
                'GHI_CHU' => 'Thanh toán tự động qua SePay (MBBank)',
            ]);
        });

        // 6. PHÁT SỰ KIỆN REALTIME (REVERB) ĐỂ MÀN HÌNH TỰ ĐỔI MÀ KHÔNG CẦN F5
        Realtime::donHang($hoaDon);

        return response()->json([
            'success' => true,
            'message' => "Đã xác nhận đơn hàng {$maHd} thành công",
        ]);
    }
}