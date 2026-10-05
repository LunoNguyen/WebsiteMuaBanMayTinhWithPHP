<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonHangController extends Controller
{
    /**
     * Trạng thái đơn mà kho cần xử lý.
     *
     * @var list<string>
     */
    private const KHO_XU_LY = ['DaXacNhan', 'DangGiao', 'DaGiao'];

    /**
     * Đơn hàng cần xuất kho / đang giao / đã giao.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');

        $query = HoaDon::query()->toBase()
            ->from('HOADON as hd')
            ->leftJoin('KHACHHANG as kh', 'hd.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'hd.MANV', '=', 'nv.MANV')
            ->select('hd.*', 'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV')
            ->selectSub('SELECT COUNT(*) FROM CHITIETHOADON WHERE MAHD = hd.MAHD', 'so_sp')
            ->selectSub('SELECT SUM(SOLUONG) FROM CHITIETHOADON WHERE MAHD = hd.MAHD', 'tong_sl')
            ->whereIn('hd.TRANGTHAI', self::KHO_XU_LY)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('hd.MAHD', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('hd.TEN_NGUOINHAN', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('hd.TRANGTHAI', $trangthai))
            ->orderByRaw("FIELD(hd.TRANGTHAI, 'DaXacNhan', 'DangGiao', 'DaGiao')")
            ->orderByDesc('hd.NGAYLAP')
            ->orderBy('hd.MAHD');

        $perPage = 12;
        $trang = $this->phanTrang($query, $perPage);

        return view('kho.donhang', [
            'donhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'trangthai' => $trangthai,
            'kpiCho' => ['c' => HoaDon::query()->where('TRANGTHAI', 'DaXacNhan')->count()],
            'kpiDangGiao' => ['c' => HoaDon::query()->where('TRANGTHAI', 'DangGiao')->count()],
            'kpiHomNay' => ['c' => HoaDon::query()->where('TRANGTHAI', 'DaGiao')->whereDate('NGAYLAP', today())->count()],
            'stMap' => [
                'DaXacNhan' => ['var(--orange)', 'Chờ xuất kho', 'cwarn'],
                'DangGiao' => ['var(--blue)', 'Đang giao', 'csc'],
                'DaGiao' => ['var(--green)', 'Đã giao', 'cok'],
            ],
        ]);
    }

    /**
     * Xuất kho và bàn giao vận chuyển: đơn đã xác nhận chuyển sang đang giao.
     */
    public function xuatKho(HoaDon $hoaDon): RedirectResponse
    {
        if ($hoaDon->TRANGTHAI !== 'DaXacNhan') {
            return back()->with('thong_bao', "Đơn {$hoaDon->MAHD} không ở trạng thái chờ xuất kho.");
        }

        $hoaDon->chuyenBuocTiepTheo();

        return back()->with('thong_bao', "Đã xuất kho & bàn giao vận chuyển đơn {$hoaDon->MAHD}!");
    }
}
