<?php

namespace Tests\Feature;

use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use Tests\TestCase;

class NhapKhoTest extends TestCase
{
    public function test_hoan_tat_nhap_kho_cong_ton_kho_mot_lan(): void
    {
        $phieu = PhieuNhapHang::query()->with('chiTiets')->firstOrFail();
        $phieu->update(['TRANGTHAI' => 'DaNhan']);
        $chiTiet = $phieu->chiTiets->first();
        $tonTruoc = SanPham::query()->findOrFail($chiTiet->MASP)->SOLUONGTON;
        $nhanVienKho = $this->taiKhoan('TK_NV002');

        $this->actingAs($nhanVienKho)
            ->postJson("/kho/phieu-nhap/{$phieu->MAPNH}/hoan-tat")
            ->assertOk()
            ->assertJson(['success' => true]);

        // Gọi lần hai không được cộng tồn kho thêm
        $this->actingAs($nhanVienKho)
            ->postJson("/kho/phieu-nhap/{$phieu->MAPNH}/hoan-tat")
            ->assertStatus(422);

        $this->assertSame('HoanThanh', $phieu->refresh()->TRANGTHAI);
        $this->assertSame(
            $tonTruoc + $phieu->chiTiets->where('MASP', $chiTiet->MASP)->sum('SOLUONG'),
            SanPham::query()->findOrFail($chiTiet->MASP)->SOLUONGTON,
        );
    }
}
