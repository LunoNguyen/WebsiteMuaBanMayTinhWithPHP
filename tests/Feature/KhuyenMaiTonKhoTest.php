<?php

namespace Tests\Feature;

use App\Models\GioHang;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Luật mã giảm giá (một mã mỗi đơn, mỗi khách dùng một lần, giảm tối đa, đơn tối thiểu) và tồn kho khi đặt.
 */
class KhuyenMaiTonKhoTest extends TestCase
{
    private TaiKhoan $khach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->khach = $this->taiKhoan('TK_KH001');
        GioHang::query()->where('MATK', $this->khach->MATK)->where('TRANGTHAI', 'DangMua')->delete();
        $this->actingAs($this->khach);
    }

    public function test_moi_khach_chi_dung_mot_ma_mot_lan(): void
    {
        $km = $this->ma('LANDAU', ['LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 500000, 'SOTIENTOIDA_KM' => 500000]);

        $this->datHang('SP003', 1, 'LANDAU')->assertSessionHasNoErrors();
        $this->assertSame(1, (int) $km->refresh()->DA_SUDUNG);

        $this->post('/gio-hang/SP003');
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'LANDAU'])
            ->assertSessionHasErrors(['ma_code' => 'Bạn đã dùng mã này cho một đơn trước đó. Mỗi khách chỉ được dùng mỗi mã một lần.']);

        // Lách bằng cách gửi thẳng mã lúc đặt hàng cũng bị chặn
        $this->withSession(['ma_giam_gia' => 'LANDAU'])->post('/thanh-toan', $this->thongTin())->assertSessionHasErrors('ma_code');
        $this->assertSame(1, (int) $km->refresh()->DA_SUDUNG);

        // Khách khác vẫn dùng được
        $this->actingAs($this->taiKhoan('TK_KH002'));
        GioHang::query()->where('MATK', 'TK_KH002')->where('TRANGTHAI', 'DangMua')->delete();
        $this->datHang('SP003', 1, 'LANDAU')->assertSessionHasNoErrors();
        $this->assertSame(2, (int) $km->refresh()->DA_SUDUNG);
    }

    public function test_huy_don_tra_lai_luot_va_khach_dung_lai_duoc(): void
    {
        $km = $this->ma('HUYLAI', ['LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 200000, 'SOTIENTOIDA_KM' => 200000]);

        $this->datHang('SP003', 1, 'HUYLAI');
        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();

        $this->patch("/don-hang-cua-toi/{$hd->MAHD}/huy");
        $this->assertSame(0, (int) $km->refresh()->DA_SUDUNG);

        $this->post('/gio-hang/SP003');
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'HUYLAI'])->assertSessionHasNoErrors();
    }

    public function test_moi_don_chi_mot_ma(): void
    {
        $this->ma('MAMOT', ['LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 100000, 'SOTIENTOIDA_KM' => 100000]);
        $this->ma('MAHAI', ['LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 100000, 'SOTIENTOIDA_KM' => 100000]);

        $this->post('/gio-hang/SP003');
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'MAMOT'])->assertSessionHasNoErrors();
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'MAHAI'])->assertSessionHasErrors('ma_code');
        $this->assertSame('MAMOT', session('ma_giam_gia'));

        $this->post('/thanh-toan', $this->thongTin())->assertSessionHasNoErrors();
        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertSame(1, $hd->khuyenMais()->count());
        $this->assertSame(100000.0, (float) $hd->TONGTIEN_GIAM);
    }

    public function test_giam_toi_da_va_don_toi_thieu(): void
    {
        $this->ma('PHANTRAM', ['LOAI_KM' => 'PhanTram', 'GIATRI_KM' => 50, 'SOTIENTOIDA_KM' => 2000000, 'SOTIENTOITHIEU_NHANKM' => 40000000]);

        $this->post('/gio-hang/SP001'); // 28 triệu
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'PHANTRAM'])
            ->assertSessionHasErrors(['ma_code' => 'Đơn hàng cần tối thiểu '.formatVND(40000000).' để dùng mã này.']);

        $this->post('/gio-hang/SP001'); // 56 triệu
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'PHANTRAM'])->assertSessionHasNoErrors();
        $this->post('/thanh-toan', $this->thongTin())->assertSessionHasNoErrors();

        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertSame(2000000.0, (float) $hd->TONGTIEN_GIAM, '50% của 56tr bị chặn ở mức tối đa 2tr');
    }

    public function test_dat_hang_tru_ton_kho_va_khong_dat_qua_ton(): void
    {
        SanPham::query()->whereKey('SP003')->update(['SOLUONGTON' => 3, 'TRANGTHAI' => 'DangBan']);

        $this->post('/gio-hang/SP003', ['so_luong' => 4])->assertSessionHasErrors('so_luong');
        $this->post('/gio-hang/SP003', ['so_luong' => 3])->assertSessionHasNoErrors();

        // Trong lúc khách chần chừ, tồn kho bị bán bớt: đặt hàng phải bị chặn, không trừ âm
        SanPham::query()->whereKey('SP003')->update(['SOLUONGTON' => 2]);
        $this->post('/thanh-toan', $this->thongTin())->assertSessionHasErrors('gio_hang');
        $this->assertSame(2, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);

        SanPham::query()->whereKey('SP003')->update(['SOLUONGTON' => 3]);
        $this->post('/thanh-toan', $this->thongTin())->assertSessionHasNoErrors();

        $sp = SanPham::query()->findOrFail('SP003');
        $this->assertSame(0, (int) $sp->SOLUONGTON);
        $this->assertSame('HetHang', $sp->TRANGTHAI);
    }

    public function test_tao_don_tai_quay_tru_kho_va_ap_luat_ma(): void
    {
        $this->ma('QUAY', ['LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 300000, 'SOTIENTOIDA_KM' => 300000]);
        $ton = (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON;
        $this->actingAs($this->taiKhoan('TK_ADMIN1'));

        $don = fn (int $soLuong) => $this->post('/admin/don-hang', [
            'MAKH' => 'KH001', 'TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567',
            'PHUONG_THUC_GH' => 'TaiQuay', 'PHUONG_THUC' => 'COD', 'MA_CODE' => 'quay',
            'san_pham' => [['MASP' => 'SP003', 'SOLUONG' => $soLuong]],
        ]);

        $don($ton + 1)->assertSessionHasErrors('san_pham');
        $don(2)->assertSessionHasNoErrors();

        $hd = HoaDon::query()->where('MANV', 'NV001')->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertSame('DaXacNhan', $hd->TRANGTHAI);
        $this->assertSame('KH001', $hd->MAKH);
        $this->assertSame(300000.0, (float) $hd->TONGTIEN_GIAM);
        $this->assertSame($ton - 2, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);

        // KH001 đã dùng mã QUAY tại quầy thì đặt online cũng không dùng lại được
        $this->actingAs($this->khach)->post('/gio-hang/SP003');
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'QUAY'])->assertSessionHasErrors('ma_code');
    }

    /**
     * @param  array<string, mixed>  $thuocTinh
     */
    private function ma(string $code, array $thuocTinh): KhuyenMai
    {
        return KhuyenMai::query()->create([
            'MAKM' => 'T'.substr($code, 0, 9),
            'TENKM' => 'Mã '.$code,
            'MA_CODE' => $code,
            'SOTIENTOITHIEU_NHANKM' => 0,
            'SOLUONG_MA' => 100,
            'DA_SUDUNG' => 0,
            'NGAYBD' => now()->subDay(),
            'NGAYKT' => now()->addDay(),
            'TRANGTHAI' => 'HoatDong',
            ...$thuocTinh,
        ]);
    }

    private function datHang(string $maSanPham, int $soLuong, string $maCode): TestResponse
    {
        $this->post("/gio-hang/{$maSanPham}", ['so_luong' => $soLuong]);
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => $maCode]);

        return $this->post('/thanh-toan', $this->thongTin());
    }

    /**
     * @return array<string, string>
     */
    private function thongTin(): array
    {
        return ['TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567', 'PHUONG_THUC_GH' => 'TaiQuay', 'PHUONG_THUC' => 'COD'];
    }
}
