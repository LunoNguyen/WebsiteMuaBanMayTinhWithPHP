<?php

namespace Tests\Feature;

use App\Services\MinioStorage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MinioStorageTest extends TestCase
{
    public function test_put_gui_content_type_dung_mot_lan_va_nam_trong_chu_ky(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $this->assertTrue(app(MinioStorage::class)->put('sanpham/SP001/a.png', 'abc', 'image/png'));

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->header('Content-Type') === ['image/png']
                && str_contains($request->header('Authorization')[0], 'SignedHeaders=content-type;host;x-amz-content-sha256;x-amz-date');
        });
    }

    public function test_link_anh_di_qua_website_va_theo_ten_mien_https_cua_tunnel(): void
    {
        Http::fake(['*' => Http::response('anh-png', 200, ['Content-Type' => 'image/png'])]);

        $this->assertSame(url('/anh/sanpham/SP001/a.png'), app(MinioStorage::class)->url('sanpham/SP001/a.png'));

        $this->get('/anh/sanpham/SP001/a.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');

        // Request đi qua Cloudflare Tunnel: link CSS / ảnh phải là https và đúng tên miền tunnel
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://abc.trycloudflare.com/')
            ->assertOk()
            ->assertSee('https://abc.trycloudflare.com/assets/shop/shop.css', false);
    }

    public function test_anh_khong_ton_tai_hoac_khong_phai_anh_tra_404(): void
    {
        Http::fake([
            '*/khong-co.png' => Http::response('', 404),
            '*/tai-lieu.pdf' => Http::response('%PDF', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $this->get('/anh/sanpham/khong-co.png')->assertNotFound();
        $this->get('/anh/sanpham/tai-lieu.pdf')->assertNotFound();
    }
}
