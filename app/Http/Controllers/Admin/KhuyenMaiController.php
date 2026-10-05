<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoaDonKhuyenMai;
use App\Models\KhuyenMai;
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
