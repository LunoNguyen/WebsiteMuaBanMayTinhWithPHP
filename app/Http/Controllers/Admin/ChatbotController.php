<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LichSuChatbot;
use App\Models\PhienChatbot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    /**
     * Danh sách phiên chatbot và nội dung phiên đang chọn.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $selectedPhien = (int) $request->query('maphien', 0);

        $phienQuery = fn () => PhienChatbot::query()->toBase()
            ->from('PHIEN_CHATBOT as pc')
            ->leftJoin('KHACHHANG as kh', 'pc.MAKH', '=', 'kh.MAKH')
            ->leftJoin('TAIKHOAN as tk', 'pc.MATK', '=', 'tk.MATK')
            ->select('pc.*', 'kh.TENKH', 'tk.EMAIL_TK');

        $query = $phienQuery()
            ->selectSub('SELECT COUNT(*) FROM LICHSU_CHATBOT WHERE MAPHIEN = pc.MAPHIEN', 'so_tin')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('tk.EMAIL_TK', 'like', "%{$search}%")))
            ->orderByDesc('pc.THOIGIAN_BD')
            ->orderByDesc('pc.MAPHIEN');

        $trang = $this->phanTrang($query, 10);

        $currentPhien = $selectedPhien > 0 ? $phienQuery()->where('pc.MAPHIEN', $selectedPhien)->first() : null;

        return view('admin.chatbot', [
            'phienList' => $trang['rows'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'selectedPhien' => $selectedPhien,
            'currentPhien' => $currentPhien ? (array) $currentPhien : null,
            'messages' => $currentPhien
                ? $this->mang(LichSuChatbot::query()->toBase()->where('MAPHIEN', $selectedPhien)->orderBy('THOIGIAN')->orderBy('MATIN')->get())
                : [],
            'statsTotal' => ['cnt' => PhienChatbot::query()->count()],
            'statsToday' => ['cnt' => PhienChatbot::query()->whereDate('THOIGIAN_BD', today())->count()],
            'statsActive' => ['cnt' => PhienChatbot::query()->where('TRANGTHAI', 'DangChat')->count()],
        ]);
    }
}
