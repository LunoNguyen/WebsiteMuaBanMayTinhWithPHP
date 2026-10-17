<?php

namespace App\Http\Controllers\Kho;

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
     * Trạng thái đơn mà kho cần xử lý.
     *
     * @var list<string>
     */
    private const KHO_XU_LY = ['DaXacNhan', 'DangGiao', 'DaGiao'];

    /**
     * Đơn hàng cần xuất kho / đang giao / đã giao (bộ lọc dùng chung ở trait LocDonHang).
     */
    public function index(Request $request): View
    {
        $duLieu = $this->duLieuDanhSachDon($request, self::KHO_XU_LY);

        return view('kho.donhang', [
            ...$duLieu,
            'dem' => $this->hangDem($duLieu['statMap'], self::KHO_XU_LY),
        ]);
    }

    /**
     * Chi tiết đơn để soạn hàng.
     */
    public function show(HoaDon $hoaDon): View
    {
        return view('kho.chi-tiet-don', ['hd' => $hoaDon->napChiTiet()]);
    }

    /**
     * Xuất kho và bàn giao vận chuyển: đơn đã xác nhận chuyển sang đang giao.
     */
    public function xuatKho(Request $request, HoaDon $hoaDon): RedirectResponse
    {
        if ($hoaDon->TRANGTHAI !== 'DaXacNhan') {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không ở trạng thái chờ xuất kho.")->with('loai', 'danger');
        }

        // Kiểm tra thanh toán đơn nhập kho cho nhà cung cấp: CHẶN HOÀN TOÀN nếu chưa thanh toán
        $chuaThanhToan = $hoaDon->pnhChuaThanhToanNCC();
        if ($chuaThanhToan->isNotEmpty()) {
            $dsPnh = $chuaThanhToan->pluck('MAPNH')->unique()->implode(', ');
            $dsSp = $chuaThanhToan->map(fn ($p) => "{$p->MASP} ({$p->TENSP}) - Phiếu nhập {$p->MAPNH}")->unique()->implode('; ');

            return back()
                ->with('loai', 'danger')
                ->with('thong_bao', "CHẶN XUẤT KHO: Đơn hàng có mặt hàng thuộc phiếu nhập NCC chưa thanh toán [{$dsPnh}]! Chi tiết: {$dsSp}. Vui lòng thanh toán cho NCC trước khi xuất kho!");
        }

        $hoaDon->chuyenBuocTiepTheo($request->user()->MANV);

        return back()->with('thong_bao', "Đã xuất kho & bàn giao vận chuyển đơn {$hoaDon->MAHD}!");
    }
}
