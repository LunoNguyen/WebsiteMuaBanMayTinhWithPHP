<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuPhieuNhapRequest;
use App\Models\NhaCungCap;
use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NhapHangController extends Controller
{
    /**
     * Phiếu còn sửa được (chưa nhận hàng, chưa cộng tồn kho).
     *
     * @var list<string>
     */
    private const SUA_DUOC = ['ChoDuyet', 'DaDuyet'];

    /**
     * Phiếu còn huỷ được (tồn kho chỉ cộng khi kho bấm hoàn tất).
     *
     * @var list<string>
     */
    private const HUY_DUOC = ['ChoDuyet', 'DaDuyet', 'DaNhan'];

    /**
     * Nhãn và màu trạng thái phiếu.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const TRANG_THAI = [
        'ChoDuyet' => ['var(--orange)', 'Chờ duyệt'],
        'DaDuyet' => ['var(--blue)', 'Đã duyệt'],
        'DaNhan' => ['var(--purple)', 'Đã nhận'],
        'HoanThanh' => ['var(--green)', 'Hoàn thành'],
        'DaHuy' => ['var(--red)', 'Đã hủy'],
    ];

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
            'statusMap' => self::TRANG_THAI,
            'ttMap' => [
                'ChuaThanhToan' => ['var(--orange)', 'Chưa TT'],
                'DaThanhToan' => ['var(--green)', 'Đã TT'],
                'HoanTien' => ['var(--text-secondary)', 'Hoàn tiền'],
            ],
        ]);
    }

    /**
     * Chi tiết phiếu nhập.
     */
    public function show(PhieuNhapHang $phieuNhapHang): View
    {
        return view('admin.nhaphang-chitiet', [
            'pn' => $phieuNhapHang->load(['nhaCungCap', 'nhanVien', 'chiTiets.sanPham']),
            'trangThai' => self::TRANG_THAI,
            'suaDuoc' => in_array($phieuNhapHang->TRANGTHAI, self::SUA_DUOC, true),
            'huyDuoc' => in_array($phieuNhapHang->TRANGTHAI, self::HUY_DUOC, true),
        ]);
    }

    /**
     * Form tạo phiếu nhập; mở từ cảnh báo tồn thấp thì điền sẵn sản phẩm.
     */
    public function create(Request $request): View
    {
        $sanPham = $request->query('masp') ? SanPham::query()->find($request->query('masp')) : null;

        return view('admin.nhaphang-form', $this->duLieuForm(
            new PhieuNhapHang(['MANCC' => $sanPham?->MANCC, 'NGAY_DATMUA' => now(), 'THUE_VAT' => 10, 'CHIETKHAU' => 0, 'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan']),
            $sanPham ? [['MASP' => $sanPham->MASP, 'SOLUONG' => 10, 'DONGIA_NHAP' => null, 'GHI_CHU' => null]] : [],
        ));
    }

    /**
     * Lưu phiếu nhập mới ở trạng thái chờ duyệt.
     */
    public function store(LuuPhieuNhapRequest $request): RedirectResponse
    {
        $phieu = DB::transaction(function () use ($request): PhieuNhapHang {
            $phieu = PhieuNhapHang::create([
                'MAPNH' => MaTuDong::tiepTheo('PHIEUNHAPHANG', 'MAPNH', 'PNH'),
                'MANV' => $request->user()->MANV,
                'TRANGTHAI' => 'ChoDuyet',
                'NGAYTAO' => now(),
                ...$request->safe()->except(['dong']),
            ]);
            $this->luuChiTiet($phieu, $request);

            return $phieu;
        });

        return redirect()->route('admin.nhaphang.show', $phieu->MAPNH)->with('thong_bao', "Đã tạo phiếu nhập {$phieu->MAPNH}.");
    }

    /**
     * Form sửa phiếu (chỉ khi chưa nhận hàng).
     */
    public function edit(PhieuNhapHang $phieuNhapHang): View|RedirectResponse
    {
        if (! in_array($phieuNhapHang->TRANGTHAI, self::SUA_DUOC, true)) {
            return redirect()->route('admin.nhaphang.show', $phieuNhapHang->MAPNH)
                ->with('thong_bao', 'Phiếu đã nhận hàng hoặc đã đóng, không sửa được.')->with('loai', 'danger');
        }

        $dong = $phieuNhapHang->chiTiets()->get(['MASP', 'SOLUONG', 'DONGIA_NHAP', 'GHI_CHU'])
            ->map(fn ($ct): array => ['MASP' => $ct->MASP, 'SOLUONG' => $ct->SOLUONG, 'DONGIA_NHAP' => (float) $ct->DONGIA_NHAP, 'GHI_CHU' => $ct->GHI_CHU])
            ->all();

        return view('admin.nhaphang-form', $this->duLieuForm($phieuNhapHang, $dong));
    }

    /**
     * Cập nhật phiếu và thay toàn bộ dòng sản phẩm.
     */
    public function update(LuuPhieuNhapRequest $request, PhieuNhapHang $phieuNhapHang): RedirectResponse
    {
        $daSua = DB::transaction(function () use ($request, $phieuNhapHang): bool {
            $phieu = PhieuNhapHang::query()->lockForUpdate()->find($phieuNhapHang->MAPNH);

            if (! in_array($phieu->TRANGTHAI, self::SUA_DUOC, true)) {
                return false;
            }

            $phieu->update($request->safe()->except(['dong']));
            $this->luuChiTiet($phieu, $request);

            return true;
        });

        return redirect()->route('admin.nhaphang.show', $phieuNhapHang->MAPNH)
            ->with('thong_bao', $daSua ? "Đã cập nhật phiếu {$phieuNhapHang->MAPNH}." : 'Phiếu đã nhận hàng hoặc đã đóng, không sửa được.')
            ->with('loai', $daSua ? 'success' : 'danger');
    }

    /**
     * Duyệt phiếu chờ duyệt để kho nhận hàng.
     */
    public function duyet(PhieuNhapHang $phieuNhapHang): RedirectResponse
    {
        $daDuyet = PhieuNhapHang::query()->whereKey($phieuNhapHang->MAPNH)->where('TRANGTHAI', 'ChoDuyet')->update(['TRANGTHAI' => 'DaDuyet']) > 0;

        return back()->with('thong_bao', $daDuyet ? "Đã duyệt phiếu {$phieuNhapHang->MAPNH}." : 'Phiếu không ở trạng thái chờ duyệt.')
            ->with('loai', $daDuyet ? 'success' : 'danger');
    }

    /**
     * Huỷ phiếu chưa nhập kho.
     */
    public function huy(PhieuNhapHang $phieuNhapHang): RedirectResponse
    {
        $daHuy = PhieuNhapHang::query()->whereKey($phieuNhapHang->MAPNH)->whereIn('TRANGTHAI', self::HUY_DUOC)->update(['TRANGTHAI' => 'DaHuy']) > 0;

        return back()->with('thong_bao', $daHuy ? "Đã huỷ phiếu {$phieuNhapHang->MAPNH}." : 'Phiếu đã nhập kho, không huỷ được.')
            ->with('loai', $daHuy ? 'info' : 'danger');
    }

    /**
     * Xoá phiếu chưa duyệt hoặc đã huỷ (chưa từng cộng tồn kho).
     */
    public function destroy(PhieuNhapHang $phieuNhapHang): RedirectResponse
    {
        if (! in_array($phieuNhapHang->TRANGTHAI, ['ChoDuyet', 'DaHuy'], true)) {
            return back()->with('thong_bao', 'Chỉ xoá được phiếu chờ duyệt hoặc đã huỷ.')->with('loai', 'danger');
        }

        DB::transaction(function () use ($phieuNhapHang): void {
            DB::table('CT_PHIEUNHAPHANG')->where('MAPNH', $phieuNhapHang->MAPNH)->delete();
            $phieuNhapHang->delete();
        });

        return redirect()->route('admin.nhaphang')->with('thong_bao', "Đã xoá phiếu {$phieuNhapHang->MAPNH}.");
    }

    /**
     * Ghi lại các dòng sản phẩm và tổng cộng: (tiền hàng - chiết khấu) + VAT.
     */
    private function luuChiTiet(PhieuNhapHang $phieu, LuuPhieuNhapRequest $request): void
    {
        DB::table('CT_PHIEUNHAPHANG')->where('MAPNH', $phieu->MAPNH)->delete();

        $tienHang = 0.0;
        foreach ($request->validated('dong') as $dong) {
            $thanhTien = (int) $dong['SOLUONG'] * (float) $dong['DONGIA_NHAP'];
            $tienHang += $thanhTien;

            DB::table('CT_PHIEUNHAPHANG')->insert([
                'MAPNH' => $phieu->MAPNH,
                'MASP' => $dong['MASP'],
                'SOLUONG' => (int) $dong['SOLUONG'],
                'DONGIA_NHAP' => $dong['DONGIA_NHAP'],
                'THANHTIEN' => $thanhTien,
                'GHI_CHU' => $dong['GHI_CHU'] ?? null,
            ]);
        }

        $sauChietKhau = $tienHang * (1 - (float) $phieu->CHIETKHAU / 100);
        $phieu->update(['TONGCONG_PNH' => round($sauChietKhau * (1 + (float) $phieu->THUE_VAT / 100))]);
    }

    /**
     * @param  list<array<string, mixed>>  $dong
     * @return array<string, mixed>
     */
    private function duLieuForm(PhieuNhapHang $phieu, array $dong): array
    {
        return [
            'pn' => $phieu,
            'dong' => old('dong', $dong ?: [['MASP' => null, 'SOLUONG' => 1, 'DONGIA_NHAP' => null, 'GHI_CHU' => null]]),
            'nccList' => NhaCungCap::query()->orderBy('TENNCC')->get(),
            'sanPhamList' => SanPham::query()->orderBy('TENSP')->get(['MASP', 'TENSP', 'SOLUONGTON']),
        ];
    }
}
