<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuKhachHangRequest;
use App\Models\KhachHang;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KhachHangController extends Controller
{
    /**
     * Danh sách khách hàng, xếp theo tổng chi tiêu.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = KhachHang::query()->toBase()
            ->from('KHACHHANG as kh')
            ->select('kh.*')
            ->selectSub('SELECT COUNT(*) FROM HOADON WHERE MAKH = kh.MAKH', 'so_hd')
            ->selectSub("SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH = kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')", 'tong_chi_tieu')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('kh.MAKH', 'like', "%{$search}%")
                ->orWhere('kh.SDT_KH', 'like', "%{$search}%")
                ->orWhere('kh.EMAIL_KH', 'like', "%{$search}%")))
            ->orderByDesc('tong_chi_tieu')
            ->orderBy('kh.MAKH');

        $trang = $this->phanTrang($query, 10);

        return view('admin.khachhang', [
            'khachhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'offset' => $trang['offset'],
            'search' => $search,
            'avatarColors' => ['#3a56e4', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'],
        ]);
    }

    /**
     * Hồ sơ khách hàng: thông tin, tài khoản và lịch sử đơn.
     */
    public function show(KhachHang $khachHang): View
    {
        return view('admin.khachhang-chitiet', [
            'kh' => $khachHang->load('taiKhoan'),
            'tomTat' => $khachHang->tomTatMuaHang(),
            'donHang' => $khachHang->hoaDons()->with('thanhToan')->withCount('chiTiets')->latest('NGAYLAP')->limit(20)->get(),
        ]);
    }

    /**
     * Form thêm khách hàng (khách mua tại quầy, chưa cần tài khoản).
     */
    public function create(): View
    {
        return view('admin.khachhang-form', ['kh' => new KhachHang]);
    }

    /**
     * Lưu khách hàng mới.
     */
    public function store(LuuKhachHangRequest $request): RedirectResponse
    {
        $khachHang = DB::transaction(fn (): KhachHang => KhachHang::create([
            'MAKH' => MaTuDong::tiepTheo('KHACHHANG', 'MAKH', 'KH'),
            ...$request->validated(),
        ]));

        return redirect()->route('admin.khachhang.show', $khachHang->MAKH)->with('thong_bao', "Đã thêm khách hàng {$khachHang->MAKH}.");
    }

    /**
     * Form sửa khách hàng.
     */
    public function edit(KhachHang $khachHang): View
    {
        return view('admin.khachhang-form', ['kh' => $khachHang]);
    }

    /**
     * Cập nhật khách hàng.
     */
    public function update(LuuKhachHangRequest $request, KhachHang $khachHang): RedirectResponse
    {
        $khachHang->update($request->validated());

        return redirect()->route('admin.khachhang.show', $khachHang->MAKH)->with('thong_bao', "Đã cập nhật khách hàng {$khachHang->MAKH}.");
    }

    /**
     * Xoá khách hàng chưa có đơn và chưa có tài khoản.
     */
    public function destroy(KhachHang $khachHang): RedirectResponse
    {
        if ($khachHang->hoaDons()->exists() || $khachHang->taiKhoan()->exists()) {
            return back()->with('thong_bao', "Khách hàng {$khachHang->MAKH} đã có đơn hàng hoặc tài khoản, không xoá được.")->with('loai', 'danger');
        }

        DB::transaction(function () use ($khachHang): void {
            DB::table('PHIEN_CHATBOT')->where('MAKH', $khachHang->MAKH)->update(['MAKH' => null]);
            $khachHang->delete();
        });

        return redirect()->route('admin.khachhang')->with('thong_bao', "Đã xoá khách hàng {$khachHang->MAKH}.");
    }
}
