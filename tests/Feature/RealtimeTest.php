<?php

namespace Tests\Feature;

use App\Events\DonHangThayDoi;
use App\Events\SanPhamCapNhat;
use App\Models\GioHang;
use App\Models\HoaDon;
use App\Models\SanPham;
use App\Services\Realtime;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    public function test_dat_hang_bao_don_moi_cho_nhan_vien_va_khach_cung_ton_kho_moi(): void
    {
        Event::fake([DonHangThayDoi::class, SanPhamCapNhat::class]);
        $khach = $this->taiKhoan('TK_KH001');
        GioHang::query()->where('MATK', 'TK_KH001')->where('TRANGTHAI', 'DangMua')->delete();

        $this->actingAs($khach)->post('/gio-hang/SP003', ['so_luong' => 2]);
        $this->post('/thanh-toan', ['TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567', 'PHUONG_THUC_GH' => 'TaiQuay', 'PHUONG_THUC' => 'COD'])
            ->assertSessionHasNoErrors();

        Event::assertDispatched(DonHangThayDoi::class, function (DonHangThayDoi $e): bool {
            $kenh = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return $e->laDonMoi
                && $kenh === ['private-quan-tri', 'private-don-hang.TK_KH001']
                && $e->broadcastWith()['trang_thai'] === 'ChoXacNhan';
        });

        $ton = (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON;
        Event::assertDispatched(SanPhamCapNhat::class, fn (SanPhamCapNhat $e): bool => $e->sanPham->MASP === 'SP003'
            && $e->broadcastWith()['ton'] === $ton
            && array_map(fn ($c) => $c->name, $e->broadcastOn()) === ['cua-hang', 'private-quan-tri']);
    }

    public function test_doi_trang_thai_va_huy_don_phat_su_kien(): void
    {
        Event::fake([DonHangThayDoi::class, SanPhamCapNhat::class]);
        $hd = HoaDon::query()->findOrFail('HD008');
        $hd->update(['TRANGTHAI' => 'ChoXacNhan']);
        $this->actingAs($this->taiKhoan('TK_ADMIN1'));

        $this->patch('/admin/don-hang/HD008/buoc-tiep-theo');
        Event::assertDispatched(DonHangThayDoi::class, fn (DonHangThayDoi $e): bool => ! $e->laDonMoi
            && $e->broadcastWith()['trang_thai'] === 'DaXacNhan'
            && $e->broadcastWith()['nhan'] === 'Đã xác nhận');

        $this->patch('/admin/don-hang/HD008/huy');
        Event::assertDispatched(DonHangThayDoi::class, fn (DonHangThayDoi $e): bool => $e->broadcastWith()['trang_thai'] === 'DaHuy');
        Event::assertDispatched(SanPhamCapNhat::class);
    }

    public function test_admin_doi_gia_phat_su_kien_san_pham(): void
    {
        Event::fake([SanPhamCapNhat::class]);
        $sp = SanPham::query()->findOrFail('SP001');

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->put('/admin/san-pham/SP001', [
            'TENSP' => $sp->TENSP, 'MALOAI' => $sp->MALOAI, 'MANSX' => $sp->MANSX, 'DONGIA_SP' => 27500000, 'TRANGTHAI' => 'DangBan',
        ])->assertSessionHasNoErrors();

        Event::assertDispatched(SanPhamCapNhat::class, fn (SanPhamCapNhat $e): bool => $e->broadcastWith()['gia_text'] === formatVND(27500000));
    }

    public function test_quyen_nghe_kenh(): void
    {
        $kenh = Broadcast::driver()->getChannels();
        $quanTri = $kenh['quan-tri'];
        $donHang = $kenh['don-hang.{maTaiKhoan}'];
        $khach = $this->taiKhoan('TK_KH001');

        $this->assertTrue($quanTri($this->taiKhoan('TK_ADMIN1')));
        $this->assertTrue($quanTri($this->taiKhoan('TK_NV001')));
        $this->assertFalse($quanTri($khach));

        $this->assertTrue($donHang($khach, 'TK_KH001'));
        $this->assertFalse($donHang($khach, 'TK_KH002'));
    }

    public function test_reverb_tat_thi_khong_lam_hong_thao_tac(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'k',
            'broadcasting.connections.reverb.secret' => 's',
            'broadcasting.connections.reverb.app_id' => '1',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 1,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);
        Log::spy();

        Realtime::sanPham('SP001');

        Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $msg): bool => str_contains($msg, 'Reverb'));
    }

    public function test_trang_nap_cau_hinh_realtime_dung_kenh_cua_tung_nguoi(): void
    {
        config(['cuahang.realtime.bat' => true, 'cuahang.realtime.key' => 'khoa-test', 'cuahang.realtime.port' => 8080]);

        $this->get('/')->assertOk()->assertSee('"kenhDon":null', false)->assertSee('"nhanVien":false', false);

        $this->actingAs($this->taiKhoan('TK_KH001'))->get('/')
            ->assertSee('"kenhDon":"don-hang.TK_KH001"', false);

        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->get('/admin')
            ->assertSee('"nhanVien":true', false)
            ->assertSee('data-rt-vung="kpi"', false);

        config(['cuahang.realtime.bat' => false]);
        $this->get('/')->assertDontSee('NEXUS_REALTIME', false);
    }
}
