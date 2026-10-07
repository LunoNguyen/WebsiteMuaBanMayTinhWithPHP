<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonHangController extends Controller
{
    /**
     * ÄÆ¡n hÃ ng cá»§a khÃ¡ch Ä‘ang Ä‘Äƒng nháº­p.
     */
    public function index(Request $request): View
    {
        return view('shop.don-hang', [
            'donHang' => HoaDon::query()
                ->where('MATK', $request->user()->MATK)
                ->with('thanhToan')
                ->withCount('chiTiets')
                ->latest('NGAYLAP')
                ->paginate(10),
        ]);
    }

    /**
     * Chi tiáº¿t má»™t Ä‘Æ¡n (chá»‰ xem Ä‘Æ°á»£c Ä‘Æ¡n cá»§a mÃ¬nh).
     */
    public function show(Request $request, HoaDon $hoaDon): View
    {
        abort_unless($hoaDon->MATK === $request->user()->MATK, 404);

        $hoaDon->load(['chiTiets.sanPham.anhs', 'chiTiets.sanPham.nhaSanXuat', 'thanhToan']);

        return view('shop.chi-tiet-don', ['hd' => $hoaDon]);
    }

    /**
     * KhÃ¡ch tá»± huá»· Ä‘Æ¡n khi Ä‘Æ¡n cÃ²n chá» xÃ¡c nháº­n.
     */
    public function inHoaDon(Request $request, HoaDon $hoaDon): \Illuminate\View\View
    {
        abort_unless($hoaDon->MATK === $request->user()->MATK, 404);
        return view('banhang.in-hoa-don', ['hd' => $hoaDon->napChiTiet()]);
    }

    public function huy(Request $request, HoaDon $hoaDon): RedirectResponse
    {
        abort_unless($hoaDon->MATK === $request->user()->MATK, 404);

        if ($hoaDon->TRANGTHAI !== 'ChoXacNhan' || ! $hoaDon->huy()) {
            return back()->with('thong_bao', 'ÄÆ¡n hÃ ng Ä‘Ã£ Ä‘Æ°á»£c xÃ¡c nháº­n nÃªn khÃ´ng thá»ƒ tá»± huá»·. Vui lÃ²ng liÃªn há»‡ cá»­a hÃ ng.');
        }

        return back()->with('thong_bao', "ÄÃ£ huá»· Ä‘Æ¡n {$hoaDon->MAHD}.");
    }
}

