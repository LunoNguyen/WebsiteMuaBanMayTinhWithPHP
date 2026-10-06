<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\DatHangRequest;
use App\Jobs\XuLyDatHang;
use App\Services\DatHangService;
use App\Services\GioHangService;
use App\Services\KhuyenMaiService;
use App\Services\YeuCauDatHang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
    public function store(DatHangRequest $request, DatHangService $datHang, YeuCauDatHang $yeuCau): RedirectResponse
    {
        $taiKhoan = $request->user();
        $maYeuCau = $request->validated('ma_yeu_cau') ?? (string) Str::uuid();
        $duLieu = [
            'san_pham' => $datHang->chupGioHang($taiKhoan),
            'thong_tin' => $request->safe()->except(['ma_yeu_cau']),
            'ma_code' => $request->session()->get('ma_giam_gia'),
        ];

        // Mã này đã được nhận (bấm hai lần, trình duyệt gửi lại): không xử lý lần nữa, chỉ báo kết quả của lần trước
        if (! $yeuCau->nhan($taiKhoan->MATK, $maYeuCau, $duLieu)) {
            return $this->ketQua($request, $yeuCau, $maYeuCau);
        }

        if ($duLieu['san_pham'] === []) {
            $yeuCau->loi($taiKhoan->MATK, $maYeuCau, 'Giỏ hàng đang trống.');

            return $this->ketQua($request, $yeuCau, $maYeuCau);
        }

        $job = XuLyDatHang::tuDuLieu($taiKhoan->MATK, $maYeuCau, $duLieu);

        // Có worker thì xếp hàng xử lý lần lượt; không có worker thì xử lý ngay, khách không phải chờ vô hạn
        if ($yeuCau->coWorker()) {
            dispatch($job);
        } else {
            $this->xuLyNgay($job);
        }

        return $this->ketQua($request, $yeuCau, $maYeuCau);
    }

    /**
     * Trang chờ trong lúc đơn đang nằm trong hàng đợi.
     */
    public function dangXuLy(Request $request, YeuCauDatHang $yeuCau, string $maYeuCau): View|RedirectResponse
    {
        $trangThai = $this->cuuNeuBiKet($request, $yeuCau, $maYeuCau);

        if (($trangThai['trang_thai'] ?? null) !== YeuCauDatHang::CHO) {
            return $this->ketQua($request, $yeuCau, $maYeuCau);
        }

        return view('shop.dang-xu-ly', ['maYeuCau' => $maYeuCau]);
    }

    /**
     * Trạng thái để trang chờ hỏi định kỳ (dự phòng khi realtime không chạy).
     */
    public function trangThai(Request $request, YeuCauDatHang $yeuCau, string $maYeuCau): JsonResponse
    {
        $trangThai = $this->cuuNeuBiKet($request, $yeuCau, $maYeuCau);
        abort_if($trangThai === null, 404);

        return response()->json([
            'trang_thai' => $trangThai['trang_thai'],
            'url' => route('thanhtoan.ket-qua', $maYeuCau),
        ]);
    }

    /**
     * Job đã vào hàng đợi nhưng worker đã tắt: tự xử lý yêu cầu ngay tại đây.
     * Sau này worker bật lại và lấy job cũ ra thì job thấy yêu cầu không còn "chờ" nên bỏ qua, không tạo đơn trùng.
     *
     * @return array<string, mixed>|null
     */
    private function cuuNeuBiKet(Request $request, YeuCauDatHang $yeuCau, string $maYeuCau): ?array
    {
        $maTaiKhoan = $request->user()->MATK;
        $trangThai = $yeuCau->trangThai($maTaiKhoan, $maYeuCau);

        if ($yeuCau->biKet($trangThai)) {
            $this->xuLyNgay(XuLyDatHang::tuDuLieu($maTaiKhoan, $maYeuCau, $trangThai['du_lieu']));
            $trangThai = $yeuCau->trangThai($maTaiKhoan, $maYeuCau);
        }

        return $trangThai;
    }

    /**
     * Chạy job ngay trong request này (không qua hàng đợi); lỗi bất ngờ thì báo khách như khi worker lỗi.
     */
    private function xuLyNgay(XuLyDatHang $job): void
    {
        try {
            app()->call([$job, 'handle']);
        } catch (Throwable $exception) {
            report($exception);
            $job->failed($exception);
        }
    }

    /**
     * Chuyển khách tới đúng chỗ theo kết quả: đơn vừa tạo, giỏ hàng kèm lý do lỗi, hoặc trang chờ.
     */
    public function ketQua(Request $request, YeuCauDatHang $yeuCau, string $maYeuCau): RedirectResponse
    {
        $trangThai = $yeuCau->trangThai($request->user()->MATK, $maYeuCau);

        // Mã giảm giá chỉ bỏ khỏi giỏ khi đơn đã thành công; lỗi thì khách giữ mã để đặt lại
        if (($trangThai['trang_thai'] ?? null) === YeuCauDatHang::XONG) {
            $request->session()->forget('ma_giam_gia');
        }

        return match ($trangThai['trang_thai'] ?? null) {
            YeuCauDatHang::XONG => redirect()->route('donhang.show', $trangThai['ma_hd'])
                ->with('thong_bao', "Đặt hàng thành công! Mã đơn của bạn là {$trangThai['ma_hd']}."),
            YeuCauDatHang::LOI => redirect()->route('giohang.index')->withErrors([$trangThai['truong'] ?? 'gio_hang' => $trangThai['loi']]),
            YeuCauDatHang::CHO => redirect()->route('thanhtoan.dang-xu-ly', $maYeuCau),
            default => redirect()->route('giohang.index'),
        };
    }
}
