<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuSanPhamRequest;
use App\Http\Requests\Admin\TaiAnhSanPhamRequest;
use App\Models\ChiTietHoaDon;
use App\Models\DanhSachAnh;
use App\Models\LichSuGia;
use App\Models\LoaiSanPham;
use App\Models\MoTa;
use App\Models\NhaCungCap;
use App\Models\NhaSanXuat;
use App\Models\SanPham;
use App\Services\MinioStorage;
use App\Services\Realtime;
use App\Support\MaTuDong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class SanPhamController extends Controller
{
    public function __construct(private MinioStorage $storage) {}

    /**
     * Danh sách sản phẩm (lọc theo tên/mã, loại, nhà sản xuất, trạng thái, tồn thấp).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $maloai = (string) $request->query('maloai', '');
        $mansx = (string) $request->query('mansx', '');
        $trangthai = (string) $request->query('trangthai', '');
        $filter = (string) $request->query('filter', '');

        $coSo = SanPham::query()->toBase()
            ->from('SANPHAM as sp')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('sp.TENSP', 'like', "%{$search}%")
                ->orWhere('sp.MASP', 'like', "%{$search}%")))
            ->when($maloai !== '', fn ($q) => $q->where('sp.MALOAI', $maloai))
            ->when($mansx !== '', fn ($q) => $q->where('sp.MANSX', $mansx))
            ->when($filter === 'low_stock', fn ($q) => $q->where('sp.SOLUONGTON', '<=', 20));

        $query = (clone $coSo)
            ->leftJoin('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
            ->leftJoin('NHASANXUA as nsx', 'sp.MANSX', '=', 'nsx.MANSX')
            ->leftJoin('NHACUNGCAP as ncc', 'sp.MANCC', '=', 'ncc.MANCC')
            ->select('sp.*', 'lsp.TENLOAI', 'nsx.TENNSX', 'ncc.TENNCC')
            ->selectSub('SELECT URL_ANH FROM DANHSACHANH WHERE MASP = sp.MASP AND LA_ANH_CHINH = 1 LIMIT 1', 'ANH_CHINH')
            ->when($trangthai !== '', fn ($q) => $q->where('sp.TRANGTHAI', $trangthai))
            ->orderByDesc('sp.NGAYTHEM')
            ->orderBy('sp.MASP');

        $perPage = 10;
        $trang = $this->phanTrang($query, $perPage);
        $sanpham = array_map(fn (array $sp): array => $sp + ['ANH_URL' => $this->storage->url($sp['ANH_CHINH'])], $trang['rows']);

        return view('admin.sanpham', [
            'sanpham' => $sanpham,
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'filter' => $filter,
            'dem' => $this->hangDemTheoCot($coSo, 'sp.TRANGTHAI', ['DangBan' => 'Đang bán', 'HetHang' => 'Hết hàng', 'NgungBan' => 'Ngừng bán']),
            'maloai' => $maloai,
            'mansx' => $mansx,
            'trangthai' => $trangthai,
            'loaiList' => LoaiSanPham::query()->orderBy('TENLOAI')->pluck('TENLOAI', 'MALOAI')->all(),
            'nsxList' => NhaSanXuat::query()->orderBy('TENNSX')->pluck('TENNSX', 'MANSX')->all(),
        ]);
    }

    /**
     * Form thêm sản phẩm.
     */
    public function create(): View
    {
        return view('admin.sanpham-form', $this->duLieuForm(new SanPham(['TRANGTHAI' => 'DangBan', 'DONVT' => 'Cái', 'SOLUONGTON' => 0])));
    }

    /**
     * Lưu sản phẩm mới cùng cấu hình.
     */
    public function store(LuuSanPhamRequest $request): RedirectResponse
    {
        $sanPham = DB::transaction(function () use ($request): SanPham {
            $sanPham = SanPham::create([
                'MASP' => MaTuDong::tiepTheo('SANPHAM', 'MASP', 'SP'),
                ...$request->safe()->only(['TENSP', 'MALOAI', 'MANSX', 'MANCC', 'DONVT', 'DONGIA_SP', 'SOLUONGTON', 'TRANGTHAI']),
                'NGAYTHEM' => now(),
            ]);
            $this->luuMoTa($sanPham, $request);

            return $sanPham;
        });

        $loiAnh = $this->luuAnhTuForm($sanPham, $request);
        Realtime::sanPham($sanPham->MASP);

        return redirect()->route('admin.sanpham.edit', $sanPham->MASP)
            ->with('thong_bao', "Đã thêm sản phẩm {$sanPham->MASP}.".$this->canhBaoAnh($loiAnh))
            ->with('loai', $loiAnh > 0 ? 'info' : 'success');
    }

    /**
     * Form sửa sản phẩm, kèm lịch sử giá.
     */
    public function edit(SanPham $sanPham): View
    {
        $sanPham->load('moTa');

        return view('admin.sanpham-form', [
            ...$this->duLieuForm($sanPham),
            'lichSuGia' => $sanPham->lichSuGias()->orderByDesc('NGAY_CAPNHAT')->limit(10)->get(),
        ]);
    }

    /**
     * Cập nhật sản phẩm; đổi giá thì ghi vào lịch sử giá.
     */
    public function update(LuuSanPhamRequest $request, SanPham $sanPham): RedirectResponse
    {
        DB::transaction(function () use ($request, $sanPham): void {
            $giaCu = (float) $sanPham->DONGIA_SP;
            $sanPham->update($request->safe()->only(['TENSP', 'MALOAI', 'MANSX', 'MANCC', 'DONVT', 'DONGIA_SP', 'TRANGTHAI']));

            if ($giaCu !== (float) $sanPham->DONGIA_SP) {
                LichSuGia::create([
                    'MASP' => $sanPham->MASP,
                    'DONGIA_MOI' => $sanPham->DONGIA_SP,
                    'DONGIA_CU' => $giaCu,
                    'NGAY_CAPNHAT' => now(),
                    'MANV_CAPNHAT' => $request->user()->MANV,
                    'GHI_CHU' => $request->validated('GHI_CHU_GIA'),
                ]);
            }

            $this->luuMoTa($sanPham, $request);
        });

        $loiAnh = $this->luuAnhTuForm($sanPham, $request);
        Realtime::sanPham($sanPham->MASP);

        return redirect()->route($loiAnh > 0 ? 'admin.sanpham.edit' : 'admin.sanpham', $loiAnh > 0 ? $sanPham->MASP : [])
            ->with('thong_bao', "Đã cập nhật sản phẩm {$sanPham->MASP}.".$this->canhBaoAnh($loiAnh))
            ->with('loai', $loiAnh > 0 ? 'info' : 'success');
    }

    /**
     * Dữ liệu chung cho form thêm / sửa.
     *
     * @return array<string, mixed>
     */
    private function duLieuForm(SanPham $sanPham): array
    {
        return [
            'sp' => $sanPham,
            'loaiList' => LoaiSanPham::query()->orderBy('TENLOAI')->get(),
            'nsxList' => NhaSanXuat::query()->orderBy('TENNSX')->get(),
            'nccList' => NhaCungCap::query()->orderBy('TENNCC')->get(),
            'lichSuGia' => collect(),
            'anhList' => $sanPham->exists
                ? $sanPham->anhs()->orderByDesc('LA_ANH_CHINH')->orderBy('THU_TU')->get()
                    ->each(fn (DanhSachAnh $anh) => $anh->setAttribute('url', $this->storage->url($anh->URL_ANH)))
                : collect(),
        ];
    }

    /**
     * Câu báo thêm khi có ảnh không tải lên được.
     */
    private function canhBaoAnh(int $soLoi): string
    {
        return $soLoi > 0 ? " Có {$soLoi} ảnh chưa lưu được lên MinIO, kiểm tra MinIO rồi tải lại." : '';
    }

    /**
     * Tạo hoặc cập nhật dòng cấu hình (MOTA) của sản phẩm; để trống hết thì không tạo.
     */
    private function luuMoTa(SanPham $sanPham, LuuSanPhamRequest $request): void
    {
        $cauHinh = $request->safe()->only(['CPU', 'RAM', 'ROM', 'MANHINH', 'VGA', 'PIN', 'KHAC']);
        $moTa = $sanPham->moTa()->first();

        if ($moTa) {
            $moTa->update($cauHinh);
        } elseif (array_filter($cauHinh, fn ($giaTri) => $giaTri !== null && $giaTri !== '')) {
            MoTa::create(['MAMT' => MaTuDong::tiepTheo('MOTA', 'MAMT', 'MT'), 'MASP' => $sanPham->MASP, ...$cauHinh]);
        }
    }

    /**
     * Ngừng bán / bật bán lại.
     */
    public function doiTrangThai(SanPham $sanPham): RedirectResponse
    {
        $sanPham->update(['TRANGTHAI' => $sanPham->TRANGTHAI === 'DangBan' ? 'NgungBan' : 'DangBan']);
        Realtime::sanPham($sanPham->MASP);

        return back()->with('thong_bao', 'Đã cập nhật trạng thái sản phẩm!');
    }

    /**
     * Xoá sản phẩm chưa từng bán, cùng ảnh, mô tả, lịch sử giá, khuyến mãi và giỏ hàng liên quan.
     */
    public function destroy(SanPham $sanPham): RedirectResponse
    {
        if (ChiTietHoaDon::query()->where('MASP', $sanPham->MASP)->exists() || DB::table('CT_PHIEUNHAPHANG')->where('MASP', $sanPham->MASP)->exists()) {
            return back()->with('thong_bao', 'Không thể xóa sản phẩm đã có trong hóa đơn hoặc phiếu nhập! Hãy chuyển sang ngừng bán.')->with('loai', 'danger');
        }

        $anhs = $sanPham->anhs()->pluck('URL_ANH')->filter()->all();

        DB::transaction(function () use ($sanPham): void {
            $sanPham->anhs()->delete();
            $sanPham->moTa()->delete();
            $sanPham->lichSuGias()->delete();
            DB::table('CT_KHUYENMAI')->where('MASP', $sanPham->MASP)->delete();
            DB::table('CT_GIOHANG')->where('MASP', $sanPham->MASP)->delete();
            $sanPham->delete();
        });

        // Xoá file trên MinIO sau khi dữ liệu đã xoá xong
        defer(function () use ($anhs): void {
            foreach ($anhs as $key) {
                $this->storage->delete($key);
            }
        });

        return back()->with('thong_bao', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Tải ảnh sản phẩm lên MinIO; ảnh đầu tiên (hoặc khi chọn "chính") thành ảnh đại diện.
     */
    public function taiAnh(TaiAnhSanPhamRequest $request, SanPham $sanPham): JsonResponse
    {
        $anh = $this->themAnh($sanPham, $request->file('anh'), $request->boolean('chinh'));

        if ($anh === null) {
            return response()->json(['success' => false, 'error' => 'Không lưu được ảnh lên MinIO. Kiểm tra MinIO có đang chạy không.'], 502);
        }

        return response()->json([
            'success' => true,
            'key' => $anh->URL_ANH,
            'url' => $this->storage->url($anh->URL_ANH),
            'la_chinh' => $anh->LA_ANH_CHINH,
        ]);
    }

    /**
     * Đặt một ảnh làm ảnh đại diện của sản phẩm.
     */
    public function datAnhChinh(SanPham $sanPham, DanhSachAnh $anh): RedirectResponse
    {
        abort_unless($anh->MASP === $sanPham->MASP, 404);

        DB::transaction(function () use ($sanPham, $anh): void {
            $sanPham->anhs()->update(['LA_ANH_CHINH' => false]);
            $anh->update(['LA_ANH_CHINH' => true]);
        });

        return back()->with('thong_bao', 'Đã đổi ảnh đại diện.');
    }

    /**
     * Xoá một ảnh (cả file trên MinIO); xoá ảnh chính thì ảnh kế tiếp thành ảnh chính.
     */
    public function xoaAnh(SanPham $sanPham, DanhSachAnh $anh): RedirectResponse
    {
        abort_unless($anh->MASP === $sanPham->MASP, 404);

        DB::transaction(function () use ($sanPham, $anh): void {
            $anh->delete();

            if ($anh->LA_ANH_CHINH) {
                $sanPham->anhs()->orderBy('THU_TU')->first()?->update(['LA_ANH_CHINH' => true]);
            }
        });

        defer(fn () => $this->storage->delete($anh->URL_ANH));

        return back()->with('thong_bao', 'Đã xoá ảnh.');
    }

    /**
     * Tải một file lên MinIO rồi ghi vào DANHSACHANH. Sản phẩm chưa có ảnh chính thì ảnh này thành ảnh chính.
     * Trả về null khi không lưu được (file trên MinIO được dọn lại nếu ghi DB lỗi).
     */
    private function themAnh(SanPham $sanPham, UploadedFile $file, bool $laChinh = false): ?DanhSachAnh
    {
        $key = $this->storage->upload($file, 'sanpham/'.$sanPham->MASP);

        if ($key === null) {
            return null;
        }

        $laChinh = $laChinh || ! $sanPham->anhs()->where('LA_ANH_CHINH', true)->exists();

        try {
            return DB::transaction(function () use ($sanPham, $key, $laChinh): DanhSachAnh {
                if ($laChinh) {
                    $sanPham->anhs()->update(['LA_ANH_CHINH' => false]);
                }

                return DanhSachAnh::create([
                    'MASP' => $sanPham->MASP,
                    'URL_ANH' => $key,
                    'LA_ANH_CHINH' => $laChinh,
                    'THU_TU' => (int) $sanPham->anhs()->max('THU_TU') + 1,
                ]);
            });
        } catch (Throwable $exception) {
            $this->storage->delete($key);
            report($exception);

            return null;
        }
    }

    /**
     * Lưu các ảnh chọn trong form thêm / sửa; trả về số ảnh không lưu được.
     */
    private function luuAnhTuForm(SanPham $sanPham, LuuSanPhamRequest $request): int
    {
        $loi = 0;

        foreach ($request->file('anh', []) as $file) {
            if ($this->themAnh($sanPham, $file) === null) {
                $loi++;
            }
        }

        return $loi;
    }
}
