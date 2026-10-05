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
     * Chuyển đơn sang bước kế tiếp. Khi đơn đã giao, thanh toán COD đang chờ được ghi nhận đã trả.
     */
    public function chuyenBuocTiepTheo(): bool
    {
        $tiepTheo = self::BUOC_TIEP_THEO[$this->TRANGTHAI] ?? null;

        if ($tiepTheo === null) {
            return false;
        }

        DB::transaction(function () use ($tiepTheo): void {
            $this->update(['TRANGTHAI' => $tiepTheo]);

            if ($tiepTheo === 'DaGiao') {
                $this->thanhToan()
                    ->where('PHUONG_THUC', 'COD')
                    ->where('TRANGTHAI', 'ChoThanhToan')
                    ->update(['TRANGTHAI' => 'DaThanhToan', 'NGAY_THANHTOAN' => now()]);
            }
        });

        Realtime::donHang($this);

        return true;
    }

    /**
     * Huỷ đơn khi đơn còn ở bước chờ / đã xác nhận.
     */
    public function huy(): bool
    {
        // Khoá dòng đơn rồi mới kiểm tra trạng thái, để hai lần bấm huỷ không hoàn kho hai lần
        $daHuy = DB::transaction(function (): bool {
            $trangThai = self::query()->whereKey($this->MAHD)->lockForUpdate()->value('TRANGTHAI');

            if (! in_array($trangThai, self::HUY_DUOC, true)) {
                return false;
            }

            // Tồn kho bị trừ lúc đặt hàng, nên huỷ thì trả lại; sản phẩm đang "hết hàng" được bán lại
            foreach ($this->chiTiets()->get(['MASP', 'SOLUONG']) as $chiTiet) {
                SanPham::query()->whereKey($chiTiet->MASP)->increment('SOLUONGTON', $chiTiet->SOLUONG);
                SanPham::query()->whereKey($chiTiet->MASP)->where('TRANGTHAI', 'HetHang')->update(['TRANGTHAI' => 'DangBan']);
            }

            // Trả lại lượt dùng mã; khách được dùng lại mã đó cho đơn khác
            foreach ($this->khuyenMais()->pluck('KHUYENMAI.MAKM') as $maKhuyenMai) {
                KhuyenMai::query()->whereKey($maKhuyenMai)->where('DA_SUDUNG', '>', 0)->decrement('DA_SUDUNG');
            }

            $this->update(['TRANGTHAI' => 'DaHuy']);

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
     * Ghi nhận khách đã trả tiền (chuyển khoản / QR, hoặc COD thu tại quầy).
     */
    public function xacNhanThanhToan(): bool
    {
        if ($this->TRANGTHAI === 'DaHuy') {
            return false;
        }

        $daGhiNhan = $this->thanhToan()->where('TRANGTHAI', 'ChoThanhToan')
            ->update(['TRANGTHAI' => 'DaThanhToan', 'NGAY_THANHTOAN' => now()]) > 0;

        if ($daGhiNhan) {
            Realtime::donHang($this);
        }

        return $daGhiNhan;
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
