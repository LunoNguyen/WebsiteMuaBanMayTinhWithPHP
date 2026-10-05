<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;

class ThongBaoController extends Controller
{
    /**
     * Thông báo cho thanh trên của trang quản trị (chỉ dữ liệu, không kèm HTML).
     */
    public function index(): JsonResponse
    {
        $thoiGian = now()->format('H:i d/m/Y');
        $thongBao = [];

        $donCho = HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->count();
        if ($donCho > 0) {
            $thongBao[] = ['loai' => 'order', 'so_luong' => "{$donCho} đơn hàng", 'noi_dung' => 'đang chờ xác nhận', 'thoi_gian' => 'Vừa xong'];
        }

        $sapHet = SanPham::query()->where('SOLUONGTON', '<=', 5)->where('TRANGTHAI', 'DangBan')->count();
        if ($sapHet > 0) {
            $thongBao[] = ['loai' => 'stock', 'so_luong' => "{$sapHet} sản phẩm", 'noi_dung' => 'sắp hết hàng (≤5 cái)', 'thoi_gian' => $thoiGian];
        }

        $chuaThanhToan = PhieuNhapHang::query()->where('TRANGTHAI_THANHTOAN', 'ChuaThanhToan');
        $soPhieu = (clone $chuaThanhToan)->count();
        if ($soPhieu > 0) {
            $thongBao[] = ['loai' => 'payment', 'so_luong' => "{$soPhieu} phiếu nhập", 'noi_dung' => 'chưa thanh toán ('.formatVND($chuaThanhToan->sum('TONGCONG_PNH')).')', 'thoi_gian' => $thoiGian];
        }

        $sapHetHan = KhuyenMai::query()->where('TRANGTHAI', 'HoatDong')->whereBetween('NGAYKT', [now(), now()->addDays(3)])->count();
        if ($sapHetHan > 0) {
            $thongBao[] = ['loai' => 'promo', 'so_luong' => "{$sapHetHan} chương trình KM", 'noi_dung' => 'sắp hết hạn trong 3 ngày', 'thoi_gian' => $thoiGian];
        }

        return response()->json($thongBao);
    }
}
