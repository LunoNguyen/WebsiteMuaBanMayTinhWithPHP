<?php

namespace App\Models;

use App\Services\Realtime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hoá đơn bán hàng.
 */
#[Table(name: 'HOADON', key: 'MAHD', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MAHD', 'MATK', 'MANV', 'MAKH', 'NGAYLAP', 'TRANGTHAI', 'TONGTIEN_TRUOCGIAM', 'TONGTIEN_GIAM', 'TONGTIEN_HD', 'TEN_NGUOINHAN', 'SDT_NGUOINHAN', 'DIACHI_GIAOHANG', 'PHUONG_THUC_GH', 'GHI_CHU'])]
class HoaDon extends Model
{
    /**
     * Trạng thái kế tiếp của đơn theo quy trình xử lý.
     *
     * @var array<string, string>
     */
    public const BUOC_TIEP_THEO = [
        'ChoXacNhan' => 'DaXacNhan',
        'DaXacNhan' => 'DangGiao',
        'DangGiao' => 'DaGiao',
        'DaGiao' => 'HoanThanh',
    ];

    /**
     * Nhãn hiển thị của từng trạng thái đơn.
     *
     * @var array<string, string>
     */
    public const NHAN_TRANG_THAI = [
        'ChoXacNhan' => 'Chờ xác nhận',
        'DaXacNhan' => 'Đã xác nhận',
        'DangGiao' => 'Đang giao',
        'DaGiao' => 'Đã giao',
        'HoanThanh' => 'Hoàn thành',
        'DaHuy' => 'Đã huỷ',
    ];

    /**
     * Nhãn nút chuyển sang bước kế tiếp.
     *
     * @var array<string, string>
     */
    public const NHAN_BUOC_TIEP_THEO = [
        'ChoXacNhan' => 'Xác nhận',
        'DaXacNhan' => 'Bắt đầu giao',
        'DangGiao' => 'Đã giao',
        'DaGiao' => 'Hoàn thành',
    ];

    /**
     * Trạng thái còn huỷ được.
     *
     * @var list<string>
     */
    public const HUY_DUOC = ['ChoXacNhan', 'DaXacNhan'];

    /**
     * Chuyển đơn sang bước kế tiếp; $maNhanVien là nhân viên thao tác, được gán vào đơn (người xử lý gần nhất).
     * Đã xác nhận → Đang giao là lúc hàng rời kho: trừ tồn kho ở bước này, thiếu hàng thì báo lỗi và không chuyển.
     * Khi đơn đã giao, khoản COD / thanh toán tại cửa hàng đang chờ được ghi nhận đã trả.
     *
     * @throws ValidationException khi kho không đủ hàng để xuất
     */
    public function chuyenBuocTiepTheo(?string $maNhanVien = null): bool
    {
        $sanPhamXuat = DB::transaction(function () use ($maNhanVien): ?array {
            // Khoá đơn rồi mới đọc trạng thái, để hai lần bấm không xuất kho hai lần
            $trangThai = self::query()->whereKey($this->MAHD)->lockForUpdate()->value('TRANGTHAI');
            $tiepTheo = self::BUOC_TIEP_THEO[$trangThai] ?? null;

            if ($tiepTheo === null) {
                return null;
            }

            $xuat = $tiepTheo === 'DangGiao' ? $this->xuatKho() : [];

            $this->update(['TRANGTHAI' => $tiepTheo] + ($maNhanVien !== null ? ['MANV' => $maNhanVien] : []));

            if ($tiepTheo === 'DaGiao') {
                $this->thanhToan()
                    ->whereIn('PHUONG_THUC', ['COD', 'TienMat'])
                    ->where('TRANGTHAI', 'ChoThanhToan')
                    ->update(['TRANGTHAI' => 'DaThanhToan', 'NGAY_THANHTOAN' => now()]);
            }

            return $xuat;
        });

        if ($sanPhamXuat === null) {
            return false;
        }

        Realtime::donHang($this);

        if ($sanPhamXuat !== []) {
            Realtime::sanPham($sanPhamXuat);
        }

        return true;
    }

    /**
     * Trừ tồn kho cho các dòng của đơn (gọi trong transaction). Về 0 thì chuyển sản phẩm sang "hết hàng".
     *
     * @return list<string> mã các sản phẩm vừa xuất
     *
     * @throws ValidationException khi tồn kho thực tế không đủ
     */
    private function xuatKho(): array
    {
        $chiTiets = $this->chiTiets()->get(['MASP', 'SOLUONG']);
        $sanPhams = SanPham::query()->whereKey($chiTiets->pluck('MASP')->unique()->values())->lockForUpdate()->get()->keyBy('MASP');
        $canXuat = $chiTiets->groupBy('MASP')->map(fn ($dong): int => (int) $dong->sum('SOLUONG'));

        foreach ($canXuat as $maSanPham => $soLuong) {
            $sp = $sanPhams[$maSanPham] ?? null;

            if ($sp === null || $sp->SOLUONGTON < $soLuong) {
                throw ValidationException::withMessages([
                    'ton_kho' => 'Không xuất kho được đơn '.$this->MAHD.': '.($sp?->TENSP ?? $maSanPham)
                        .' trong kho còn '.(int) $sp?->SOLUONGTON.', đơn cần '.$soLuong.'.',
                ]);
            }
        }

        foreach ($canXuat as $maSanPham => $soLuong) {
            $sp = $sanPhams[$maSanPham];
            $conLai = $sp->SOLUONGTON - $soLuong;
            $sp->update(['SOLUONGTON' => $conLai] + ($conLai === 0 ? ['TRANGTHAI' => 'HetHang'] : []));
        }

        return $canXuat->keys()->map(fn ($ma): string => (string) $ma)->values()->all();
    }

    /**
     * Huỷ đơn khi đơn còn ở bước chờ / đã xác nhận. Hàng chưa rời kho nên không phải cộng lại tồn kho;
     * phần đang giữ cho đơn tự được nhả ra (xem SanPham::soLuongCoTheBan()).
     */
    public function huy(?string $maNhanVien = null): bool
    {
        // Khoá dòng đơn rồi mới kiểm tra trạng thái, để hai lần bấm huỷ không trả lượt mã hai lần
        $daHuy = DB::transaction(function () use ($maNhanVien): bool {
            $trangThai = self::query()->whereKey($this->MAHD)->lockForUpdate()->value('TRANGTHAI');

            if (! in_array($trangThai, self::HUY_DUOC, true)) {
                return false;
            }

            // Trả lại lượt dùng mã; khách được dùng lại mã đó cho đơn khác
            foreach ($this->khuyenMais()->pluck('KHUYENMAI.MAKM') as $maKhuyenMai) {
                KhuyenMai::query()->whereKey($maKhuyenMai)->where('DA_SUDUNG', '>', 0)->decrement('DA_SUDUNG');
            }

            $this->update(['TRANGTHAI' => 'DaHuy'] + ($maNhanVien !== null ? ['MANV' => $maNhanVien] : []));

            return true;
        });

        if ($daHuy) {
            $this->TRANGTHAI = 'DaHuy';
            Realtime::donHang($this);
            Realtime::sanPham($this->chiTiets()->pluck('MASP'));
        }

        return $daHuy;
    }

    /**
     * Nạp đủ quan hệ cho trang chi tiết đơn (Admin, Kho, Bán hàng).
     */
    public function napChiTiet(): static
    {
        return $this->load(['chiTiets.sanPham', 'thanhToan', 'khachHang', 'nhanVien', 'khuyenMais']);
    }

    /**
     * Ghi nhận khách đã trả tiền (chuyển khoản / QR, COD hoặc trả tại cửa hàng); gán nhân viên ghi nhận vào đơn.
     */
    public function xacNhanThanhToan(?string $maNhanVien = null): bool
    {
        if ($this->TRANGTHAI === 'DaHuy') {
            return false;
        }

        $daGhiNhan = $this->thanhToan()->where('TRANGTHAI', 'ChoThanhToan')
            ->update(['TRANGTHAI' => 'DaThanhToan', 'NGAY_THANHTOAN' => now()]) > 0;

        if ($daGhiNhan && $maNhanVien !== null) {
            $this->update(['MANV' => $maNhanVien]);
        }

        if ($daGhiNhan) {
            Realtime::donHang($this);
        }

        return $daGhiNhan;
    }

    /**
     * Doanh thu các đơn đã bán (đã giao, hoàn thành) theo từng tháng trong $soThang tháng gần nhất, tháng cũ trước.
     * Gom nhóm bằng PHP thay cho DATE_FORMAT / YEAR / MONTH để chạy được trên cả MySQL lẫn SQLite.
     *
     * @return list<array{thang_label: string, nam: int, thang: int, doanhthu: float, so_don: int, trung_binh: float, tong_giam: float}>
     */
    public static function doanhThuTheoThang(int $soThang): array
    {
        return self::query()
            ->whereIn('TRANGTHAI', ['DaGiao', 'HoanThanh'])
            ->where('NGAYLAP', '>=', now()->subMonths($soThang)->startOfDay())
            ->orderBy('NGAYLAP')
            ->get(['NGAYLAP', 'TONGTIEN_HD', 'TONGTIEN_GIAM'])
            ->groupBy(fn (self $hd): string => $hd->NGAYLAP->format('Y-m'))
            ->map(fn ($nhom): array => [
                'thang_label' => $nhom->first()->NGAYLAP->format('m/Y'),
                'nam' => (int) $nhom->first()->NGAYLAP->format('Y'),
                'thang' => (int) $nhom->first()->NGAYLAP->format('n'),
                'doanhthu' => (float) $nhom->sum('TONGTIEN_HD'),
                'so_don' => $nhom->count(),
                'trung_binh' => (float) $nhom->avg('TONGTIEN_HD'),
                'tong_giam' => (float) $nhom->sum('TONGTIEN_GIAM'),
            ])
            ->values()
            ->all();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'NGAYLAP' => 'datetime',
            'NGAY_CAPNHAT' => 'datetime',
            'TONGTIEN_TRUOCGIAM' => 'decimal:2',
            'TONGTIEN_GIAM' => 'decimal:2',
            'TONGTIEN_HD' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<KhachHang, $this>
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    /**
     * @return BelongsTo<NhanVien, $this>
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MANV', 'MANV');
    }

    /**
     * @return BelongsTo<TaiKhoan, $this>
     */
    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'MATK', 'MATK');
    }

    /**
     * @return HasMany<ChiTietHoaDon, $this>
     */
    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietHoaDon::class, 'MAHD', 'MAHD');
    }

    /**
     * Mã khuyến mãi đã áp vào đơn (mỗi đơn tối đa một mã), kèm số tiền giảm.
     *
     * @return BelongsToMany<KhuyenMai, $this>
     */
    public function khuyenMais(): BelongsToMany
    {
        return $this->belongsToMany(KhuyenMai::class, 'HOADON_KHUYENMAI', 'MAHD', 'MAKM')->withPivot('SOTIEN_GIAM');
    }

    /**
     * @return HasOne<ThanhToan, $this>
     */
    public function thanhToan(): HasOne
    {
        return $this->hasOne(ThanhToan::class, 'MAHD', 'MAHD');
    }
}
