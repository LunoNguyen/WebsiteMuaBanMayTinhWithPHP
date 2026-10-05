<?php

namespace App\Http\Controllers\BanHang;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonHangController extends Controller
{
    /**
     * Danh sách đơn hàng cho nhân viên bán hàng.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $query = HoaDon::query()->toBase()
            ->from('HOADON as hd')
            ->leftJoin('KHACHHANG as kh', 'hd.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'hd.MANV', '=', 'nv.MANV')
            ->select('hd.*', 'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV')
            ->selectSub('SELECT COUNT(*) FROM CHITIETHOADON WHERE MAHD = hd.MAHD', 'so_sp')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('hd.MAHD', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('hd.TEN_NGUOINHAN', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('hd.TRANGTHAI', $trangthai))
            ->when($from !== '', fn ($q) => $q->whereDate('hd.NGAYLAP', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('hd.NGAYLAP', '<=', $to))
            ->orderByDesc('hd.NGAYLAP')
            ->orderBy('hd.MAHD');

        $perPage = 12;
        $trang = $this->phanTrang($query, $perPage);
        $thangNay = fn () => HoaDon::query()->whereMonth('NGAYLAP', now()->month)->whereYear('NGAYLAP', now()->year);

        return view('banhang.donhang', [
            'donhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'trangthai' => $trangthai,
            'from' => $from,
            'to' => $to,
            'kpiCho' => ['c' => HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->count()],
            'kpiGiao' => ['c' => HoaDon::query()->where('TRANGTHAI', 'DangGiao')->count()],
            'kpiHT' => ['c' => $thangNay()->where('TRANGTHAI', 'HoanThanh')->count()],
            'kpiDT' => ['t' => $thangNay()->whereIn('TRANGTHAI', ['DaGiao', 'HoanThanh'])->sum('TONGTIEN_HD')],
            'validTransitions' => HoaDon::BUOC_TIEP_THEO,
            'stMap' => [
                'ChoXacNhan' => ['var(--orange)', 'Chờ Xác Nhận'],
                'DaXacNhan' => ['var(--blue)', 'Đã Xác Nhận'],
                'DangGiao' => ['var(--purple)', 'Đang Giao'],
                'DaGiao' => ['var(--cyan)', 'Đã Giao'],
                'HoanThanh' => ['var(--green)', 'Hoàn Thành'],
                'DaHuy' => ['var(--red)', 'Đã Hủy'],
            ],
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
}
