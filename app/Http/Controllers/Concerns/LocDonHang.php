<?php

namespace App\Http\Controllers\Concerns;

use App\Models\HoaDon;
use Illuminate\Http\Request;

/**
 * Danh sách đơn hàng dùng chung cho Admin, Bán hàng, Kho: cùng bộ lọc (tìm, trạng thái, hình thức nhận,
 * thanh toán, khoảng ngày), cùng cột dữ liệu, cùng số đếm theo trạng thái. Dùng trong controller kế thừa Controller.
 */
trait LocDonHang
{
    /**
     * @param  list<string>  $chiTrangThai  giới hạn trạng thái (Kho chỉ xem đơn cần xuất / đang giao / đã giao); rỗng là mọi trạng thái
     * @return array<string, mixed> biến cho partials/quan-tri/ds-don
     */
    protected function duLieuDanhSachDon(Request $request, array $chiTrangThai = [], int $perPage = 12): array
    {
        $search = trim((string) $request->query('q', ''));
        $trangthai = (string) $request->query('trangthai', '');
        $ptgh = (string) $request->query('ptgh', '');
        $tttt = (string) $request->query('tttt', '');
        $khoang = (string) $request->query('khoang', '');
        [$from, $to] = $this->khoangNgay($khoang);

        $query = HoaDon::query()->toBase()
            ->from('HOADON as hd')
            ->leftJoin('KHACHHANG as kh', 'hd.MAKH', '=', 'kh.MAKH')
            ->leftJoin('NHANVIEN as nv', 'hd.MANV', '=', 'nv.MANV')
            ->leftJoin('THANHTOAN as tt', 'hd.MAHD', '=', 'tt.MAHD')
            ->select('hd.MAHD', 'hd.NGAYLAP', 'hd.TRANGTHAI', 'hd.TONGTIEN_HD', 'hd.TEN_NGUOINHAN', 'hd.SDT_NGUOINHAN',
                'hd.PHUONG_THUC_GH', 'kh.TENKH', 'kh.SDT_KH', 'nv.TENNV', 'tt.PHUONG_THUC as PT_TT', 'tt.TRANGTHAI as TT_TT')
            ->when($chiTrangThai !== [], fn ($q) => $q->whereIn('hd.TRANGTHAI', $chiTrangThai))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('hd.MAHD', 'like', "%{$search}%")
                ->orWhere('kh.TENKH', 'like', "%{$search}%")
                ->orWhere('hd.TEN_NGUOINHAN', 'like', "%{$search}%")
                ->orWhere('hd.SDT_NGUOINHAN', 'like', "%{$search}%")))
            ->when($trangthai !== '', fn ($q) => $q->where('hd.TRANGTHAI', $trangthai))
            ->when($request->filled('makh'), fn ($q) => $q->where('hd.MAKH', (string) $request->query('makh')))
            ->when($ptgh !== '', fn ($q) => $q->where('hd.PHUONG_THUC_GH', $ptgh))
            ->when($tttt !== '', fn ($q) => $q->where('tt.TRANGTHAI', $tttt))
            ->when($from !== null, fn ($q) => $q->whereDate('hd.NGAYLAP', '>=', $from))
            ->when($to !== null, fn ($q) => $q->whereDate('hd.NGAYLAP', '<=', $to))
            ->orderByDesc('hd.NGAYLAP')
            ->orderBy('hd.MAHD');

        $trang = $this->phanTrang($query, $perPage);

        return [
            'donhang' => $trang['rows'],
            'total' => $trang['total'],
            'pages' => $trang['pages'],
            'page' => $trang['page'],
            'perPage' => $perPage,
            'search' => $search,
            'trangthai' => $trangthai,
            'ptgh' => $ptgh,
            'tttt' => $tttt,
            'khoang' => $khoang,
            'coLoc' => $search !== '' || $trangthai !== '' || $ptgh !== '' || $tttt !== '' || $khoang !== '',
            'statMap' => HoaDon::query()->toBase()
                ->when($chiTrangThai !== [], fn ($q) => $q->whereIn('TRANGTHAI', $chiTrangThai))
                ->selectRaw('TRANGTHAI, COUNT(*) AS cnt')->groupBy('TRANGTHAI')->pluck('cnt', 'TRANGTHAI')->all(),
            'choLau' => HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->where('NGAYLAP', '<=', now()->subHours(2))->count(),
        ];
    }

    /**
     * Hàng đếm trạng thái cho x-qt.dem: "Tất cả" đứng đầu, rồi từng trạng thái theo thứ tự đưa vào.
     *
     * @param  array<string, int>  $statMap
     * @param  list<string>  $trangThai
     * @return list<array{0: string, 1: string, 2: int}>
     */
    protected function hangDem(array $statMap, array $trangThai): array
    {
        return [
            ['', 'Tất cả', array_sum(array_intersect_key($statMap, array_flip($trangThai)))],
            ...array_map(fn (string $t): array => [$t, HoaDon::NHAN_TRANG_THAI[$t], (int) ($statMap[$t] ?? 0)], $trangThai),
        ];
    }
}
