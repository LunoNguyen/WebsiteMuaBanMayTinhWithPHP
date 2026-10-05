<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ThemGioHangRequest;
use App\Models\CtGioHang;
use App\Models\SanPham;
use App\Services\GioHangService;
use App\Services\KhuyenMaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GioHangController extends Controller
{
    public function __construct(
        private GioHangService $gioHang,
        private KhuyenMaiService $khuyenMai,
    ) {}

    /**
     * Giỏ hàng, tạm tính và mã giảm giá đang áp (tính lại mỗi lần xem).
     */
    public function index(Request $request): View
    {
        $dong = $this->gioHang->dong($request->user());
        $tamTinh = $this->gioHang->tamTinh($dong);
        $ma = $request->session()->get('ma_giam_gia');
        $giam = 0.0;
        $khuyenMai = null;

        if ($ma !== null) {
            try {
                ['khuyenMai' => $khuyenMai, 'giam' => $giam] = $this->khuyenMai->apDung($ma, $dong, $request->user());
            } catch (ValidationException $e) {
                $request->session()->forget('ma_giam_gia');
                $request->session()->now('loi_ma', $e->validator->errors()->first('ma_code'));
            }
        }

        return view('shop.gio-hang', [
            'dong' => $dong,
            'tamTinh' => $tamTinh,
            'khuyenMai' => $khuyenMai,
            'giam' => $giam,
            'tong' => $tamTinh - $giam,
            'goiY' => $this->khuyenMai->dangApDung($request->user()),
            'dieuKien' => fn ($km): array => $this->khuyenMai->dieuKien($km),
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ.
     */
    public function store(ThemGioHangRequest $request, SanPham $sanPham): RedirectResponse
    {
        $this->gioHang->them($request->user(), $sanPham, (int) $request->validated('so_luong'));

        if ($request->boolean('mua_ngay')) {
            return redirect()->route('giohang.index');
        }

        return back()->with('thong_bao', "Đã thêm {$sanPham->TENSP} vào giỏ hàng.");
    }

    /**
     * Đổi số lượng một dòng.
     */
    public function update(Request $request, CtGioHang $ctGioHang): RedirectResponse
    {
        abort_unless($this->gioHang->cuaTaiKhoan($ctGioHang, $request->user()), 404);
        $soLuong = (int) $request->validate(['so_luong' => ['required', 'integer', 'min:1', 'max:99']])['so_luong'];

        $this->gioHang->capNhat($ctGioHang, $soLuong);

        return back();
    }

    /**
     * Bỏ một dòng khỏi giỏ.
     */
    public function destroy(Request $request, CtGioHang $ctGioHang): RedirectResponse
    {
        abort_unless($this->gioHang->cuaTaiKhoan($ctGioHang, $request->user()), 404);
        $ctGioHang->delete();

        return back()->with('thong_bao', 'Đã xoá sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Áp mã giảm giá (kiểm tra ngay; lưu mã trong session để dùng lúc thanh toán).
     */
    public function apMa(Request $request): RedirectResponse
    {
        $ma = trim((string) $request->validate(['ma_code' => ['required', 'string', 'max:50']])['ma_code']);
        $dangDung = $request->session()->get('ma_giam_gia');

        // Mỗi đơn chỉ một mã: phải bỏ mã đang áp rồi mới áp mã khác
        if ($dangDung !== null && strcasecmp($dangDung, $ma) !== 0) {
            throw ValidationException::withMessages([
                'ma_code' => "Mỗi đơn chỉ dùng được một mã giảm giá. Bỏ mã {$dangDung} trước khi áp mã khác.",
            ]);
        }

        ['khuyenMai' => $khuyenMai, 'giam' => $giam] = $this->khuyenMai->apDung($ma, $this->gioHang->dong($request->user()), $request->user());

        $request->session()->put('ma_giam_gia', $khuyenMai->MA_CODE);

        return back()->with('thong_bao', "Đã áp dụng mã {$khuyenMai->MA_CODE}, giảm ".formatVND($giam).'.');
    }

    /**
     * Bỏ mã giảm giá đang áp.
     */
    public function boMa(Request $request): RedirectResponse
    {
        $request->session()->forget('ma_giam_gia');

        return back();
    }
}
