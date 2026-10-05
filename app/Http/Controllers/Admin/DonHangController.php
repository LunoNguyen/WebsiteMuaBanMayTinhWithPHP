<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shop\ThanhToanController;
use App\Http\Requests\Admin\TaoDonHangRequest;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\SanPham;
use App\Services\DatHangService;
use App\Services\KhuyenMaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonHangController extends Controller
{
    /**
     * Danh sách đơn hàng (lọc theo từ khoá, trạng thái, hình thức giao, khoảng ngày).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');
        $ptgh = (string) $request->query('ptgh', '');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $query = HoaDon::query()->toBase()
            ->from('HOADON as hd')
            ->leftJoin('KHACHHANG as kh', 'hd.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'hd.MANV', '=', 'nv.MANV')
            ->leftJoin('THANHTOAN as tt', 'hd.MAHD', '=', 'tt.MAHD')
            ->select('hd.MAHD', 'hd.NGAYLAP', 'hd.TRANGTHAI', 'hd.TONGTIEN_HD', 'hd.TONGTIEN_GIAM',
                'hd.TEN_NGUOINHAN', 'hd.SDT_NGUOINHAN', 'hd.DIACHI_GIAOHANG', 'hd.PHUONG_THUC_GH',
                'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV', 'tt.PHUONG_THUC as PT_TT', 'tt.TRANGTHAI as TT_TT')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('hd.MAHD', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('hd.TEN_NGUOINHAN', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('hd.TRANGTHAI', $trangthai))
            ->when($request->filled('makh'), fn ($q) => $q->where('hd.MAKH', (string) $request->query('makh')))
            ->when($ptgh !== '', fn ($q) => $q->where('hd.PHUONG_THUC_GH', $ptgh))
            ->when($from !== '', fn ($q) => $q->whereDate('hd.NGAYLAP', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('hd.NGAYLAP', '<=', $to))
            ->orderByDesc('hd.NGAYLAP')
            ->orderBy('hd.MAHD');

        $trang = $this->phanTrang($query, 12);

        return view('admin.donhang', [
            'donhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'trangthai' => $trangthai,
            'ptgh' => $ptgh,
            'from' => $from,
            'to' => $to,
            'statMap' => HoaDon::query()->toBase()->selectRaw('TRANGTHAI, COUNT(*) AS cnt')
                ->groupBy('TRANGTHAI')->pluck('cnt', 'TRANGTHAI')->all(),
            'validTransitions' => HoaDon::BUOC_TIEP_THEO,
            'actionLabel' => HoaDon::NHAN_BUOC_TIEP_THEO,
        ]);
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show(HoaDon $hoaDon): View
    {
        return view('admin.donhang-chitiet', ['hd' => $hoaDon->napChiTiet()]);
    }

    /**
     * Form tạo đơn tại quầy.
     */
    public function create(KhuyenMaiService $khuyenMai): View
    {
        return view('admin.donhang-form', [
            'khachHangList' => KhachHang::query()->orderBy('TENKH')->get(['MAKH', 'TENKH', 'SDT_KH', 'DIACHI_KH']),
            'sanPhamList' => SanPham::query()->where('TRANGTHAI', 'DangBan')->where('SOLUONGTON', '>', 0)
                ->orderBy('TENSP')->get(['MASP', 'TENSP', 'DONGIA_SP', 'SOLUONGTON']),
            'maDangChay' => $khuyenMai->dangApDung(),
            'phuongThuc' => ThanhToanController::PHUONG_THUC,
            'dong' => old('san_pham', [['MASP' => null, 'SOLUONG' => 1]]),
        ]);
    }

    /**
     * Tạo đơn tại quầy: kiểm tra tồn kho, áp mã (một mã, mỗi khách một lần), trừ tồn kho.
     */
    public function store(TaoDonHangRequest $request, DatHangService $datHang): RedirectResponse
    {
        $hoaDon = $datHang->taoDonTaiQuay(
            $request->user()->MANV,
            $request->validated('MAKH'),
            $request->validated('san_pham'),
            $request->safe()->only(['TEN_NGUOINHAN', 'SDT_NGUOINHAN', 'DIACHI_GIAOHANG', 'PHUONG_THUC_GH', 'PHUONG_THUC', 'GHI_CHU']),
            $request->validated('MA_CODE'),
        );

        return redirect()->route('admin.donhang.show', $hoaDon->MAHD)->with('thong_bao', "Đã tạo đơn {$hoaDon->MAHD}.");
    }

    /**
     * Ghi nhận đơn đã thanh toán.
     */
    public function thanhToan(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->xacNhanThanhToan()) {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không có khoản chờ thanh toán.")->with('loai', 'danger');
        }

        return back()->with('thong_bao', "Đã ghi nhận thanh toán đơn {$hoaDon->MAHD}.");
    }

    /**
     * Chuyển đơn sang bước kế tiếp của quy trình.
     */
    public function buocTiepTheo(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->chuyenBuocTiepTheo()) {
            return back()->with('thong_bao', "Đơn hàng {$hoaDon->MAHD} không thể chuyển tiếp.")->with('loai', 'danger');
        }

        return back()->with('thong_bao', "Cập nhật trạng thái đơn hàng {$hoaDon->MAHD} thành công!");
    }

    /**
     * Huỷ đơn còn ở bước chờ / đã xác nhận.
     */
    public function huy(HoaDon $hoaDon): RedirectResponse
    {
        if (! $hoaDon->huy()) {
            return back()->with('thong_bao', "Đơn hàng {$hoaDon->MAHD} không thể hủy ở trạng thái hiện tại.")->with('loai', 'danger');
        }

        return back()->with('thong_bao', "Đã hủy đơn hàng {$hoaDon->MAHD}.")->with('loai', 'info');
    }
}
