<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChiTietHoaDon;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\ThanhToan;
use Illuminate\View\View;

class BaoCaoController extends Controller
{
    /**
     * Trạng thái đơn được tính vào doanh thu.
     *
     * @var list<string>
     */
    private const DA_BAN = ['DaGiao', 'HoanThanh'];

    /**
     * Báo cáo doanh thu, sản phẩm, khách hàng, nhân viên.
     */
    public function index(): View
    {
        $dtThang = $this->mang(HoaDon::query()->toBase()
            ->selectRaw("DATE_FORMAT(NGAYLAP,'%m/%Y') AS thang_label, YEAR(NGAYLAP) AS nam, MONTH(NGAYLAP) AS thang,
                SUM(TONGTIEN_HD) AS doanhthu, COUNT(*) AS so_don, AVG(TONGTIEN_HD) AS trung_binh, SUM(TONGTIEN_GIAM) AS tong_giam")
            ->whereIn('TRANGTHAI', self::DA_BAN)
            ->where('NGAYLAP', '>=', now()->subMonths(12)->startOfDay())
            ->groupByRaw("YEAR(NGAYLAP), MONTH(NGAYLAP), DATE_FORMAT(NGAYLAP,'%m/%Y')")
            ->orderByRaw('YEAR(NGAYLAP), MONTH(NGAYLAP)')
            ->get());

        $topSP = $this->mang(ChiTietHoaDon::query()->toBase()
            ->from('CHITIETHOADON as ct')
            ->join('SANPHAM as sp', 'ct.MASP', '=', 'sp.MASP')
            ->join('HOADON as hd', 'ct.MAHD', '=', 'hd.MAHD')
            ->join('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
            ->join('NHASANXUA as nsx', 'sp.MANSX', '=', 'nsx.MANSX')
            ->whereIn('hd.TRANGTHAI', self::DA_BAN)
            ->groupBy('sp.MASP', 'sp.TENSP', 'lsp.TENLOAI', 'nsx.TENNSX')
            ->selectRaw('sp.MASP, sp.TENSP, lsp.TENLOAI, nsx.TENNSX, SUM(ct.SOLUONG) AS tong_ban, SUM(ct.THANHTIEN) AS tong_dt')
            ->orderByDesc('tong_ban')
            ->limit(5)
            ->get());

        $dtPT = $this->mang(ThanhToan::query()->toBase()
            ->where('TRANGTHAI', 'DaThanhToan')
            ->groupBy('PHUONG_THUC')
            ->selectRaw('PHUONG_THUC, COUNT(*) AS so_gd, SUM(SOTIEN) AS tong')
            ->get());

        $topNV = $this->mang(NhanVien::query()->toBase()
            ->from('NHANVIEN as nv')
            ->leftJoin('HOADON as hd', fn ($join) => $join->on('nv.MANV', '=', 'hd.MANV')->whereIn('hd.TRANGTHAI', self::DA_BAN))
            ->leftJoin('CHUCVU as cv', 'nv.MACV', '=', 'cv.MACV')
            ->groupBy('nv.MANV', 'nv.TENNV', 'cv.TENCV')
            ->selectRaw('nv.MANV, nv.TENNV, cv.TENCV, COUNT(hd.MAHD) AS so_don, SUM(hd.TONGTIEN_HD) AS tong_dt')
            ->havingRaw('so_don > 0')
            ->orderByDesc('tong_dt')
            ->limit(5)
            ->get());

        $kpi = HoaDon::query()->toBase()->whereIn('TRANGTHAI', self::DA_BAN)
            ->selectRaw('SUM(TONGTIEN_HD) AS tong_dt, COUNT(*) AS so_don, AVG(TONGTIEN_HD) AS trung_binh, SUM(TONGTIEN_GIAM) AS tong_giam')
            ->first();

        return view('admin.baocao', [
            'dtThang' => $dtThang,
            'topSP' => $topSP,
            'dtPT' => $dtPT,
            'topKH' => $this->mang(KhachHang::query()->toBase()
                ->from('KHACHHANG as kh')
                ->join('HOADON as hd', 'kh.MAKH', '=', 'hd.MAKH')
                ->whereIn('hd.TRANGTHAI', self::DA_BAN)
                ->groupBy('kh.MAKH', 'kh.TENKH', 'kh.EMAIL_KH')
                ->selectRaw('kh.MAKH, kh.TENKH, kh.EMAIL_KH, COUNT(hd.MAHD) AS so_don, SUM(hd.TONGTIEN_HD) AS tong_chi_tieu')
                ->orderByDesc('tong_chi_tieu')
                ->limit(5)
                ->get()),
            'topNV' => $topNV,
            'kpiTotal' => (array) $kpi,
            'thisMonth' => $dtThang === [] ? [] : end($dtThang),
            'tongDT' => array_sum(array_column($topNV, 'tong_dt')) ?: 1,
            'maxDT' => max(array_map('floatval', array_column($topSP, 'tong_dt')) ?: [1]),
            'ptColors' => ['QR' => '#3a56e4', 'COD' => '#b45309', 'ChuyenKhoan' => '#15803d', 'TienMat' => '#6d28d9'],
            'chartLabels' => array_column($dtThang, 'thang_label'),
            'chartDT' => array_map('floatval', array_column($dtThang, 'doanhthu')),
            'chartDon' => array_map('intval', array_column($dtThang, 'so_don')),
            'ptLabels' => array_column($dtPT, 'PHUONG_THUC'),
            'ptData' => array_map(fn (array $r): float => (float) $r['tong'], $dtPT),
        ]);
    }
}
