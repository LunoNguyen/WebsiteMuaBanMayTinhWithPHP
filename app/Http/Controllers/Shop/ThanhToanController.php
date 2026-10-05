<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\DatHangRequest;
use App\Services\DatHangService;
use App\Services\GioHangService;
use App\Services\KhuyenMaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ThanhToanController extends Controller
{
    /**
     * Phương thức thanh toán khách được chọn.
     *
     * @var array<string, string>
     */
    public const PHUONG_THUC = [
        'COD' => 'Thanh toán khi nhận hàng (COD)',
        'ChuyenKhoan' => 'Chuyển khoản ngân hàng',
        'QR' => 'Quét mã QR',
    ];

    public function __construct(
        private GioHangService $gioHang,
        private KhuyenMaiService $khuyenMai,
    ) {}

    /**
     * Form thông tin nhận hàng và tóm tắt đơn.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $taiKhoan = $request->user();
        $dong = $this->gioHang->dong($taiKhoan);

        if ($dong->isEmpty()) {
            return redirect()->route('giohang.index')->with('thong_bao', 'Giỏ hàng đang trống.');
        }

        $tamTinh = $this->gioHang->tamTinh($dong);
        $giam = 0.0;
        $khuyenMai = null;

        if ($ma = $request->session()->get('ma_giam_gia')) {
            try {
                ['khuyenMai' => $khuyenMai, 'giam' => $giam] = $this->khuyenMai->apDung($ma, $dong, $taiKhoan);
            } catch (ValidationException) {
                $request->session()->forget('ma_giam_gia');
            }
        }

        return view('shop.thanh-toan', [
            'dong' => $dong,
            'khachHang' => $taiKhoan->khachHang,
            'tamTinh' => $tamTinh,
            'khuyenMai' => $khuyenMai,
            'giam' => $giam,
            'tong' => $tamTinh - $giam,
            'phuongThuc' => self::PHUONG_THUC,
        ]);
    }

    /**
     * Đặt hàng.
     */
    public function store(DatHangRequest $request, DatHangService $datHang): RedirectResponse
    {
        $hoaDon = $datHang->datHang($request->user(), $request->validated(), $request->session()->get('ma_giam_gia'));
        $request->session()->forget('ma_giam_gia');

        return redirect()->route('donhang.show', $hoaDon->MAHD)
            ->with('thong_bao', "Đặt hàng thành công! Mã đơn của bạn là {$hoaDon->MAHD}.");
    }
}
