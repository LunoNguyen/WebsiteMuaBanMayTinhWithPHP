<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuKhuyenMaiRequest;
use App\Models\HoaDonKhuyenMai;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KhuyenMaiController extends Controller
{
    /**
     * Danh sách khuyến mãi (lọc theo từ khoá, trạng thái, loại).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');
        $loaikm = (string) $request->query('loaikm', '');

        $query = KhuyenMai::query()->toBase()
            ->from('KHUYENMAI as km')
            ->select('km.*')
            ->selectSub('SELECT COUNT(*) FROM CT_KHUYENMAI WHERE MAKM = km.MAKM', 'so_sp_ap_dung')
            ->selectSub('SELECT COUNT(*) FROM HOADON_KHUYENMAI WHERE MAKM = km.MAKM', 'so_hd_da_dung')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('km.TENKM', 'like', "%{$search}%")
                ->orWhere('km.MA_CODE', 'like', "%{$search}%")
                ->orWhere('km.MAKM', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('km.TRANGTHAI', $trangthai))
            ->when($loaikm !== '', fn ($q) => $q->where('km.LOAI_KM', $loaikm))
            ->orderByRaw("km.TRANGTHAI = 'HoatDong' DESC")
            ->orderByDesc('km.NGAYKT')
            ->orderBy('km.MAKM');

        $trang = $this->phanTrang($query, 10);

        return view('admin.khuyenmai', [
            'khuyenmai' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'search' => $search,
            'trangthai' => $trangthai,
            'loaikm' => $loaikm,
            'statKMMap' => KhuyenMai::query()->toBase()->selectRaw('TRANGTHAI, COUNT(*) AS cnt')
                ->groupBy('TRANGTHAI')->pluck('cnt', 'TRANGTHAI')->all(),
        ]);
    }

    /**
     * Form tạo khuyến mãi.
     */
    public function create(): View
    {
        return view('admin.khuyenmai-form', $this->duLieuForm(new KhuyenMai([
            'LOAI_KM' => 'PhanTram',
            'TRANGTHAI' => 'HoatDong',
            'SOTIENTOITHIEU_NHANKM' => 0,
            'NGAYBD' => today(),
            'NGAYKT' => today()->addMonth()->endOfDay(),
        ])));
    }

    /**
     * Lưu khuyến mãi mới cùng danh sách sản phẩm áp dụng.
     */
    public function store(LuuKhuyenMaiRequest $request): RedirectResponse
    {
        $khuyenMai = DB::transaction(function () use ($request): KhuyenMai {
            $khuyenMai = KhuyenMai::create([
                'MAKM' => MaTuDong::tiepTheo('KHUYENMAI', 'MAKM', 'KM'),
                'DA_SUDUNG' => 0,
                ...$this->thuocTinh($request),
            ]);
            $khuyenMai->sanPhams()->sync($request->validated('san_pham') ?? []);

            return $khuyenMai;
        });

        return redirect()->route('admin.khuyenmai')->with('thong_bao', "Đã tạo khuyến mãi {$khuyenMai->MA_CODE}.");
    }

    /**
     * Form sửa khuyến mãi.
     */
    public function edit(KhuyenMai $khuyenMai): View
    {
        return view('admin.khuyenmai-form', $this->duLieuForm($khuyenMai->load('sanPhams:MASP')));
    }

    /**
     * Cập nhật khuyến mãi; lượt đã dùng giữ nguyên.
     */
    public function update(LuuKhuyenMaiRequest $request, KhuyenMai $khuyenMai): RedirectResponse
    {
        DB::transaction(function () use ($request, $khuyenMai): void {
            $khuyenMai->update($this->thuocTinh($request));
            $khuyenMai->sanPhams()->sync($request->validated('san_pham') ?? []);
        });

        return redirect()->route('admin.khuyenmai')->with('thong_bao', "Đã cập nhật khuyến mãi {$khuyenMai->MA_CODE}.");
    }

    /**
     * Cột của bảng KHUYENMAI từ form. Giảm số tiền cố định thì mức giảm tối đa chính là số tiền đó.
     *
     * @return array<string, mixed>
     */
    private function thuocTinh(LuuKhuyenMaiRequest $request): array
    {
        $duLieu = $request->safe()->except(['san_pham']);

        if ($duLieu['LOAI_KM'] === 'SoTienCoDinh') {
            $duLieu['SOTIENTOIDA_KM'] = $duLieu['GIATRI_KM'];
        }

        return $duLieu;
    }

    /**
     * @return array<string, mixed>
     */
    private function duLieuForm(KhuyenMai $khuyenMai): array
    {
        return [
            'km' => $khuyenMai,
            'sanPhamList' => SanPham::query()->orderBy('TENSP')->get(['MASP', 'TENSP', 'DONGIA_SP']),
            'daChon' => old('san_pham', $khuyenMai->exists ? $khuyenMai->sanPhams->pluck('MASP')->all() : []),
        ];
    }

    /**
     * Tạm dừng / kích hoạt khuyến mãi.
     */
    public function doiTrangThai(KhuyenMai $khuyenMai): RedirectResponse
    {
        $khuyenMai->update(['TRANGTHAI' => $khuyenMai->TRANGTHAI === 'HoatDong' ? 'TamDung' : 'HoatDong']);

        return back()->with('thong_bao', 'Đã cập nhật trạng thái khuyến mãi!');
    }

    /**
     * Xoá khuyến mãi chưa từng áp vào hoá đơn.
     */
    public function destroy(KhuyenMai $khuyenMai): RedirectResponse
    {
        if (HoaDonKhuyenMai::query()->where('MAKM', $khuyenMai->MAKM)->exists()) {
            return back()->with('thong_bao', 'Không thể xóa khuyến mãi đã áp dụng vào hóa đơn!')->with('loai', 'danger');
        }

        DB::transaction(function () use ($khuyenMai): void {
            $khuyenMai->sanPhams()->detach();
            $khuyenMai->delete();
        });

        return back()->with('thong_bao', 'Đã xóa khuyến mãi thành công!');
    }
}
