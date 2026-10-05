<?php

namespace App\Services;

use App\Models\CtGioHang;
use App\Models\GioHang;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Giỏ hàng đang mua của một tài khoản khách hàng (GIOHANG trạng thái DangMua + CT_GIOHANG).
 */
class GioHangService
{
    /**
     * Giỏ đang mua của tài khoản, tạo mới nếu chưa có.
     */
    public function gioHang(TaiKhoan $taiKhoan): GioHang
    {
        return GioHang::query()->firstOrCreate(
            ['MATK' => $taiKhoan->MATK, 'TRANGTHAI' => 'DangMua'],
            ['NGAYTAO' => now()],
        );
    }

    /**
     * Các dòng trong giỏ, kèm sản phẩm, mô tả và ảnh đại diện.
     *
     * @return Collection<int, CtGioHang>
     */
    public function dong(TaiKhoan $taiKhoan): Collection
    {
        return $this->gioHang($taiKhoan)->chiTiets()
            ->with(['sanPham.moTa', 'sanPham.nhaSanXuat', 'sanPham.anhs' => fn ($q) => $q->where('LA_ANH_CHINH', true)])
            ->orderBy('MACTGH')
            ->get();
    }

    /**
     * Tổng số lượng sản phẩm trong giỏ (cho biểu tượng giỏ hàng).
     */
    public function soLuong(TaiKhoan $taiKhoan): int
    {
        return (int) CtGioHang::query()
            ->whereHas('gioHang', fn ($q) => $q->where('MATK', $taiKhoan->MATK)->where('TRANGTHAI', 'DangMua'))
            ->sum('SOLUONG');
    }

    /**
     * Tạm tính theo giá hiện tại của sản phẩm.
     *
     * @param  Collection<int, CtGioHang>  $dong
     */
    public function tamTinh(Collection $dong): float
    {
        return (float) $dong->sum(fn (CtGioHang $ct): float => $ct->SOLUONG * (float) $ct->sanPham->DONGIA_SP);
    }

    /**
     * Thêm sản phẩm (cộng dồn nếu đã có), không vượt quá tồn kho.
     */
    public function them(TaiKhoan $taiKhoan, SanPham $sanPham, int $soLuong): void
    {
        $this->kiemTraDangBan($sanPham);
        $gioHang = $this->gioHang($taiKhoan);

        $dong = $gioHang->chiTiets()->where('MASP', $sanPham->MASP)->first();
        $moi = ($dong?->SOLUONG ?? 0) + $soLuong;
        $this->kiemTraTonKho($sanPham, $moi);

        if ($dong) {
            $dong->update(['SOLUONG' => $moi, 'DONGIA_LUCAT' => $sanPham->DONGIA_SP]);

            return;
        }

        $gioHang->chiTiets()->create([
            'MASP' => $sanPham->MASP,
            'SOLUONG' => $soLuong,
            'DONGIA_LUCAT' => $sanPham->DONGIA_SP,
            'NGAYTHEM' => now(),
        ]);
    }

    /**
     * Đổi số lượng một dòng trong giỏ.
     */
    public function capNhat(CtGioHang $dong, int $soLuong): void
    {
        $this->kiemTraTonKho($dong->sanPham, $soLuong);
        $dong->update(['SOLUONG' => $soLuong]);
    }

    /**
     * Dòng giỏ có thuộc giỏ đang mua của tài khoản không.
     */
    public function cuaTaiKhoan(CtGioHang $dong, TaiKhoan $taiKhoan): bool
    {
        return $dong->gioHang?->MATK === $taiKhoan->MATK && $dong->gioHang->TRANGTHAI === 'DangMua';
    }

    private function kiemTraDangBan(SanPham $sanPham): void
    {
        if ($sanPham->TRANGTHAI !== 'DangBan' || $sanPham->SOLUONGTON < 1) {
            throw ValidationException::withMessages(['so_luong' => "{$sanPham->TENSP} hiện đã hết hàng."]);
        }
    }

    private function kiemTraTonKho(SanPham $sanPham, int $soLuong): void
    {
        if ($soLuong > $sanPham->SOLUONGTON) {
            throw ValidationException::withMessages([
                'so_luong' => "Chỉ còn {$sanPham->SOLUONGTON} sản phẩm {$sanPham->TENSP} trong kho.",
            ]);
        }
    }
}
