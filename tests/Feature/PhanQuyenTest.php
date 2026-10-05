<?php

namespace Tests\Feature;

use Tests\TestCase;

class PhanQuyenTest extends TestCase
{
    public function test_khach_chua_dang_nhap_bi_chuyen_ve_dang_nhap(): void
    {
        $this->get('/admin')->assertRedirect('/dang-nhap');
        $this->get('/kho')->assertRedirect('/dang-nhap');
        $this->get('/ban-hang')->assertRedirect('/dang-nhap');
    }

    public function test_nhan_vien_kho_khong_vao_duoc_trang_quan_tri(): void
    {
        $this->actingAs($this->taiKhoan('TK_NV002'))
            ->get('/admin/tai-khoan')
            ->assertRedirect('/kho');
    }

    public function test_nhan_vien_ban_hang_khong_vao_duoc_kho(): void
    {
        $this->actingAs($this->taiKhoan('TK_NV001'))
            ->get('/kho')
            ->assertRedirect('/ban-hang');
    }

    public function test_admin_xem_duoc_moi_khu_vuc(): void
    {
        $admin = $this->taiKhoan('TK_ADMIN1');

        foreach (['/admin', '/admin/nhan-vien', '/admin/tai-khoan', '/admin/san-pham', '/admin/nhap-hang', '/admin/khuyen-mai',
            '/admin/don-hang', '/admin/khach-hang', '/admin/chatbot', '/admin/bao-cao', '/kho', '/kho/don-hang',
            '/ban-hang', '/ban-hang/khach-hang'] as $trang) {
            $this->actingAs($admin)->get($trang)->assertOk();
        }
    }

    public function test_tai_khoan_bi_khoa_giua_phien_bi_dang_xuat(): void
    {
        $taiKhoan = $this->taiKhoan('TK_NV002');
        $this->actingAs($taiKhoan);
        $taiKhoan->update(['TRANGTHAI' => 'KhoaTamThoi']);

        $this->get('/kho')->assertRedirect('/dang-nhap');
        $this->assertGuest();
    }

    public function test_admin_khong_tu_khoa_chinh_minh(): void
    {
        $admin = $this->taiKhoan('TK_ADMIN1');

        $this->actingAs($admin)->patch('/admin/tai-khoan/TK_ADMIN1/trang-thai');

        $this->assertSame('HoatDong', $admin->refresh()->TRANGTHAI);
    }
}
