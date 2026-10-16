<?php

namespace Tests\Feature;

use App\Models\GioHang;
use App\Models\HoaDon;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Tests\TestCase;

class XuLyDonHangTest extends TestCase
{
    private TaiKhoan $khach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->khach = $this->taiKhoan('TK_KH001');
        GioHang::query()->where('MATK', $this->khach->MATK)->where('TRANGTHAI', 'DangMua')->delete();
    }

    public function test_chi_tru_ton_khi_xuat_kho_va_gan_nhan_vien_xu_ly(): void
    {
        $ton = (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON;
        $hd = $this->datHang(['PHUONG_THUC_GH' => 'GiaoHang', 'DIACHI_GIAOHANG' => '1 Lê Lợi, Phường Bến Thành, Thành phố Hồ Chí Minh', 'PHUONG_THUC' => 'COD']);
        $this->assertNull($hd->MANV);

        // Nhân viên bán hàng xác nhận: đơn gán cho NV002, hàng vẫn trong kho
        $this->actingAs($this->taiKhoan('TK_NV001'))->patch(route('banhang.donhang.buoc-tiep-theo', $hd))->assertSessionHasNoErrors();
        $this->assertSame(['DaXacNhan', 'NV002'], [$hd->refresh()->TRANGTHAI, $hd->MANV]);
        $this->assertSame($ton, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);

        // Kho xuất đơn: trừ tồn, đơn gán cho nhân viên kho NV003
        $this->actingAs($this->taiKhoan('TK_NV002'))->patch(route('kho.donhang.xuat-kho', $hd))->assertSessionHasNoErrors();
        $this->assertSame(['DangGiao', 'NV003'], [$hd->refresh()->TRANGTHAI, $hd->MANV]);
        $this->assertSame($ton - 2, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);

        // Khách thấy nhân viên phụ trách (mã, họ tên, số điện thoại)
        $this->actingAs($this->khach)->get(route('donhang.show', $hd))
            ->assertOk()->assertSee('Nguyễn Thị Kho')->assertSee('NV003')->assertSee('0903333333');
    }

    public function test_xuat_kho_thieu_hang_thi_bao_loi_va_giu_nguyen_don(): void
    {
        $hd = HoaDon::query()->findOrFail('HD013');
        $maSanPham = $hd->chiTiets()->value('MASP');
        SanPham::query()->whereKey($maSanPham)->update(['SOLUONGTON' => 0]);

        $this->actingAs($this->taiKhoan('TK_NV002'))->patch(route('kho.donhang.xuat-kho', $hd))->assertSessionHasErrors('ton_kho');

        $this->assertSame('DaXacNhan', $hd->refresh()->TRANGTHAI);
        $this->assertSame(0, (int) SanPham::query()->findOrFail($maSanPham)->SOLUONGTON);
    }

    public function test_huy_don_chua_xuat_khong_doi_ton_kho_va_gan_nhan_vien(): void
    {
        $hd = HoaDon::query()->findOrFail('HD014');
        $maSanPham = $hd->chiTiets()->value('MASP');
        $ton = (int) SanPham::query()->findOrFail($maSanPham)->SOLUONGTON;

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->patch(route('admin.donhang.huy', $hd))->assertSessionHasNoErrors();

        $this->assertSame(['DaHuy', 'NV001'], [$hd->refresh()->TRANGTHAI, $hd->MANV]);
        $this->assertSame($ton, (int) SanPham::query()->findOrFail($maSanPham)->SOLUONGTON);
    }

    public function test_nhan_tai_cua_hang_thanh_toan_tai_cua_hang_thay_cod(): void
    {
        $this->actingAs($this->khach)->post('/gio-hang/SP003');
        $this->get('/thanh-toan')->assertOk()->assertSee('Thanh toán tại cửa hàng');

        $taiQuay = ['TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567', 'PHUONG_THUC_GH' => 'TaiQuay'];
        $this->post('/thanh-toan', $taiQuay + ['PHUONG_THUC' => 'COD'])->assertSessionHasErrors('PHUONG_THUC');
        $this->post('/thanh-toan', ['PHUONG_THUC_GH' => 'GiaoHang', 'DIACHI_GIAOHANG' => '1 Lê Lợi', 'PHUONG_THUC' => 'TienMat'] + $taiQuay)
            ->assertSessionHasErrors('PHUONG_THUC');

        $this->post('/thanh-toan', $taiQuay + ['PHUONG_THUC' => 'TienMat'])->assertSessionHasNoErrors();
        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertDatabaseHas('THANHTOAN', ['MAHD' => $hd->MAHD, 'PHUONG_THUC' => 'TienMat', 'TRANGTHAI' => 'ChoThanhToan']);

        // Khách nhận hàng tại cửa hàng (đã giao): khoản thanh toán tại cửa hàng được ghi nhận đã trả
        $nhanVien = $this->taiKhoan('TK_ADMIN1');
        foreach (range(1, 3) as $buoc) {
            $this->actingAs($nhanVien)->patch(route('admin.donhang.buoc-tiep-theo', $hd))->assertSessionHasNoErrors();
        }
        $this->assertSame('DaGiao', $hd->refresh()->TRANGTHAI);
        $this->assertDatabaseHas('THANHTOAN', ['MAHD' => $hd->MAHD, 'PHUONG_THUC' => 'TienMat', 'TRANGTHAI' => 'DaThanhToan']);
    }

    public function test_footer_co_vi_tri_cua_hang(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Vị trí cửa hàng')
            ->assertSee('Trường Đại học Công Thương TP.HCM')
            ->assertSee('140 Lê Trọng Tấn');
    }

    /**
     * Khách đặt 2 cái SP003, trả về đơn vừa tạo.
     *
     * @param  array<string, string>  $thongTin
     */
    private function datHang(array $thongTin): HoaDon
    {
        $this->actingAs($this->khach)->post('/gio-hang/SP003', ['so_luong' => 2])->assertSessionHasNoErrors();
        $this->post('/thanh-toan', ['TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567'] + $thongTin)->assertSessionHasNoErrors();

        return HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
    }
}
