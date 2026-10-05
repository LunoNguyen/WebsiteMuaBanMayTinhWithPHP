<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\CtPhieuNhapHang;
use App\Models\NhaCungCap;
use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NhapHangController extends Controller
{
    /**
     * Phiếu còn đang chờ kiểm đếm (chưa cộng vào tồn kho).
     *
     * @var list<string>
     */
    private const CHO_KIEM_DEM = ['ChoDuyet', 'DaDuyet', 'DaNhan'];

    /**
     * Danh sách phiếu nhập và chi tiết phiếu đang chọn.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $filter = (string) $request->query('filter', '');
        $mancc = (string) $request->query('mancc', '');
        $thang = (string) $request->query('thang', now()->format('Y-m'));

        $query = PhieuNhapHang::query()->toBase()
            ->from('PHIEUNHAPHANG as pnh')
            ->leftJoin('NHACUNGCAP as ncc', 'pnh.MANCC', '=', 'ncc.MANCC')
            ->leftJoin('NHANVIEN as nv', 'pnh.MANV', '=', 'nv.MANV')
            ->select('pnh.*', 'ncc.TENNCC', 'nv.TENNV')
            ->selectSub('SELECT COUNT(*) FROM CT_PHIEUNHAPHANG WHERE MAPNH = pnh.MAPNH', 'so_sku')
            ->selectSub('SELECT SUM(SOLUONG) FROM CT_PHIEUNHAPHANG WHERE MAPNH = pnh.MAPNH', 'tong_sl')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('pnh.MAPNH', 'like', "%{$search}%")
                ->orWhere('ncc.TENNCC', 'like', "%{$search}%")))
            ->when($filter === 'cho', fn ($q) => $q->whereIn('pnh.TRANGTHAI', ['ChoDuyet', 'DaDuyet']))
            ->when($mancc !== '', fn ($q) => $q->where('pnh.MANCC', $mancc))
            ->when($thang !== '', fn ($q) => $q->whereRaw("DATE_FORMAT(pnh.NGAYTAO, '%Y-%m') = ?", [$thang]))
            ->orderByDesc('pnh.NGAYTAO')
            ->orderBy('pnh.MAPNH');

        $perPage = 8;
        $trang = $this->phanTrang($query, $perPage);
        $phieunhap = $trang['rows'];

        $selectedMapnh = (string) $request->query('pn', $phieunhap[0]['MAPNH'] ?? '');
        $detail = null;
        $ctpnList = [];

        if ($selectedMapnh !== '') {
            $detail = PhieuNhapHang::query()->toBase()
                ->from('PHIEUNHAPHANG as pnh')
                ->leftJoin('NHACUNGCAP as ncc', 'pnh.MANCC', '=', 'ncc.MANCC')
                ->leftJoin('NHANVIEN as nv', 'pnh.MANV', '=', 'nv.MANV')
                ->where('pnh.MAPNH', $selectedMapnh)
                ->first(['pnh.*', 'ncc.TENNCC', 'nv.TENNV', 'nv.MANV']);

            $ctpnList = $this->mang(CtPhieuNhapHang::query()->toBase()
                ->from('CT_PHIEUNHAPHANG as ct')
                ->join('SANPHAM as sp', 'ct.MASP', '=', 'sp.MASP')
                ->leftJoin('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
                ->leftJoin('NHASANXUA as nsx', 'sp.MANSX', '=', 'nsx.MANSX')
                ->where('ct.MAPNH', $selectedMapnh)
                ->orderBy('ct.MASP')
                ->get(['ct.*', 'sp.TENSP', 'sp.MASP', 'sp.SOLUONGTON', 'lsp.TENLOAI', 'nsx.TENNSX']));
        }

        $thangNay = PhieuNhapHang::query()->whereMonth('NGAYTAO', now()->month)->whereYear('NGAYTAO', now()->year);

        return view('kho.nhaphang', [
            'phieunhap' => $phieunhap,
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'filter' => $filter,
            'mancc' => $mancc,
            'thang' => $thang,
            'selectedMapnh' => $selectedMapnh,
            'detail' => $detail ? (array) $detail : null,
            'ctpnList' => $ctpnList,
            'nccList' => $this->mang(NhaCungCap::query()->toBase()->orderBy('TENNCC')->get(['MANCC', 'TENNCC'])),
            'kpiPhieu' => ['tong' => (clone $thangNay)->count()],
            'kpiCho' => ['tong' => PhieuNhapHang::query()->whereIn('TRANGTHAI', ['ChoDuyet', 'DaDuyet'])->count()],
            'kpiTonThap' => ['tong' => SanPham::query()->where('SOLUONGTON', '<=', 10)->where('TRANGTHAI', 'DangBan')->count()],
            'kpiGiaTri' => ['tong' => (clone $thangNay)->sum('TONGCONG_PNH')],
            'stMap' => [
                'ChoDuyet' => ['var(--orange)', 'Chờ duyệt'],
                'DaDuyet' => ['var(--blue)', 'Đã duyệt'],
                'DaNhan' => ['var(--purple)', 'Đang kiểm đếm'],
                'HoanThanh' => ['var(--green)', 'Đã nhập đủ'],
                'DaHuy' => ['var(--red)', 'Đã hủy'],
                'DangVanChuyen' => ['var(--cyan)', 'Đang vận chuyển'],
            ],
        ]);
    }

    /**
     * Hoàn tất nhập kho: phiếu sang "HoanThanh", ghi ngày nhận, cộng số lượng vào tồn kho từng sản phẩm.
     * Khoá dòng phiếu trong transaction để không cộng tồn kho hai lần.
     */
    public function hoanTat(PhieuNhapHang $phieuNhapHang): JsonResponse
    {
        $ketQua = DB::transaction(function () use ($phieuNhapHang): ?string {
            $phieu = PhieuNhapHang::query()->lockForUpdate()->find($phieuNhapHang->MAPNH);

            if (! in_array($phieu?->TRANGTHAI, self::CHO_KIEM_DEM, true)) {
                return 'Phiếu nhập không ở trạng thái chờ kiểm đếm.';
            }

            foreach ($phieu->chiTiets()->get(['MASP', 'SOLUONG']) as $chiTiet) {
                SanPham::query()->whereKey($chiTiet->MASP)->increment('SOLUONGTON', $chiTiet->SOLUONG);
            }

            $phieu->update(['TRANGTHAI' => 'HoanThanh', 'NGAYNHAN' => now()]);

            return null;
        });

        return $ketQua === null
            ? response()->json(['success' => true])
            : response()->json(['success' => false, 'error' => $ketQua], 422);
    }
}
