<?php

namespace Tests\Feature;

use App\Jobs\XuLyDatHang;
use App\Models\GioHang;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Đặt hàng qua hàng đợi "dat-hang": không tạo đơn trùng, đơn đúng với giỏ lúc bấm, báo kết quả cho khách.
 */
class DatHangHangDoiTest extends TestCase
{
    private TaiKhoan $khach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->khach = $this->taiKhoan('TK_KH001');
        GioHang::query()->where('MATK', 'TK_KH001')->where('TRANGTHAI', 'DangMua')->delete();
        $this->actingAs($this->khach);
    }

    public function test_dat_hang_dua_vao_hang_doi_va_cho_ket_qua(): void
    {
        Queue::fake();
        $ma = (string) Str::uuid();
        $soDon = HoaDon::query()->where('MATK', 'TK_KH001')->count();
        $this->post('/gio-hang/SP003', ['so_luong' => 2]);

        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma])
            ->assertRedirect("/thanh-toan/dang-xu-ly/{$ma}");

        Queue::assertPushedOn('dat-hang', XuLyDatHang::class, fn (XuLyDatHang $job): bool => $job->maYeuCau === $ma
            && $job->sanPham === [['MASP' => 'SP003', 'SOLUONG' => 2]]);

        $this->get("/thanh-toan/dang-xu-ly/{$ma}")->assertOk()->assertSee('Đang xử lý đơn hàng');
        $this->getJson("/thanh-toan/trang-thai/{$ma}")->assertOk()->assertJson(['trang_thai' => 'cho']);
        $this->assertSame($soDon, HoaDon::query()->where('MATK', 'TK_KH001')->count(), 'Chưa có đơn khi worker chưa chạy');

        // Worker xử lý xong thì trang chờ chuyển sang đơn vừa tạo
        $this->chayWorker();

        $hd = HoaDon::query()->where('MATK', 'TK_KH001')->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->getJson("/thanh-toan/trang-thai/{$ma}")->assertJson(['trang_thai' => 'xong']);
        $this->get("/thanh-toan/ket-qua/{$ma}")->assertRedirect("/don-hang-cua-toi/{$hd->MAHD}");
        $this->get("/thanh-toan/dang-xu-ly/{$ma}")->assertRedirect("/don-hang-cua-toi/{$hd->MAHD}");
    }

    public function test_gui_trung_ma_yeu_cau_chi_tao_mot_don(): void
    {
        $ma = (string) Str::uuid();
        $ton = (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON;
        $soDon = HoaDon::query()->where('MATK', 'TK_KH001')->count();

        $this->post('/gio-hang/SP003', ['so_luong' => 1]);
        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma])->assertSessionHasNoErrors();

        // Bấm lại (hoặc trình duyệt gửi lại) với cùng mã, kể cả khi giỏ lại có hàng
        $this->post('/gio-hang/SP003', ['so_luong' => 1]);
        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma])->assertRedirect();

        $this->assertSame($soDon + 1, HoaDon::query()->where('MATK', 'TK_KH001')->count());
        $this->assertSame($ton - 1, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);
    }

    public function test_don_dung_voi_gio_luc_bam_dat_hang_hang_them_sau_van_o_lai_gio(): void
    {
        Queue::fake();
        $ma = (string) Str::uuid();
        $this->post('/gio-hang/SP003', ['so_luong' => 1]);
        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma]);

        // Trong lúc đơn còn chờ, khách thêm sản phẩm khác ở tab khác
        $this->post('/gio-hang/SP001', ['so_luong' => 1]);

        $this->chayWorker();

        $hd = HoaDon::query()->where('MATK', 'TK_KH001')->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertSame(['SP003'], $hd->chiTiets()->pluck('MASP')->all());

        $conTrongGio = GioHang::query()->where('MATK', 'TK_KH001')->where('TRANGTHAI', 'DangMua')->firstOrFail()->chiTiets()->pluck('MASP')->all();
        $this->assertSame(['SP001'], $conTrongGio);
    }

    public function test_het_hang_luc_xu_ly_thi_bao_loi_va_giu_ma_giam_gia(): void
    {
        KhuyenMai::query()->create([
            'MAKM' => 'TGIU', 'TENKM' => 'Giữ mã', 'MA_CODE' => 'GIUMA', 'LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 100000,
            'SOTIENTOIDA_KM' => 100000, 'SOTIENTOITHIEU_NHANKM' => 0, 'SOLUONG_MA' => 10, 'DA_SUDUNG' => 0,
            'NGAYBD' => now()->subDay(), 'NGAYKT' => now()->addDay(), 'TRANGTHAI' => 'HoatDong',
        ]);
        Queue::fake();
        $ma = (string) Str::uuid();
        SanPham::query()->whereKey('SP003')->update(['SOLUONGTON' => 2, 'TRANGTHAI' => 'DangBan']);

        $this->post('/gio-hang/SP003', ['so_luong' => 2]);
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'GIUMA']);
        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma]);

        // Người khác mua mất trước khi worker tới lượt đơn này
        SanPham::query()->whereKey('SP003')->update(['SOLUONGTON' => 1]);
        $this->chayWorker();

        $this->get("/thanh-toan/ket-qua/{$ma}")->assertRedirect('/gio-hang')->assertSessionHasErrors('gio_hang');
        $this->assertSame(1, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON, 'Không trừ kho khi đơn lỗi');
        $this->assertSame('GIUMA', session('ma_giam_gia'), 'Đơn lỗi thì khách vẫn giữ mã giảm giá');
        $this->assertSame(0, (int) KhuyenMai::query()->findOrFail('TGIU')->DA_SUDUNG);
    }

    public function test_khong_xem_duoc_yeu_cau_cua_nguoi_khac(): void
    {
        Queue::fake();
        $ma = (string) Str::uuid();
        $this->post('/gio-hang/SP003');
        $this->post('/thanh-toan', [...$this->thongTin(), 'ma_yeu_cau' => $ma]);

        $this->actingAs($this->taiKhoan('TK_KH002'))->getJson("/thanh-toan/trang-thai/{$ma}")->assertNotFound();
    }

    /**
     * Giả lập worker lấy job đầu tiên trong hàng đợi ra chạy.
     */
    private function chayWorker(): void
    {
        app()->call([Queue::pushed(XuLyDatHang::class)->first(), 'handle']);
    }

    /**
     * @return array<string, string>
     */
    private function thongTin(): array
    {
        return ['TEN_NGUOINHAN' => 'Khách', 'SDT_NGUOINHAN' => '0901234567', 'PHUONG_THUC_GH' => 'TaiQuay', 'PHUONG_THUC' => 'COD'];
    }
}
