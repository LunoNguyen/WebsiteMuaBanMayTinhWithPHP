<?php

namespace Tests\Feature;

use App\Models\HoaDon;
use App\Models\ThanhToan;
use Tests\TestCase;

class DonHangTest extends TestCase
{
    public function test_chuyen_don_qua_tung_buoc_va_ghi_nhan_cod_khi_da_giao(): void
    {
        $nhanVien = $this->taiKhoan('TK_NV001');
        $hoaDon = HoaDon::query()->findOrFail('HD008');
        $hoaDon->update(['TRANGTHAI' => 'DangGiao']);

        $this->actingAs($nhanVien)
            ->patch('/ban-hang/don-hang/HD008/buoc-tiep-theo')
            ->assertSessionHas('thong_bao');

        $this->assertSame('DaGiao', $hoaDon->refresh()->TRANGTHAI);
        $this->assertSame('DaThanhToan', ThanhToan::query()->where('MAHD', 'HD008')->value('TRANGTHAI'));
    }

    public function test_khong_huy_don_dang_giao(): void
    {
        $hoaDon = HoaDon::query()->findOrFail('HD008');
        $hoaDon->update(['TRANGTHAI' => 'DangGiao']);

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->patch('/admin/don-hang/HD008/huy');

        $this->assertSame('DangGiao', $hoaDon->refresh()->TRANGTHAI);
    }

    public function test_huy_don_cho_xac_nhan(): void
    {
        $hoaDon = HoaDon::query()->findOrFail('HD008');
        $hoaDon->update(['TRANGTHAI' => 'ChoXacNhan']);

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->patch('/admin/don-hang/HD008/huy');

        $this->assertSame('DaHuy', $hoaDon->refresh()->TRANGTHAI);
    }

    public function test_thao_tac_qua_get_khong_doi_du_lieu(): void
    {
        $hoaDon = HoaDon::query()->findOrFail('HD008');
        $hoaDon->update(['TRANGTHAI' => 'ChoXacNhan']);

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))
            ->get('/admin/don-hang?action=huy&mahd=HD008')
            ->assertOk();

        $this->assertSame('ChoXacNhan', $hoaDon->refresh()->TRANGTHAI);
    }
}
