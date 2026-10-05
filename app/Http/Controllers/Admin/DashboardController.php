<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChiTietHoaDon;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\KhuyenMai;
use App\Models\LichSuGia;
use App\Models\NhanVien;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Trạng thái đơn được tính vào doanh thu.
     *
     * @var list<string>
     */
    private const DA_BAN = ['DaGiao', 'HoanThanh'];

    /**
     * Tổng quan hệ thống.
     */
    public function index(): View
    {
        $doanhThu = HoaDon::query()->whereIn('TRANGTHAI', self::DA_BAN);

        $dtThang = $this->mang(HoaDon::query()->toBase()
            ->selectRaw("DATE_FORMAT(NGAYLAP,'%m/%Y') AS thang_label, YEAR(NGAYLAP) AS nam, MONTH(NGAYLAP) AS thang, SUM(TONGTIEN_HD) AS doanhthu, COUNT(*) AS so_don")
            ->whereIn('TRANGTHAI', self::DA_BAN)
            ->where('NGAYLAP', '>=', now()->subMonths(6)->startOfDay())
            ->groupByRaw("YEAR(NGAYLAP), MONTH(NGAYLAP), DATE_FORMAT(NGAYLAP,'%m/%Y')")
            ->orderByRaw('YEAR(NGAYLAP), MONTH(NGAYLAP)')
            ->get());

        $coSua = $this->mang(ChiTietHoaDon::query()->toBase()
            ->from('CHITIETHOADON as cthd')
            ->join('SANPHAM as sp', 'cthd.MASP', '=', 'sp.MASP')
            ->join('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
            ->join('HOADON as hd', 'cthd.MAHD', '=', 'hd.MAHD')
            ->whereIn('hd.TRANGTHAI', self::DA_BAN)
            ->groupBy('lsp.MALOAI', 'lsp.TENLOAI')
            ->selectRaw('lsp.TENLOAI, SUM(cthd.THANHTIEN) AS tong_tien, COUNT(*) AS so_san_pham_ban')
            ->orderByDesc('tong_tien')
            ->get());

        $tongDoanhThu = array_sum(array_column($coSua, 'tong_tien')) ?: 1;
        $donutColors = ['#3a56e4', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'];

        return view('admin.dashboard', [
            'kpiDT' => ['tong_dt' => (clone $doanhThu)->sum('TONGTIEN_HD'), 'so_don' => (clone $doanhThu)->count()],
            'kpiSP' => ['cnt' => SanPham::query()->where('TRANGTHAI', 'DangBan')->count()],
            'kpiKH' => ['cnt' => KhachHang::query()->count()],
            'kpiNV' => ['cnt' => NhanVien::query()->where('TRANGTHAI', 1)->count()],
            'kpiTK' => ['cnt' => TaiKhoan::query()->where('TRANGTHAI', 'HoatDong')->count()],
            'dtThang' => $dtThang,
            'coSua' => $coSua,
            'tonKhoThap' => $this->mang(SanPham::query()->toBase()
                ->from('SANPHAM as sp')
                ->leftJoin('LOAISANPHAM as lsp', 'sp.MALOAI', '=', 'lsp.MALOAI')
                ->leftJoin('NHASANXUA as nsx', 'sp.MANSX', '=', 'nsx.MANSX')
                ->where('sp.SOLUONGTON', '<=', 20)
                ->where('sp.TRANGTHAI', 'DangBan')
                ->orderBy('sp.SOLUONGTON')
                ->limit(5)
                ->get(['sp.MASP', 'sp.TENSP', 'sp.SOLUONGTON', 'sp.DONGIA_SP', 'lsp.TENLOAI', 'nsx.TENNSX'])),
            'kmHoatDong' => $this->mang(KhuyenMai::query()->toBase()
                ->whereIn('TRANGTHAI', ['HoatDong', 'TamDung'])
                ->orderByRaw("TRANGTHAI = 'HoatDong' DESC")
                ->orderByDesc('NGAYKT')
                ->limit(4)
                ->get(['MAKM', 'TENKM', 'MA_CODE', 'LOAI_KM', 'GIATRI_KM', 'SOTIENTOIDA_KM', 'NGAYKT', 'TRANGTHAI', 'DA_SUDUNG', 'SOLUONG_MA'])),
            'activityHD' => $this->mang(HoaDon::query()->toBase()
                ->from('HOADON as hd')
                ->leftJoin('KHACHHANG as kh', 'hd.MAKH', '=', 'kh.MAKH')
                ->leftJoin('NHANVIEN as nv', 'hd.MANV', '=', 'nv.MANV')
                ->orderByDesc('hd.NGAYLAP')
                ->limit(6)
                ->get(['hd.MAHD', 'hd.NGAYLAP', 'hd.TRANGTHAI', 'hd.TONGTIEN_HD', 'kh.TENKH', 'nv.TENNV'])),
            'activityGia' => $this->mang(LichSuGia::query()->toBase()
                ->from('LICHSUGIA as lsg')
                ->join('SANPHAM as sp', 'lsg.MASP', '=', 'sp.MASP')
                ->leftJoin('NHANVIEN as nv', 'lsg.MANV_CAPNHAT', '=', 'nv.MANV')
                ->orderByDesc('lsg.NGAY_CAPNHAT')
                ->limit(4)
                ->get(['lsg.MASP', 'lsg.DONGIA_MOI', 'lsg.DONGIA_CU', 'lsg.NGAY_CAPNHAT', 'lsg.GHI_CHU', 'sp.TENSP', 'nv.TENNV'])),
            'donCho' => ['cnt' => HoaDon::query()->whereIn('TRANGTHAI', ['ChoXacNhan', 'DaXacNhan'])->count()],
            'donGiao' => ['cnt' => HoaDon::query()->where('TRANGTHAI', 'DangGiao')->count()],
            'tongDoanhThu' => $tongDoanhThu,
            'donutColors' => $donutColors,
            'chartLabels' => array_column($dtThang, 'thang_label'),
            'chartDT' => array_map('floatval', array_column($dtThang, 'doanhthu')),
            'chartDon' => array_map('intval', array_column($dtThang, 'so_don')),
            'donutLabels' => array_column($coSua, 'TENLOAI'),
            'donutData' => array_map(fn (array $c): float => round($c['tong_tien'] / $tongDoanhThu * 100, 1), $coSua),
            'donutColorsUsed' => array_slice($donutColors, 0, count($coSua)),
        ]);
    }
}
