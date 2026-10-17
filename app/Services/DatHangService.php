<?php

namespace App\Services;

use App\Mail\XacNhanDonHangMail;
use App\Models\CtGioHang;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

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
     * Danh sách sản phẩm trong giỏ ở thời điểm khách bấm "Đặt hàng" (chỉ mã và số lượng).
     *
     * @return list<array{MASP: string, SOLUONG: int}>
     */
    public function chupGioHang(TaiKhoan $taiKhoan): array
    {
        return $this->gioHang->dong($taiKhoan)
            ->map(fn (CtGioHang $ct): array => ['MASP' => $ct->MASP, 'SOLUONG' => (int) $ct->SOLUONG])
            ->values()
            ->all();
    }

    /**
     * Đặt hàng theo danh sách đã chụp lúc khách bấm "Đặt hàng" (worker hàng đợi gọi hàm này).
     * Giá lấy theo thời điểm xử lý; các dòng đã mua được bỏ khỏi giỏ, dòng khách thêm sau vẫn giữ lại.
     *
     * @param  list<array{MASP: string, SOLUONG: int|string}>  $sanPham
     * @param  array{TEN_NGUOINHAN: string, SDT_NGUOINHAN: string, DIACHI_GIAOHANG: ?string, PHUONG_THUC_GH: string, PHUONG_THUC: string, GHI_CHU?: ?string}  $thongTin
     */
    public function datHangTheoDanhSach(TaiKhoan $taiKhoan, array $sanPham, array $thongTin, ?string $maCode): HoaDon
    {
        if ($sanPham === []) {
            throw ValidationException::withMessages(['gio_hang' => 'Giỏ hàng đang trống.']);
        }

        $hoaDon = DB::transaction(function () use ($taiKhoan, $sanPham, $thongTin, $maCode): HoaDon {
            $hoaDon = $this->lapHoaDon($this->taoDong($sanPham), $thongTin, $maCode, [
                'MATK' => $taiKhoan->MATK,
                'MAKH' => $taiKhoan->MAKH,
                'MANV' => null,
                'TRANGTHAI' => 'ChoXacNhan',
            ], $taiKhoan, 'gio_hang');

            $gioHang = $this->gioHang->gioHang($taiKhoan);
            $gioHang->chiTiets()->whereIn('MASP', array_column($sanPham, 'MASP'))->delete();

            if (! $gioHang->chiTiets()->exists()) {
                $gioHang->update(['TRANGTHAI' => 'DaThanhToan']);
            }

            return $hoaDon;
        });

        $this->guiEmailXacNhan($hoaDon, $taiKhoan);

        return $hoaDon;
    }

    /**
     * Tự động gửi email xác nhận đơn hàng cho khách sau khi đặt hàng thành công.
     * Bọc trong try/catch để nếu chưa cấu hình SMTP hoặc mất mạng thì đơn hàng vẫn hoàn tất bình thường.
     */
    public function guiEmailXacNhan(HoaDon $hoaDon, ?TaiKhoan $taiKhoan = null): void
    {
        try {
            $taiKhoan ??= $hoaDon->taiKhoan;
            $email = $hoaDon->khachHang?->EMAIL_KH ?: ($taiKhoan?->khachHang?->EMAIL_KH ?: $taiKhoan?->EMAIL_TK);

            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new XacNhanDonHangMail($hoaDon));
            }
        } catch (Throwable $e) {
            Log::warning("Không gửi được email xác nhận đơn hàng {$hoaDon->MAHD}: ".$e->getMessage());
        }
    }

    /**
     * Dòng sản phẩm (chưa lưu) từ danh sách mã + số lượng; gộp các dòng trùng mã.
     *
     * @param  list<array{MASP: string, SOLUONG: int|string}>  $sanPham
     * @return Collection<int, CtGioHang>
     */
    private function taoDong(array $sanPham): Collection
    {
        $gop = [];
        foreach ($sanPham as $d) {
            $gop[$d['MASP']] = ($gop[$d['MASP']] ?? 0) + (int) $d['SOLUONG'];
        }

        return new Collection(array_map(
            fn (string $ma, int $soLuong): CtGioHang => new CtGioHang(['MASP' => $ma, 'SOLUONG' => $soLuong]),
            array_keys($gop),
            $gop,
        ));
    }

    /**
     * Nhân viên tạo đơn tại quầy cho khách (có hoặc không có hồ sơ khách hàng). Đơn đã được xác nhận.
     *
     * @param  list<array{MASP: string, SOLUONG: int|string}>  $sanPham
     * @param  array{TEN_NGUOINHAN: string, SDT_NGUOINHAN: string, DIACHI_GIAOHANG: ?string, PHUONG_THUC_GH: string, PHUONG_THUC: string, GHI_CHU?: ?string}  $thongTin
     */
    public function taoDonTaiQuay(string $maNhanVien, ?string $maKhachHang, array $sanPham, array $thongTin, ?string $maCode): HoaDon
    {
        $hoaDon = DB::transaction(function () use ($maNhanVien, $maKhachHang, $sanPham, $thongTin, $maCode): HoaDon {
            $taiKhoan = $maKhachHang !== null ? TaiKhoan::query()->where('MAKH', $maKhachHang)->first() : null;

            return $this->lapHoaDon($this->taoDong($sanPham), $thongTin, $maCode, [
                'MATK' => $taiKhoan?->MATK,
                'MAKH' => $maKhachHang,
                'MANV' => $maNhanVien,
                'TRANGTHAI' => 'DaXacNhan',
            ], $taiKhoan, 'san_pham', $maKhachHang);
        });

        $this->guiEmailXacNhan($hoaDon);

        return $hoaDon;
    }

    /**
     * Phần chung: khoá sản phẩm, kiểm tra số lượng còn bán, áp mã, ghi hoá đơn / chi tiết / khuyến mãi / thanh toán.
     * Không trừ tồn kho ở đây: hàng chỉ rời kho (trừ SOLUONGTON) khi kho xuất đơn, xem HoaDon::chuyenBuocTiepTheo().
     * Phải gọi trong transaction.
     *
     * @param  Collection<int, CtGioHang>  $dong
     * @param  array<string, string|null>  $thongTin
     * @param  array{MATK: ?string, MAKH: ?string, MANV: ?string, TRANGTHAI: string}  $nguoiLap
     */
    private function lapHoaDon(Collection $dong, array $thongTin, ?string $maCode, array $nguoiLap, ?TaiKhoan $taiKhoan, string $truongLoi, ?string $maKhachHang = null): HoaDon
    {
        // Khoá sản phẩm để hai đơn cùng lúc không đặt quá số lượng còn bán (tồn kho trừ phần đang giữ cho đơn chưa xuất)
        $sanPhams = SanPham::query()->whereKey($dong->pluck('MASP')->unique()->values())->lockForUpdate()->get()->keyBy('MASP');

        foreach ($dong as $ct) {
            $sp = $sanPhams[$ct->MASP] ?? null;

            if ($sp === null || $sp->TRANGTHAI !== 'DangBan' || $sp->soLuongCoTheBan() < 1) {
                throw ValidationException::withMessages([$truongLoi => ($sp?->TENSP ?? $ct->MASP).' hiện không còn bán.']);
            }

            if ($ct->SOLUONG > $sp->soLuongCoTheBan()) {
                throw ValidationException::withMessages([
                    $truongLoi => "{$sp->TENSP} chỉ còn {$sp->soLuongCoTheBan()} sản phẩm, không đặt được {$ct->SOLUONG}.",
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
        }

        if ($khuyenMai !== null) {
            DB::table('HOADON_KHUYENMAI')->insert(['MAHD' => $hoaDon->MAHD, 'MAKM' => $khuyenMai->MAKM, 'SOTIEN_GIAM' => $giam]);
            $khuyenMai->increment('DA_SUDUNG');
        }

        $hoaDon->thanhToan()->create([
            'PHUONG_THUC' => $thongTin['PHUONG_THUC'],
            'TRANGTHAI' => 'ChoThanhToan',
            'SOTIEN' => $tamTinh - $giam,
            'NOI_DUNG_CK' => in_array($thongTin['PHUONG_THUC'], ['COD', 'TienMat'], true) ? null : "TT {$hoaDon->MAHD}",
        ]);

        // Gửi sau khi transaction commit: báo đơn mới cho nhân viên, số lượng còn bán mới cho trang sản phẩm
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
