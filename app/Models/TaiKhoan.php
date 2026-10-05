<?php

namespace App\Models;

use App\Enums\VaiTro;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Tài khoản đăng nhập (nhân viên, quản trị, khách hàng). Dùng làm model xác thực của Laravel.
 */
#[Table(name: 'TAIKHOAN', key: 'MATK', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MATK', 'MANV', 'MAKH', 'EMAIL_TK', 'MATKHAU', 'LOAI_TAIKHOAN', 'TRANGTHAI'])]
#[Hidden(['MATKHAU'])]
class TaiKhoan extends Authenticatable
{
    /**
     * Cột chứa mật khẩu (bcrypt).
     *
     * @var string
     */
    protected $authPasswordName = 'MATKHAU';

    /**
     * Bảng không có cột remember token.
     *
     * @var string
     */
    protected $rememberTokenName = '';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'MATKHAU' => 'hashed',
            'NGAYTAO' => 'datetime',
            'NGAY_CAPNHAT' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<NhanVien, $this>
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MANV', 'MANV');
    }

    /**
     * @return BelongsTo<KhachHang, $this>
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    /**
     * So mật khẩu với hash bcrypt đã lưu. Hash tạo bằng công cụ khác có tiền tố $2a$ / $2b$:
     * cùng thuật toán với $2y$ của PHP nhưng Laravel từ chối, nên đổi tiền tố trước khi so.
     */
    public function kiemTraMatKhau(string $matKhau): bool
    {
        $hash = (string) $this->MATKHAU;

        if (preg_match('/^\$2[ab]\$/', $hash) === 1) {
            $hash = '$2y$'.substr($hash, 4);
        }

        return password_get_info($hash)['algo'] === PASSWORD_BCRYPT && Hash::check($matKhau, $hash);
    }

    /**
     * Hash đang lưu chưa phải bcrypt $2y$ với độ khó hiện tại thì cần mã hoá lại.
     */
    public function canMaHoaLai(): bool
    {
        return ! str_starts_with((string) $this->MATKHAU, '$2y$') || Hash::needsRehash($this->MATKHAU);
    }

    /**
     * Tài khoản đang hoạt động (không bị khoá).
     */
    public function dangHoatDong(): bool
    {
        return $this->TRANGTHAI === 'HoatDong';
    }

    /**
     * Vai trò trong hệ thống quản lý, hoặc null khi tài khoản không được vào.
     */
    public function vaiTro(): ?VaiTro
    {
        return $this->lyDoKhongVaoDuoc() === null ? $this->vaiTroTheoChucVu() : null;
    }

    /**
     * Lý do tài khoản không được vào hệ thống, null nếu được vào.
     */
    public function lyDoKhongVaoDuoc(): ?string
    {
        return match ($this->LOAI_TAIKHOAN) {
            'Admin' => null,
            'KhachHang' => 'Tài khoản khách hàng không đăng nhập được vào trang quản lý.',
            'NhanVien' => match (true) {
                $this->nhanVien === null => 'Tài khoản chưa được gắn với nhân viên nào.',
                ! $this->nhanVien->TRANGTHAI => 'Nhân viên đã nghỉ việc, không thể đăng nhập.',
                $this->vaiTroTheoChucVu() === null => 'Chức vụ '.($this->nhanVien->chucVu?->TENCV ?? '').' chưa được cấp quyền vào hệ thống.',
                default => null,
            },
            default => 'Loại tài khoản không hợp lệ.',
        };
    }

    /**
     * Tên hiển thị (tên nhân viên hoặc khách hàng).
     */
    public function tenHienThi(): string
    {
        return $this->nhanVien?->TENNV ?? $this->khachHang?->TENKH ?? 'Người dùng';
    }

    private function vaiTroTheoChucVu(): ?VaiTro
    {
        if ($this->LOAI_TAIKHOAN === 'Admin') {
            return VaiTro::Admin;
        }

        return config('phanquyen.chuc_vu')[$this->nhanVien?->MACV] ?? null;
    }
}
