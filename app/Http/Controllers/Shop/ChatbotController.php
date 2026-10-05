<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Nhận câu hỏi của khách (đăng nhập hay chưa đều được), trả lời và lưu lịch sử chat.
     */
    public function store(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $cauHoi = trim((string) $request->validate(['noi_dung' => ['required', 'string', 'max:500']])['noi_dung']);
        $taiKhoan = $request->user()?->LOAI_TAIKHOAN === 'KhachHang' ? $request->user() : null;

        $phien = $chatbot->phien($request->session()->get('chatbot_phien'), $taiKhoan);
        $request->session()->put('chatbot_phien', $phien->MAPHIEN);

        return response()->json($chatbot->hoi($phien, $cauHoi, $taiKhoan));
    }
}
