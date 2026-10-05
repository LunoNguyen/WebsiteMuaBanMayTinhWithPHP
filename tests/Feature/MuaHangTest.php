<?php

namespace Tests\Feature;

use App\Models\CtGioHang;
use App\Models\GioHang;
use App\Models\HoaDon;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Tests\TestCase;

class MuaHangTest extends TestCase
{
    private TaiKhoan $khach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->khach = $this->taiKhoan('TK_KH001');
        GioHang::query()->where('MATK', $this->khach->MATK)->where('TRANGTHAI', 'DangMua')->delete();
    }

    public function test_khach_chua_dang_nhap_xem_duoc_san_pham_nhung_khong_mua_duoc(): void
    {
        $this->get('/')->assertOk()->assertSee('Danh mục sản phẩm');
        $this->get('/san-pham?q=asus&sap_xep=gia-tang')->assertOk();
        $this->get('/san-pham/SP001')->assertOk()->assertSee('Đăng nhập để mua');

        $this->post('/gio-hang/SP001')->assertRedirect('/dang-nhap');
        $this->get('/gio-hang')->assertRedirect('/dang-nhap');
        $this->get('/thanh-toan')->assertRedirect('/dang-nhap');
    }

    public function test_nhan_vien_khong_dung_gio_hang(): void
    {
        $this->actingAs($this->taiKhoan('TK_ADMIN1'))->post('/gio-hang/SP001')->assertRedirect();

        $this->assertDatabaseMissing('GIOHANG', ['MATK' => 'TK_ADMIN1', 'TRANGTHAI' => 'DangMua']);
    }

    public function test_them_cap_nhat_va_xoa_san_pham_trong_gio(): void
    {
        $this->actingAs($this->khach);

        $this->post('/gio-hang/SP001', ['so_luong' => 2])->assertSessionHasNoErrors();
        $this->post('/gio-hang/SP001', ['so_luong' => 1]);

        $dong = CtGioHang::query()->whereHas('gioHang', fn ($q) => $q->where('MATK', $this->khach->MATK)->where('TRANGTHAI', 'DangMua'))->sole();
        $this->assertSame(3, (int) $dong->SOLUONG);

        $this->get('/gio-hang')->assertOk()->assertSee($dong->sanPham->TENSP);

        $this->patch("/gio-hang/dong/{$dong->MACTGH}", ['so_luong' => 5]);
        $this->assertSame(5, (int) $dong->refresh()->SOLUONG);

        $this->delete("/gio-hang/dong/{$dong->MACTGH}");
        $this->assertModelMissing($dong);
    }

    public function test_khong_them_qua_so_luong_ton(): void
    {
        SanPham::query()->whereKey('SP001')->update(['SOLUONGTON' => 1]);

        $this->actingAs($this->khach)->post('/gio-hang/SP001', ['so_luong' => 2])
            ->assertSessionHasErrors('so_luong');
    }

    public function test_khong_sua_dong_gio_cua_nguoi_khac(): void
    {
        $this->actingAs($this->taiKhoan('TK_KH002'))->post('/gio-hang/SP001');
        $dong = CtGioHang::query()->latest('MACTGH')->first();

        $this->actingAs($this->khach)->patch("/gio-hang/dong/{$dong->MACTGH}", ['so_luong' => 9])->assertNotFound();
    }

    public function test_ap_ma_giam_gia_va_dat_hang_tru_ton_kho(): void
    {
        $km = $this->maConHan(['LOAI_KM' => 'PhanTram', 'GIATRI_KM' => 10, 'SOTIENTOIDA_KM' => 1000000, 'SOTIENTOITHIEU_NHANKM' => 0]);
        $ton = (int) SanPham::query()->findOrFail('SP001')->SOLUONGTON;

        $this->actingAs($this->khach)->post('/gio-hang/SP001', ['so_luong' => 2]);
        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => $km->MA_CODE])->assertSessionHasNoErrors();
        $this->get('/gio-hang')->assertOk()->assertSee($km->MA_CODE);
        $this->get('/thanh-toan')->assertOk();

        $res = $this->post('/thanh-toan', [
            'TEN_NGUOINHAN' => 'Nguyễn Văn A',
            'SDT_NGUOINHAN' => '0901234567',
            'PHUONG_THUC_GH' => 'GiaoHang',
            'DIACHI_GIAOHANG' => '1 Lê Lợi, Q1',
            'PHUONG_THUC' => 'ChuyenKhoan',
        ]);

        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $res->assertRedirect("/don-hang-cua-toi/{$hd->MAHD}");

        $this->assertSame(1000000.0, (float) $hd->TONGTIEN_GIAM); // 10% của 56tr, giới hạn 1tr
        $this->assertSame(55000000.0, (float) $hd->TONGTIEN_HD);
        $this->assertSame($ton - 2, (int) SanPham::query()->findOrFail('SP001')->SOLUONGTON);
        $this->assertSame(1, (int) $km->refresh()->DA_SUDUNG);
        $this->assertDatabaseHas('THANHTOAN', ['MAHD' => $hd->MAHD, 'PHUONG_THUC' => 'ChuyenKhoan', 'TRANGTHAI' => 'ChoThanhToan']);

        $this->get("/don-hang-cua-toi/{$hd->MAHD}")->assertOk()->assertSee("TT {$hd->MAHD}");
        $this->get('/don-hang-cua-toi')->assertOk()->assertSee($hd->MAHD);
        $this->get('/gio-hang')->assertSee('Giỏ hàng của bạn đang trống');
    }

    public function test_ma_het_han_bi_tu_choi(): void
    {
        $this->actingAs($this->khach)->post('/gio-hang/SP001');

        $this->post('/gio-hang-ma-giam-gia', ['ma_code' => 'KHAITUONG10'])->assertSessionHasErrors('ma_code');
    }

    public function test_huy_don_hoan_lai_ton_kho_va_khong_xem_don_nguoi_khac(): void
    {
        $ton = (int) SanPham::query()->findOrFail('SP001')->SOLUONGTON;

        $this->actingAs($this->khach)->post('/gio-hang/SP001', ['so_luong' => 3]);
        $this->post('/thanh-toan', [
            'TEN_NGUOINHAN' => 'Khách',
            'SDT_NGUOINHAN' => '0901234567',
            'PHUONG_THUC_GH' => 'TaiQuay',
            'PHUONG_THUC' => 'COD',
        ])->assertSessionHasNoErrors();

        $hd = HoaDon::query()->where('MATK', $this->khach->MATK)->latest('NGAYLAP')->latest('MAHD')->firstOrFail();
        $this->assertSame($ton - 3, (int) SanPham::query()->findOrFail('SP001')->SOLUONGTON);

        $this->actingAs($this->taiKhoan('TK_KH002'))->get("/don-hang-cua-toi/{$hd->MAHD}")->assertNotFound();

        $this->actingAs($this->khach)->patch("/don-hang-cua-toi/{$hd->MAHD}/huy");
        $this->assertSame('DaHuy', $hd->refresh()->TRANGTHAI);
        $this->assertSame($ton, (int) SanPham::query()->findOrFail('SP001')->SOLUONGTON);
    }

    public function test_thanh_toan_giao_tan_noi_bat_buoc_dia_chi(): void
    {
        $this->actingAs($this->khach)->post('/gio-hang/SP001');

        $this->post('/thanh-toan', [
            'TEN_NGUOINHAN' => 'Khách',
            'SDT_NGUOINHAN' => '0901234567',
            'PHUONG_THUC_GH' => 'GiaoHang',
            'PHUONG_THUC' => 'COD',
        ])->assertSessionHasErrors('DIACHI_GIAOHANG');
    }

    public function test_cap_nhat_thong_tin_va_doi_mat_khau(): void
    {
        $this->actingAs($this->khach)->get('/tai-khoan')->assertOk()->assertSee($this->khach->EMAIL_TK);

        $this->put('/tai-khoan', ['TENKH' => 'Tên Mới', 'SDT_KH' => '0911222333', 'DIACHI_KH' => 'Đà Nẵng', 'NGAYSINH' => '2000-01-02', 'GIOITINH' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('KHACHHANG', ['MAKH' => $this->khach->MAKH, 'TENKH' => 'Tên Mới', 'DIACHI_KH' => 'Đà Nẵng']);

        $this->put('/tai-khoan/mat-khau', ['mat_khau_cu' => 'sai-mat-khau', 'password' => 'moi-123456', 'password_confirmation' => 'moi-123456'])
            ->assertSessionHasErrorsIn('doiMatKhau', 'mat_khau_cu');

        $this->put('/tai-khoan/mat-khau', ['mat_khau_cu' => self::MAT_KHAU_TEST, 'password' => 'moi-123456', 'password_confirmation' => 'moi-123456'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($this->khach->refresh()->kiemTraMatKhau('moi-123456'));
    }

    public function test_chatbot_tra_loi_va_luu_lich_su(): void
    {
        $this->postJson('/chatbot', ['noi_dung' => 'laptop dưới 30 triệu'])
            ->assertOk()
            ->assertJsonStructure(['tra_loi', 'san_pham']);

        $this->assertDatabaseHas('LICHSU_CHATBOT', ['NOI_DUNG' => 'laptop dưới 30 triệu']);
    }

    /**
     * Mã giảm giá còn hạn dùng cho test (dữ liệu mẫu đều đã hết hạn).
     *
     * @param  array<string, mixed>  $thuocTinh
     */
    private function maConHan(array $thuocTinh): KhuyenMai
    {
        return KhuyenMai::query()->create([
            'MAKM' => 'KMTEST',
            'TENKM' => 'Mã test',
            'MA_CODE' => 'TEST10',
            'SOLUONG_MA' => 10,
            'DA_SUDUNG' => 0,
            'NGAYBD' => now()->subDay(),
            'NGAYKT' => now()->addDay(),
            'TRANGTHAI' => 'HoatDong',
            ...$thuocTinh,
        ]);
    }
}
