<?php

namespace App\Services;

use App\Models\CtGioHang;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lập hoá đơn: kiểm tra tồn kho, áp tối đa một mã khuyến mãi, tạo chi tiết và thanh toán, trừ tồn kho.
 * Dùng cho khách đặt online (từ giỏ hàng) và nhân viên tạo đơn tại quầy.
 */
class DatHangService
{
    public function __construct(
        private GioHangService $gioHang,
        private KhuyenMaiService $khuyenMai,
    ) {}

    /**
     * Khách đặt hàng từ giỏ. Đơn chờ cửa hàng xác nhận.
     *
     * @param  array{TEN_NGUOINHAN: string, SDT_NGUOINHAN: string, DIACHI_GIAOHANG: ?string, PHUONG_THUC_GH: string, PHUONG_THUC: string, GHI_CHU?: ?string}  $thongTin
     */
    public function datHang(TaiKhoan $taiKhoan, array $thongTin, ?string $maCode): HoaDon
    {
        return DB::transaction(function () use ($taiKhoan, $thongTin, $maCode): HoaDon {
            $dong = $this->gioHang->dong($taiKhoan);

            if ($dong->isEmpty()) {
                throw ValidationException::withMessages(['gio_hang' => 'Giỏ hàng đang trống.']);
            }

            $hoaDon = $this->lapHoaDon($dong, $thongTin, $maCode, [
                'MATK' => $taiKhoan->MATK,
                'MAKH' => $taiKhoan->MAKH,
                'MANV' => null,
                'TRANGTHAI' => 'ChoXacNhan',
            ], $taiKhoan, 'gio_hang');

            $this->gioHang->gioHang($taiKhoan)->update(['TRANGTHAI' => 'DaThanhToan']);

            return $hoaDon;
        });
    }

    /**
     * Nhân viên tạo đơn tại quầy cho khách (có hoặc không có hồ sơ khách hàng). Đơn đã được xác nhận.
     *
     * @param  list<array{MASP: string, SOLUONG: int|string}>  $sanPham
     * @param  array{TEN_NGUOINHAN: string, SDT_NGUOINHAN: string, DIACHI_GIAOHANG: ?string, PHUONG_THUC_GH: string, PHUONG_THUC: string, GHI_CHU?: ?string}  $thongTin
     */
    public function taoDonTaiQuay(string $maNhanVien, ?string $maKhachHang, array $sanPham, array $thongTin, ?string $maCode): HoaDon
    {
        return DB::transaction(function () use ($maNhanVien, $maKhachHang, $sanPham, $thongTin, $maCode): HoaDon {
            $taiKhoan = $maKhachHang !== null ? TaiKhoan::query()->where('MAKH', $maKhachHang)->first() : null;

            $dong = new Collection(array_map(
                fn (array $d): CtGioHang => new CtGioHang(['MASP' => $d['MASP'], 'SOLUONG' => (int) $d['SOLUONG']]),
                $sanPham,
            ));

            return $this->lapHoaDon($dong, $thongTin, $maCode, [
                'MATK' => $taiKhoan?->MATK,
                'MAKH' => $maKhachHang,
                'MANV' => $maNhanVien,
                'TRANGTHAI' => 'DaXacNhan',
            ], $taiKhoan, 'san_pham', $maKhachHang);
        });
    }

    /**
     * Phần chung: khoá sản phẩm, kiểm tra tồn kho, áp mã, ghi hoá đơn / chi tiết / khuyến mãi / thanh toán, trừ kho.
     * Phải gọi trong transaction.
     *
     * @param  Collection<int, CtGioHang>  $dong
     * @param  array<string, string|null>  $thongTin
     * @param  array{MATK: ?string, MAKH: ?string, MANV: ?string, TRANGTHAI: string}  $nguoiLap
     */
    private function lapHoaDon(Collection $dong, array $thongTin, ?string $maCode, array $nguoiLap, ?TaiKhoan $taiKhoan, string $truongLoi, ?string $maKhachHang = null): HoaDon
    {
        // Khoá sản phẩm để hai đơn cùng lúc không bán quá tồn kho
        $sanPhams = SanPham::query()->whereKey($dong->pluck('MASP')->unique()->values())->lockForUpdate()->get()->keyBy('MASP');

        foreach ($dong as $ct) {
            $sp = $sanPhams[$ct->MASP] ?? null;

            if ($sp === null || $sp->TRANGTHAI !== 'DangBan' || $sp->SOLUONGTON < 1) {
                throw ValidationException::withMessages([$truongLoi => ($sp?->TENSP ?? $ct->MASP).' hiện không còn bán.']);
            }

            if ($ct->SOLUONG > $sp->SOLUONGTON) {
                throw ValidationException::withMessages([
                    $truongLoi => "{$sp->TENSP} chỉ còn {$sp->SOLUONGTON} sản phẩm, không đặt được {$ct->SOLUONG}.",
                ]);
            }

            $ct->setRelation('sanPham', $sp);
        }

        $tamTinh = $this->gioHang->tamTinh($dong);
        $khuyenMai = null;
        $giam = 0.0;

        // Một đơn chỉ một mã. Khoá dòng mã trước để lượt dùng và luật "mỗi khách một lần" không bị vượt khi đặt đồng thời
        if ($maCode !== null && trim($maCode) !== '') {
            KhuyenMai::query()->where('MA_CODE', trim($maCode))->lockForUpdate()->first();
            ['khuyenMai' => $khuyenMai, 'giam' => $giam] = $this->khuyenMai->apDung($maCode, $dong, $taiKhoan, $maKhachHang);
        }

        $hoaDon = HoaDon::create([
            'MAHD' => $this->maHoaDonMoi(),
            ...$nguoiLap,
            'NGAYLAP' => now(),
            'TONGTIEN_TRUOCGIAM' => $tamTinh,
            'TONGTIEN_GIAM' => $giam,
            'TONGTIEN_HD' => $tamTinh - $giam,
            'TEN_NGUOINHAN' => $thongTin['TEN_NGUOINHAN'],
            'SDT_NGUOINHAN' => $thongTin['SDT_NGUOINHAN'],
            'DIACHI_GIAOHANG' => $thongTin['PHUONG_THUC_GH'] === 'GiaoHang' ? $thongTin['DIACHI_GIAOHANG'] : null,
            'PHUONG_THUC_GH' => $thongTin['PHUONG_THUC_GH'],
            'GHI_CHU' => $thongTin['GHI_CHU'] ?? null,
        ]);

        foreach ($dong as $ct) {
            $gia = (float) $ct->sanPham->DONGIA_SP;
            $hoaDon->chiTiets()->create([
                'MASP' => $ct->MASP,
                'SOLUONG' => $ct->SOLUONG,
                'DONGIA_LUCAT' => $gia,
                'THANHTIEN' => $gia * $ct->SOLUONG,
            ]);

            // Trừ tồn kho ngay khi đặt; về 0 thì chuyển "hết hàng"
            $conLai = $ct->sanPham->SOLUONGTON - $ct->SOLUONG;
            $ct->sanPham->update(['SOLUONGTON' => $conLai] + ($conLai === 0 ? ['TRANGTHAI' => 'HetHang'] : []));
        }

        if ($khuyenMai !== null) {
            DB::table('HOADON_KHUYENMAI')->insert(['MAHD' => $hoaDon->MAHD, 'MAKM' => $khuyenMai->MAKM, 'SOTIEN_GIAM' => $giam]);
            $khuyenMai->increment('DA_SUDUNG');
        }

        $hoaDon->thanhToan()->create([
            'PHUONG_THUC' => $thongTin['PHUONG_THUC'],
            'TRANGTHAI' => 'ChoThanhToan',
            'SOTIEN' => $tamTinh - $giam,
            'NOI_DUNG_CK' => $thongTin['PHUONG_THUC'] === 'COD' ? null : "TT {$hoaDon->MAHD}",
        ]);

        // Gửi sau khi transaction commit: báo đơn mới cho nhân viên, tồn kho mới cho trang sản phẩm
        Realtime::donHang($hoaDon, laDonMoi: true);
        Realtime::sanPham($dong->pluck('MASP'));

        return $hoaDon;
    }

    /**
     * Mã hoá đơn kế tiếp dạng HD011, HD012... (lấy số lớn nhất theo giá trị số, khoá để không trùng).
     */
    private function maHoaDonMoi(): string
    {
        $lonNhat = (int) HoaDon::query()->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(MAHD, 3) AS UNSIGNED)) AS so')
            ->value('so');

        return 'HD'.str_pad((string) ($lonNhat + 1), 3, '0', STR_PAD_LEFT);
    }
}
