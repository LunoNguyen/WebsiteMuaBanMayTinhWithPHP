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
     * Đơn hàng của khách đang đăng nhập.
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
     * Chi tiết một đơn (chỉ xem được đơn của mình).
     */
    public function show(Request $request, HoaDon $hoaDon): View
    {
        abort_unless($hoaDon->MATK === $request->user()->MATK, 404);

        $hoaDon->load(['chiTiets.sanPham.anhs', 'chiTiets.sanPham.nhaSanXuat', 'thanhToan']);

        return view('shop.chi-tiet-don', ['hd' => $hoaDon]);
    }

    /**
     * Khách tự huỷ đơn khi đơn còn chờ xác nhận.
     */
    public function huy(Request $request, HoaDon $hoaDon): RedirectResponse
    {
        abort_unless($hoaDon->MATK === $request->user()->MATK, 404);

        if ($hoaDon->TRANGTHAI !== 'ChoXacNhan' || ! $hoaDon->huy()) {
            return back()->with('thong_bao', 'Đơn hàng đã được xác nhận nên không thể tự huỷ. Vui lòng liên hệ cửa hàng.');
        }

        return back()->with('thong_bao', "Đã huỷ đơn {$hoaDon->MAHD}.");
    }
}
