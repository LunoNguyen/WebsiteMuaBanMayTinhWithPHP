<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\LoaiSanPham;
use App\Models\NhaSanXuat;
use App\Models\SanPham;
use App\Services\KhuyenMaiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class TrangChuController extends Controller
{
    /**
     * Trang chủ cửa hàng: danh mục, ưu đãi, bán chạy, mới về, theo loại, thương hiệu.
     */
    public function index(KhuyenMaiService $khuyenMai): View
    {
        $sanPham = fn (): Builder => SanPham::query()->dangBan()->with(['moTa', 'nhaSanXuat', 'anhs']);

        $banChay = $sanPham()
            ->withSum(['chiTietHoaDons as da_ban' => fn ($q) => $q->whereHas('hoaDon', fn ($h) => $h->whereIn('TRANGTHAI', ['DaGiao', 'HoanThanh']))], 'SOLUONG')
            ->orderByDesc('da_ban')
            ->orderBy('MASP')
            ->limit(5)
            ->get();

        return view('shop.trang-chu', [
            'danhMuc' => LoaiSanPham::query()->withCount(['sanPhams' => fn ($q) => $q->dangBan()])->orderBy('MALOAI')->get(),
            'khuyenMai' => $khuyenMai->dangApDung(),
            'banChay' => $banChay,
            'moiVe' => $sanPham()->orderByDesc('NGAYTHEM')->orderBy('MASP')->limit(5)->get(),
            'laptop' => $sanPham()->whereRelation('loaiSanPham', 'TENLOAI', 'Laptop')->orderByDesc('NGAYTHEM')->limit(5)->get(),
            'pc' => $sanPham()->whereHas('loaiSanPham', fn ($q) => $q->where('TENLOAI', 'like', 'PC%')->orWhere('TENLOAI', 'like', '%All-in-One%'))->orderByDesc('NGAYTHEM')->limit(5)->get(),
            'thuongHieu' => NhaSanXuat::query()->whereHas('sanPhams', fn ($q) => $q->dangBan())->orderBy('TENNSX')->get(),
        ]);
    }
}
