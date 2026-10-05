<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaiAnhSanPhamRequest;
use App\Models\ChiTietHoaDon;
use App\Models\DanhSachAnh;
use App\Models\LoaiSanPham;
use App\Models\NhaSanXuat;
use App\Models\SanPham;
use App\Services\MinioStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $query = SanPham::query()->toBase()
            ->from('SANPHAM as sp')
            ->leftJoin('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
            ->leftJoin('NHASANXUA as nsx', 'sp.MANSX', '=', 'nsx.MANSX')
            ->leftJoin('NHACUNGCAP as ncc', 'sp.MANCC', '=', 'ncc.MANCC')
            ->select('sp.*', 'lsp.TENLOAI', 'nsx.TENNSX', 'ncc.TENNCC')
            ->selectSub('SELECT URL_ANH FROM DANHSACHANH WHERE MASP = sp.MASP AND LA_ANH_CHINH = 1 LIMIT 1', 'ANH_CHINH')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('sp.TENSP', 'like', "%{$search}%")
                ->orWhere('sp.MASP', 'like', "%{$search}%")))
            ->when($maloai !== '', fn ($q) => $q->where('sp.MALOAI', $maloai))
            ->when($mansx !== '', fn ($q) => $q->where('sp.MANSX', $mansx))
            ->when($trangthai !== '', fn ($q) => $q->where('sp.TRANGTHAI', $trangthai))
            ->when($filter === 'low_stock', fn ($q) => $q->where('sp.SOLUONGTON', '<=', 20))
            ->orderByDesc('sp.NGAYTHEM')
            ->orderBy('sp.MASP');

        $trang = $this->phanTrang($query, 10);
        $sanpham = array_map(fn (array $sp): array => $sp + ['ANH_URL' => $this->storage->url($sp['ANH_CHINH'])], $trang['rows']);

        return view('admin.sanpham', [
            'sanpham' => $sanpham,
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'offset' => $trang['offset'],
            'search' => $search,
            'maloai' => $maloai,
            'mansx' => $mansx,
            'trangthai' => $trangthai,
            'loaiList' => $this->mang(LoaiSanPham::query()->toBase()->orderBy('TENLOAI')->get()),
            'nsxList' => $this->mang(NhaSanXuat::query()->toBase()->orderBy('TENNSX')->get()),
        ]);
    }

    /**
     * Ngừng bán / bật bán lại.
     */
    public function doiTrangThai(SanPham $sanPham): RedirectResponse
    {
        $sanPham->update(['TRANGTHAI' => $sanPham->TRANGTHAI === 'DangBan' ? 'NgungBan' : 'DangBan']);

        return back()->with('thong_bao', 'Đã cập nhật trạng thái sản phẩm!');
    }

    /**
     * Xoá sản phẩm chưa từng bán, cùng ảnh, mô tả, lịch sử giá, khuyến mãi và giỏ hàng liên quan.
     */
    public function destroy(SanPham $sanPham): RedirectResponse
    {
        if (ChiTietHoaDon::query()->where('MASP', $sanPham->MASP)->exists()) {
            return back()->with('thong_bao', 'Không thể xóa sản phẩm đã có trong hóa đơn!')->with('loai', 'danger');
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
        $key = $this->storage->upload($request->file('anh'), 'sanpham/'.$sanPham->MASP);

        if ($key === null) {
            return response()->json(['success' => false, 'error' => 'Không lưu được file lên MinIO. Kiểm tra MinIO có đang chạy không.'], 502);
        }

        $laChinh = $request->boolean('chinh') || ! $sanPham->anhs()->where('LA_ANH_CHINH', true)->exists();

        try {
            DB::transaction(function () use ($sanPham, $key, $laChinh): void {
                if ($laChinh) {
                    $sanPham->anhs()->update(['LA_ANH_CHINH' => false]);
                }

                DanhSachAnh::create([
                    'MASP' => $sanPham->MASP,
                    'URL_ANH' => $key,
                    'LA_ANH_CHINH' => $laChinh,
                    'THU_TU' => (int) $sanPham->anhs()->max('THU_TU') + 1,
                ]);
            });
        } catch (Throwable $exception) {
            $this->storage->delete($key);
            report($exception);

            return response()->json(['success' => false, 'error' => 'Lưu thông tin ảnh thất bại.'], 500);
        }

        return response()->json([
            'success' => true,
            'key' => $key,
            'url' => $this->storage->url($key),
            'la_chinh' => $laChinh,
        ]);
    }
}
