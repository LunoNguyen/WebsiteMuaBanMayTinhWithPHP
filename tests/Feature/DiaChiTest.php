<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiaChiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('dia-chi:tinh');
        Cache::forget('dia-chi:phuong:1');
    }

    public function test_chua_dang_nhap_khong_lay_duoc_danh_muc(): void
    {
        $this->getJson(route('diachi.tinh'))->assertUnauthorized();
    }

    public function test_tra_ve_tinh_thanh_gon_va_cache_lai(): void
    {
        Http::fake(['provinces.open-api.vn/*' => Http::response([
            ['name' => 'Thành phố Hà Nội', 'code' => 1, 'division_type' => 'thành phố trung ương', 'wards' => []],
            ['name' => 'Tỉnh Cao Bằng', 'code' => 4, 'division_type' => 'tỉnh', 'wards' => []],
        ])]);

        $khach = $this->taiKhoan('TK_KH001');
        $this->actingAs($khach)->getJson(route('diachi.tinh'))
            ->assertOk()
            ->assertExactJson([['ma' => 1, 'ten' => 'Thành phố Hà Nội'], ['ma' => 4, 'ten' => 'Tỉnh Cao Bằng']]);
        $this->actingAs($khach)->getJson(route('diachi.tinh'))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_tra_ve_phuong_xa_cua_mot_tinh(): void
    {
        Http::fake(['provinces.open-api.vn/api/v2/p/1*' => Http::response([
            'name' => 'Thành phố Hà Nội',
            'code' => 1,
            'wards' => [['name' => 'Phường Ba Đình', 'code' => 4, 'province_code' => 1]],
        ])]);

        $this->actingAs($this->taiKhoan('TK_NV001'))->getJson(route('diachi.phuong', 1))
            ->assertOk()
            ->assertExactJson([['ma' => 4, 'ten' => 'Phường Ba Đình']]);
    }

    public function test_api_loi_thi_bao_503_va_khong_cache(): void
    {
        Http::fake(['provinces.open-api.vn/*' => Http::response('loi', 500)]);

        $this->actingAs($this->taiKhoan('TK_KH001'))->getJson(route('diachi.tinh'))->assertServiceUnavailable();

        $this->assertFalse(Cache::has('dia-chi:tinh'));
    }
}
