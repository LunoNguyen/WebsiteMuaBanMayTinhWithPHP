<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuPhieuNhapRequest;
use App\Models\CtPhieuNhapHang;
use App\Models\LoaiSanPham;
use App\Models\NhaCungCap;
use App\Models\NhaSanXuat;
use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use App\Services\Realtime;
use App\Support\MaTuDong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $tuanNay = PhieuNhapHang::query()->whereBetween('NGAYTAO', [now()->startOfWeek(), now()->endOfWeek()])->count();

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
            'kpiPhieu' => [
                'tong' => (clone $thangNay)->count(),
                'tuan' => $tuanNay,
            ],
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
            Realtime::sanPham($phieu->chiTiets()->pluck('MASP'));

            return null;
        });

        return $ketQua === null
            ? response()->json(['success' => true])
            : response()->json(['success' => false, 'error' => $ketQua], 422);
    }

    /**
     * Form tạo phiếu nhập kho; mở từ cảnh báo tồn thấp thì điền sẵn sản phẩm.
     */
    public function create(Request $request): View
    {
        $sanPham = $request->query('masp') ? SanPham::query()->find($request->query('masp')) : null;
        $nccList = NhaCungCap::query()->orderBy('TENNCC')->get();
        $sanPhamList = SanPham::query()->with(['loaiSanPham', 'nhaCungCap'])->orderBy('TENSP')->get(['MASP', 'TENSP', 'SOLUONGTON', 'DONGIA_SP', 'MANCC', 'MALOAI']);

        return view('kho.nhaphang-form', [
            'pn' => new PhieuNhapHang([
                'MANCC' => $sanPham?->MANCC,
                'NGAY_DATMUA' => now(),
                'THUE_VAT' => 10,
                'CHIETKHAU' => 0,
                'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan',
            ]),
            'dong' => old('dong', $sanPham ? [['MASP' => $sanPham->MASP, 'SOLUONG' => 10, 'DONGIA_NHAP' => null, 'GHI_CHU' => null]] : []),
            'nccList' => $nccList,
            'sanPhamList' => $sanPhamList,
            'spData' => $sanPhamList->map(fn ($sp) => [
                'MASP' => $sp->MASP,
                'TENSP' => $sp->TENSP,
                'DONGIA_SP' => (float) $sp->DONGIA_SP,
                'SOLUONGTON' => (int) $sp->SOLUONGTON,
            ])->values()->all(),
            'nccData' => $nccList->map(fn ($n) => [
                'MANCC' => $n->MANCC,
                'TENNCC' => $n->TENNCC,
            ])->values()->all(),
            'loaiList' => LoaiSanPham::query()->orderBy('TENLOAI')->get(['MALOAI', 'TENLOAI']),
            'nsxList' => NhaSanXuat::query()->orderBy('TENNSX')->get(['MANSX', 'TENNSX']),
        ]);
    }

    /**
     * Lưu phiếu nhập mới ở trạng thái chờ duyệt.
     * Tự động tạo NCC mới / SP mới nếu người dùng gõ thủ công.
     */
    public function store(LuuPhieuNhapRequest $request): RedirectResponse
    {
        $phieu = DB::transaction(function () use ($request): PhieuNhapHang {
            // --- Tạo NCC mới nếu cần ---
            $mancc = $request->input('MANCC');
            if ($mancc === '__new__') {
                $mancc = MaTuDong::tiepTheo('NHACUNGCAP', 'MANCC', 'NCC');
                NhaCungCap::create([
                    'MANCC'     => $mancc,
                    'TENNCC'    => trim($request->input('ncc_moi_ten')),
                    'DIACHI_NCC' => trim($request->input('ncc_moi_diachi') ?? ''),
                    'SDT_NCC'   => trim($request->input('ncc_moi_sdt') ?? ''),
                    'EMAIL_NCC' => trim($request->input('ncc_moi_email') ?? ''),
                ]);
            }

            // --- Tạo SP mới cho từng dòng nếu cần ---
            $dongDaXuly = [];
            foreach ($request->validated()['dong'] as $dong) {
                if ($dong['MASP'] === '__new__') {
                    $masp = MaTuDong::tiepTheo('SANPHAM', 'MASP', 'SP');

                    // 1. Xử lý Loại sản phẩm (chọn có sẵn hoặc tự nhập tay)
                    $maloai = null;
                    $tenLoaiTuNhap = trim((string) ($dong['TENLOAI_TU_NHAP'] ?? ''));
                    if (($dong['MALOAI_MOI'] ?? '') === '__new__' || $tenLoaiTuNhap !== '') {
                        if ($tenLoaiTuNhap !== '') {
                            $exLoai = LoaiSanPham::where('TENLOAI', $tenLoaiTuNhap)->value('MALOAI');
                            if ($exLoai) {
                                $maloai = $exLoai;
                            } else {
                                $maloai = MaTuDong::tiepTheo('LOAISANPHAM', 'MALOAI', 'LSP', 3);
                                LoaiSanPham::create(['MALOAI' => $maloai, 'TENLOAI' => $tenLoaiTuNhap]);
                            }
                        }
                    } elseif (! empty($dong['MALOAI_MOI'])) {
                        $maloai = $dong['MALOAI_MOI'];
                    }
                    if (! $maloai) {
                        $maloai = DB::table('LOAISANPHAM')->value('MALOAI') ?? 'LSP001';
                    }

                    // 2. Xử lý Nhà sản xuất (chọn có sẵn hoặc tự nhập tay)
                    $mansx = null;
                    $tenNsxTuNhap = trim((string) ($dong['TENNSX_TU_NHAP'] ?? ''));
                    if (($dong['MANSX_MOI'] ?? '') === '__new__' || $tenNsxTuNhap !== '') {
                        if ($tenNsxTuNhap !== '') {
                            $exNsx = NhaSanXuat::where('TENNSX', $tenNsxTuNhap)->value('MANSX');
                            if ($exNsx) {
                                $mansx = $exNsx;
                            } else {
                                $mansx = MaTuDong::tiepTheo('NHASANXUA', 'MANSX', 'NSX', 3);
                                NhaSanXuat::create(['MANSX' => $mansx, 'TENNSX' => $tenNsxTuNhap, 'QUOCGIA' => 'Việt Nam']);
                            }
                        }
                    } elseif (! empty($dong['MANSX_MOI'])) {
                        $mansx = $dong['MANSX_MOI'];
                    }
                    if (! $mansx) {
                        $mansx = DB::table('NHASANXUA')->value('MANSX') ?? 'NSX001';
                    }

                    $giaNhap = (float) ($dong['DONGIA_NHAP'] ?? 0);
                    $giaBan  = ! empty($dong['DONGIA_BAN_MOI']) && (float) $dong['DONGIA_BAN_MOI'] > 0
                        ? (float) $dong['DONGIA_BAN_MOI']
                        : round($giaNhap * 1.2);

                    SanPham::create([
                        'MASP'        => $masp,
                        'TENSP'       => trim($dong['TENSP_MOI'] ?? '') ?: 'Sản phẩm mới',
                        'MALOAI'      => $maloai,
                        'MANSX'       => $mansx,
                        'MANCC'       => $mancc,
                        'DONVT'       => 'Cái',
                        'SOLUONGTON'  => 0,
                        'DONGIA_SP'   => $giaBan,
                        'TRANGTHAI'   => 'DangBan',
                        'NGAYTHEM'    => now(),
                    ]);
                    $dong['MASP'] = $masp;
                }
                $dongDaXuly[] = $dong;
            }

            $manv = $request->user()->MANV ?? DB::table('NHANVIEN')->value('MANV') ?? 'NV003';

            $phieu = PhieuNhapHang::create([
                'MAPNH'                 => MaTuDong::tiepTheo('PHIEUNHAPHANG', 'MAPNH', 'PNH'),
                'MANV'                  => $manv,
                'MANCC'                 => $mancc,
                'TRANGTHAI'             => 'ChoDuyet',
                'NGAYTAO'               => now(),
                ...collect($request->validated())->except(['dong', 'MANCC', 'ncc_moi_ten', 'ncc_moi_diachi', 'ncc_moi_sdt', 'ncc_moi_email'])->all(),
            ]);

            $this->luuChiTiet($phieu, $dongDaXuly);

            return $phieu;
        });

        return redirect()->route('kho.nhaphang', ['pn' => $phieu->MAPNH])
            ->with('thong_bao', "Đã tạo phiếu nhập {$phieu->MAPNH} thành công.");
    }

    /**
     * Ghi lại các dòng sản phẩm và tổng cộng: (tiền hàng - chiết khấu) + VAT.
     *
     * @param  array<int, array<string, mixed>>  $dongList
     */
    private function luuChiTiet(PhieuNhapHang $phieu, array $dongList): void
    {
        DB::table('CT_PHIEUNHAPHANG')->where('MAPNH', $phieu->MAPNH)->delete();

        $tienHang = 0.0;
        // Gộp các dòng trùng MASP nếu có để tránh lỗi Unique constraint (MAPNH, MASP)
        $gopList = [];
        foreach ($dongList as $dong) {
            $m = $dong['MASP'];
            if (isset($gopList[$m])) {
                $gopList[$m]['SOLUONG'] += (int) $dong['SOLUONG'];
                if (! empty($dong['DONGIA_NHAP'])) {
                    $gopList[$m]['DONGIA_NHAP'] = $dong['DONGIA_NHAP'];
                }
                if (! empty($dong['GHI_CHU'])) {
                    $gopList[$m]['GHI_CHU'] = trim($gopList[$m]['GHI_CHU'] . '; ' . $dong['GHI_CHU'], '; ');
                }
            } else {
                $gopList[$m] = $dong;
            }
        }

        foreach ($gopList as $dong) {
            $thanhTien = (int) $dong['SOLUONG'] * (float) $dong['DONGIA_NHAP'];
            $tienHang += $thanhTien;

            DB::table('CT_PHIEUNHAPHANG')->insert([
                'MAPNH'        => $phieu->MAPNH,
                'MASP'         => $dong['MASP'],
                'SOLUONG'      => (int) $dong['SOLUONG'],
                'DONGIA_NHAP'  => $dong['DONGIA_NHAP'],
                'THANHTIEN'    => $thanhTien,
                'GHI_CHU'      => $dong['GHI_CHU'] ?? null,
            ]);
        }

        $sauChietKhau = $tienHang * (1 - (float) $phieu->CHIETKHAU / 100);
        $phieu->update(['TONGCONG_PNH' => round($sauChietKhau * (1 + (float) $phieu->THUE_VAT / 100))]);
    }
}
