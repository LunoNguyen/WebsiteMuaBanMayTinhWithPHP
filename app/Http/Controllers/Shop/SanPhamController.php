<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\LoaiSanPham;
use App\Models\NhaSanXuat;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    /**
     * Cách sắp xếp danh sách sản phẩm.
     *
     * @var array<string, string>
     */
    public const SAP_XEP = [
        'moi' => 'Mới nhất',
        'gia-tang' => 'Giá thấp đến cao',
        'gia-giam' => 'Giá cao đến thấp',
        'ten' => 'Tên A–Z',
    ];

    /**
     * Danh sách sản phẩm: tìm kiếm, lọc theo loại, hãng, khoảng giá; sắp xếp.
     */
    public function index(Request $request): View
    {
        $tuKhoa = trim((string) $request->query('q', ''));
        $loai = (array) $request->query('loai', []);
        $hang = (array) $request->query('hang', []);
        $giaTu = (int) $request->query('gia_tu', 0);
        $giaDen = (int) $request->query('gia_den', 0);
        $sapXep = array_key_exists((string) $request->query('sap_xep'), self::SAP_XEP) ? (string) $request->query('sap_xep') : 'moi';

        $sanPham = SanPham::query()
            ->dangBan()
            ->with(['moTa', 'nhaSanXuat', 'anhs'])
            ->when($tuKhoa !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('TENSP', 'like', "%{$tuKhoa}%")
                ->orWhereRelation('nhaSanXuat', 'TENNSX', 'like', "%{$tuKhoa}%")
                ->orWhereRelation('moTa', 'CPU', 'like', "%{$tuKhoa}%")
                ->orWhereRelation('moTa', 'VGA', 'like', "%{$tuKhoa}%")))
            ->when($loai !== [], fn ($q) => $q->whereIn('MALOAI', $loai))
            ->when($hang !== [], fn ($q) => $q->whereIn('MANSX', $hang))
            ->when($giaTu > 0, fn ($q) => $q->where('DONGIA_SP', '>=', $giaTu))
            ->when($giaDen > 0, fn ($q) => $q->where('DONGIA_SP', '<=', $giaDen))
            ->when($sapXep === 'moi', fn ($q) => $q->orderByDesc('NGAYTHEM'))
            ->when($sapXep === 'gia-tang', fn ($q) => $q->orderBy('DONGIA_SP'))
            ->when($sapXep === 'gia-giam', fn ($q) => $q->orderByDesc('DONGIA_SP'))
            ->when($sapXep === 'ten', fn ($q) => $q->orderBy('TENSP'))
            ->orderBy('MASP')
            ->paginate(12)
            ->withQueryString();

        return view('shop.san-pham', [
            'sanPham' => $sanPham,
            'tuKhoa' => $tuKhoa,
            'loai' => $loai,
            'hang' => $hang,
            'giaTu' => $giaTu ?: null,
            'giaDen' => $giaDen ?: null,
            'sapXep' => $sapXep,
            'danhMuc' => LoaiSanPham::query()->withCount(['sanPhams' => fn ($q) => $q->dangBan()])->orderBy('MALOAI')->get(),
            'thuongHieu' => NhaSanXuat::query()->withCount(['sanPhams' => fn ($q) => $q->dangBan()])->orderBy('TENNSX')->get(),
        ]);
    }

    /**
     * Chi tiết sản phẩm.
     */
    public function show(SanPham $sanPham): View
    {
        abort_if($sanPham->TRANGTHAI === 'NgungBan', 404);

        $sanPham->load(['moTa', 'nhaSanXuat', 'loaiSanPham', 'anhs' => fn ($q) => $q->orderByDesc('LA_ANH_CHINH')->orderBy('THU_TU')]);

        return view('shop.chi-tiet', [
            'sp' => $sanPham,
            'lienQuan' => SanPham::query()->dangBan()->with(['moTa', 'nhaSanXuat', 'anhs'])
                ->where('MALOAI', $sanPham->MALOAI)->whereKeyNot($sanPham->MASP)
                ->orderByDesc('NGAYTHEM')->limit(5)->get(),
        ]);
    }
}
