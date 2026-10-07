<?php

namespace App\Http\Controllers\BanHang;

use App\Http\Controllers\Concerns\LocDonHang;
use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonHangController extends Controller
{
    use LocDonHang;

    /**
     * Danh sách đơn hàng cho nhân viên bán hàng (bộ lọc dùng chung ở trait LocDonHang).
     */
    public function index(Request $request): View
    {
        $duLieu = $this->duLieuDanhSachDon($request);

        return view('banhang.donhang', [
            ...$duLieu,
            'dem' => $this->hangDem($duLieu['statMap'], ['ChoXacNhan', 'DaXacNhan', 'DangGiao', 'DaGiao', 'HoanThanh', 'DaHuy']),
            'actionLabel' => HoaDon::NHAN_BUOC_TIEP_THEO,
        ]);
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show(HoaDon $hoaDon): View
    {
        return view('banhang.chi-tiet-don', ['hd' => $hoaDon->napChiTiet()]);
    }

    /**
     * Ghi nhận đơn đã thanh toán (khách chuyển khoản / trả tại quầy).
     */
    public function thanhToan(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->xacNhanThanhToan()) {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không có khoản chờ thanh toán.")->with('loai', 'info');
        }

        return back()->with('thong_bao', "Đã ghi nhận thanh toán đơn {$hoaDon->MAHD}.");
    }

    /**
     * Chuyển đơn sang bước kế tiếp của quy trình.
     */
    public function buocTiepTheo(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->chuyenBuocTiepTheo()) {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không thể chuyển tiếp.")->with('loai', 'info');
        }

        return back()->with('thong_bao', "Cập nhật trạng thái đơn {$hoaDon->MAHD} thành công!");
    }

    /**
     * Huỷ đơn còn ở bước chờ / đã xác nhận.
     */
    public function huy(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->huy()) {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không thể hủy ở trạng thái hiện tại.")->with('loai', 'info');
        }

        return back()->with('thong_bao', "Đã hủy đơn hàng {$hoaDon->MAHD}.")->with('loai', 'info');
    }

    public function inHoaDon(HoaDon $hoaDon): View
    {
        return view('banhang.in-hoa-don', ['hd' => $hoaDon->napChiTiet()]);
    }
}
