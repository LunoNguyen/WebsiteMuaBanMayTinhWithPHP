<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\PhieuNhapHang;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NhapHangController extends Controller
{
    /**
     * Danh sách phiếu nhập hàng (lọc theo mã/NCC, trạng thái, nhà cung cấp).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');
        $mancc = (string) $request->query('mancc', '');

        $query = PhieuNhapHang::query()->toBase()
            ->from('PHIEUNHAPHANG as pnh')
            ->leftJoin('NHACUNGCAP as ncc', 'pnh.MANCC', '=', 'ncc.MANCC')
            ->leftJoin('NHANVIEN as nv', 'pnh.MANV', '=', 'nv.MANV')
            ->select('pnh.*', 'ncc.TENNCC', 'nv.TENNV')
            ->selectSub('SELECT SUM(SOLUONG) FROM CT_PHIEUNHAPHANG WHERE MAPNH = pnh.MAPNH', 'tong_sl')
            ->selectSub('SELECT COUNT(*) FROM CT_PHIEUNHAPHANG WHERE MAPNH = pnh.MAPNH', 'so_san_pham')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('pnh.MAPNH', 'like', "%{$search}%")
                ->orWhere('ncc.TENNCC', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('pnh.TRANGTHAI', $trangthai))
            ->when($mancc !== '', fn ($q) => $q->where('pnh.MANCC', $mancc))
            ->orderByDesc('pnh.NGAYTAO')
            ->orderBy('pnh.MAPNH');

        $trang = $this->phanTrang($query, 10);

        $statsTT = array_merge(
            ['ChuaThanhToan' => 0, 'DaThanhToan' => 0, 'HoanTien' => 0],
            PhieuNhapHang::query()->toBase()->selectRaw('TRANGTHAI_THANHTOAN, COUNT(*) AS cnt')
                ->groupBy('TRANGTHAI_THANHTOAN')->pluck('cnt', 'TRANGTHAI_THANHTOAN')->all(),
        );

        return view('admin.nhaphang', [
            'phieunhap' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'trangthai' => $trangthai,
            'mancc' => $mancc,
            'nccList' => $this->mang(NhaCungCap::query()->toBase()->orderBy('TENNCC')->get()),
            'statsTT' => $statsTT,
            'tongNhapThang' => ['tong' => PhieuNhapHang::query()
                ->whereMonth('NGAYTAO', now()->month)->whereYear('NGAYTAO', now()->year)->sum('TONGCONG_PNH')],
            'statusMap' => [
                'ChoDuyet' => ['var(--orange)', 'Chờ duyệt'],
                'DaDuyet' => ['var(--blue)', 'Đã duyệt'],
                'DaNhan' => ['var(--purple)', 'Đã nhận'],
                'HoanThanh' => ['var(--green)', 'Hoàn thành'],
                'DaHuy' => ['var(--red)', 'Đã hủy'],
            ],
            'ttMap' => [
                'ChuaThanhToan' => ['var(--orange)', 'Chưa TT'],
                'DaThanhToan' => ['var(--green)', 'Đã TT'],
                'HoanTien' => ['var(--text-secondary)', 'Hoàn tiền'],
            ],
        ]);
    }
}
