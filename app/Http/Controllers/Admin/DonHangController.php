<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
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
