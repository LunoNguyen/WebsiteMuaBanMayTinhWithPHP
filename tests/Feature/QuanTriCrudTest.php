<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use App\Models\KhuyenMai;
use App\Models\NhanVien;
use App\Models\PhieuNhapHang;
use App\Models\SanPham;
use App\Models\TaiKhoan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuanTriCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->taiKhoan('TK_ADMIN1'));
    }

    public function test_moi_trang_quan_tri_mo_duoc_khong_con_404(): void
    {
        $trang = [
            '/admin', '/admin/nhan-vien', '/admin/nhan-vien/them', '/admin/nhan-vien/NV002/sua',
            '/admin/tai-khoan', '/admin/tai-khoan/them', '/admin/tai-khoan/TK_NV001/sua',
            '/admin/san-pham', '/admin/san-pham/them', '/admin/san-pham/SP001/sua',
            '/admin/nhap-hang', '/admin/nhap-hang/them', '/admin/nhap-hang/PNH001',
            '/admin/khuyen-mai', '/admin/khuyen-mai/them', '/admin/khuyen-mai/KM001/sua',
            '/admin/don-hang', '/admin/don-hang/them', '/admin/don-hang/HD001', '/admin/don-hang?makh=KH001',
            '/admin/khach-hang', '/admin/khach-hang/them', '/admin/khach-hang/KH001', '/admin/khach-hang/KH001/sua',
            '/admin/danh-muc', '/admin/danh-muc?bang=ncc', '/admin/danh-muc?bang=chucvu&sua=CV001',
            '/admin/ho-so', '/admin/chatbot', '/admin/bao-cao',
            '/kho', '/kho/don-hang', '/kho/don-hang/HD001',
            '/ban-hang', '/ban-hang/don-hang/HD001', '/ban-hang/khach-hang', '/ban-hang/khach-hang/KH001',
        ];

        foreach ($trang as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_them_sua_san_pham_ghi_lich_su_gia(): void
    {
        $this->post('/admin/san-pham', [
            'TENSP' => 'Laptop Test 14', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX001', 'DONVT' => 'Cái',
            'DONGIA_SP' => 20000000, 'SOLUONGTON' => 5, 'TRANGTHAI' => 'DangBan', 'CPU' => 'Core i5', 'RAM' => '16GB',
        ])->assertSessionHasNoErrors();

        $sp = SanPham::query()->where('TENSP', 'Laptop Test 14')->firstOrFail();
        $this->assertSame('Core i5', $sp->moTa->CPU);

        $this->put("/admin/san-pham/{$sp->MASP}", [
            'TENSP' => 'Laptop Test 14 (2026)', 'MALOAI' => 'LSP001', 'MANSX' => 'NSX001',
            'DONGIA_SP' => 18500000, 'TRANGTHAI' => 'DangBan', 'CPU' => 'Core i7', 'GHI_CHU_GIA' => 'Giảm giá',
        ])->assertRedirect('/admin/san-pham');

        $this->assertSame(5, (int) $sp->refresh()->SOLUONGTON, 'Sửa sản phẩm không được đổi tồn kho');
        $this->assertSame('Core i7', $sp->moTa->refresh()->CPU);
        $this->assertDatabaseHas('LICHSUGIA', ['MASP' => $sp->MASP, 'DONGIA_CU' => 20000000, 'DONGIA_MOI' => 18500000, 'MANV_CAPNHAT' => 'NV001']);
    }

    public function test_form_san_pham_tai_anh_dat_anh_chinh_va_xoa_anh(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $sp = SanPham::query()->findOrFail('SP005');
        $sp->anhs()->delete();
        $truong = ['TENSP' => $sp->TENSP, 'MALOAI' => $sp->MALOAI, 'MANSX' => $sp->MANSX, 'DONGIA_SP' => (int) $sp->DONGIA_SP, 'TRANGTHAI' => $sp->TRANGTHAI];

        $this->get("/admin/san-pham/{$sp->MASP}/sua")->assertOk()->assertSee('Chọn ảnh');

        $this->put("/admin/san-pham/{$sp->MASP}", [...$truong, 'anh' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')]])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/san-pham');

        $anhs = $sp->anhs()->orderBy('THU_TU')->get();
        $this->assertCount(2, $anhs);
        $this->assertTrue($anhs[0]->LA_ANH_CHINH, 'Ảnh đầu tiên thành ảnh chính');
        $this->assertFalse($anhs[1]->LA_ANH_CHINH);
        $this->assertStringStartsWith("sanpham/{$sp->MASP}/", $anhs[0]->URL_ANH);

        $this->patch("/admin/san-pham/{$sp->MASP}/anh/{$anhs[1]->MAANH}/chinh");
        $this->assertTrue($anhs[1]->refresh()->LA_ANH_CHINH);
        $this->assertFalse($anhs[0]->refresh()->LA_ANH_CHINH);

        $this->delete("/admin/san-pham/{$sp->MASP}/anh/{$anhs[1]->MAANH}");
        $this->assertModelMissing($anhs[1]);
        $this->assertTrue($anhs[0]->refresh()->LA_ANH_CHINH, 'Xoá ảnh chính thì ảnh còn lại thành ảnh chính');

        $this->put("/admin/san-pham/{$sp->MASP}", [...$truong, 'anh' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]])
            ->assertSessionHasErrors('anh.0');

        // Ảnh của sản phẩm khác không thao tác được qua sản phẩm này
        $this->delete("/admin/san-pham/SP001/anh/{$anhs[0]->MAANH}")->assertNotFound();
    }

    public function test_minio_loi_thi_bao_va_khong_ghi_anh(): void
    {
        Http::fake(['*' => Http::response('', 500)]);
        $sp = SanPham::query()->findOrFail('SP005');
        $sp->anhs()->delete();

        $this->put("/admin/san-pham/{$sp->MASP}", [
            'TENSP' => $sp->TENSP, 'MALOAI' => $sp->MALOAI, 'MANSX' => $sp->MANSX, 'DONGIA_SP' => (int) $sp->DONGIA_SP, 'TRANGTHAI' => $sp->TRANGTHAI,
            'anh' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertRedirect("/admin/san-pham/{$sp->MASP}/sua")->assertSessionHas('loai', 'info');

        $this->assertSame(0, $sp->anhs()->count());
    }

    public function test_them_sua_xoa_nhan_vien(): void
    {
        $this->post('/admin/nhan-vien', ['TENNV' => 'Nhân Viên Mới', 'MACV' => 'CV004', 'TRANGTHAI' => '1'])->assertSessionHasNoErrors();
        $nv = NhanVien::query()->where('TENNV', 'Nhân Viên Mới')->firstOrFail();
        $this->assertMatchesRegularExpression('/^NV\d{3}$/', $nv->MANV);

        $this->put("/admin/nhan-vien/{$nv->MANV}", ['TENNV' => 'Đã Đổi Tên', 'MACV' => 'CV003', 'TRANGTHAI' => '0'])->assertSessionHasNoErrors();
        $this->assertSame('CV003', $nv->refresh()->MACV);

        $this->delete("/admin/nhan-vien/{$nv->MANV}");
        $this->assertModelMissing($nv);

        $this->delete('/admin/nhan-vien/NV002')->assertSessionHas('loai', 'danger');
        $this->assertDatabaseHas('NHANVIEN', ['MANV' => 'NV002']);
    }

    public function test_tao_tai_khoan_cho_khach_va_dang_nhap_duoc(): void
    {
        $kh = KhachHang::query()->create(['MAKH' => 'KHTEST', 'TENKH' => 'Khách Test']);

        $this->post('/admin/tai-khoan', [
            'LOAI_TAIKHOAN' => 'KhachHang', 'MAKH' => 'KHTEST', 'EMAIL_TK' => 'khachtest@example.com',
            'password' => 'matkhau123', 'password_confirmation' => 'matkhau123', 'TRANGTHAI' => 'HoatDong',
        ])->assertSessionHasNoErrors();

        $tk = TaiKhoan::query()->where('EMAIL_TK', 'khachtest@example.com')->firstOrFail();
        $this->assertSame($kh->MAKH, $tk->MAKH);
        $this->assertTrue($tk->kiemTraMatKhau('matkhau123'));

        $this->post('/admin/tai-khoan', [
            'LOAI_TAIKHOAN' => 'KhachHang', 'MAKH' => 'KHTEST', 'EMAIL_TK' => 'khac@example.com',
            'password' => 'matkhau123', 'password_confirmation' => 'matkhau123', 'TRANGTHAI' => 'HoatDong',
        ])->assertSessionHasErrors('MAKH');

        $this->put("/admin/tai-khoan/{$tk->MATK}", ['EMAIL_TK' => 'khachtest@example.com', 'TRANGTHAI' => 'KhoaTamThoi', 'password' => ''])
            ->assertSessionHasNoErrors();
        $this->assertSame('KhoaTamThoi', $tk->refresh()->TRANGTHAI);
        $this->assertTrue($tk->kiemTraMatKhau('matkhau123'), 'Để trống mật khẩu thì giữ nguyên');

        $this->delete("/admin/tai-khoan/{$tk->MATK}");
        $this->assertModelMissing($tk);
    }

    public function test_khong_tu_khoa_hay_xoa_tai_khoan_dang_dung(): void
    {
        $this->put('/admin/tai-khoan/TK_ADMIN1', ['EMAIL_TK' => TaiKhoan::query()->find('TK_ADMIN1')->EMAIL_TK, 'TRANGTHAI' => 'KhoaTamThoi']);
        $this->delete('/admin/tai-khoan/TK_ADMIN1');

        $this->assertDatabaseHas('TAIKHOAN', ['MATK' => 'TK_ADMIN1', 'TRANGTHAI' => 'HoatDong']);
    }

    public function test_khach_hang_crud(): void
    {
        $this->post('/admin/khach-hang', ['TENKH' => 'Khách Quầy', 'SDT_KH' => '0912345678'])->assertSessionHasNoErrors();
        $kh = KhachHang::query()->where('TENKH', 'Khách Quầy')->firstOrFail();

        $this->put("/admin/khach-hang/{$kh->MAKH}", ['TENKH' => 'Khách Quầy 2', 'GIOITINH' => '0'])->assertRedirect("/admin/khach-hang/{$kh->MAKH}");
        $this->assertSame('Khách Quầy 2', $kh->refresh()->TENKH);

        $this->delete("/admin/khach-hang/{$kh->MAKH}")->assertRedirect('/admin/khach-hang');
        $this->assertModelMissing($kh);

        $this->delete('/admin/khach-hang/KH001');
        $this->assertDatabaseHas('KHACHHANG', ['MAKH' => 'KH001']);
    }

    public function test_khuyen_mai_bat_buoc_co_muc_giam_toi_da_va_dieu_kien(): void
    {
        $coBan = ['TENKM' => 'Sale', 'MA_CODE' => 'sale-he', 'LOAI_KM' => 'PhanTram', 'GIATRI_KM' => 10,
            'NGAYBD' => now()->format('Y-m-d\TH:i'), 'NGAYKT' => now()->addDays(7)->format('Y-m-d\TH:i'), 'TRANGTHAI' => 'HoatDong'];

        $this->post('/admin/khuyen-mai', $coBan)->assertSessionHasErrors(['SOTIENTOIDA_KM', 'SOTIENTOITHIEU_NHANKM']);
        $this->post('/admin/khuyen-mai', [...$coBan, 'GIATRI_KM' => 120, 'SOTIENTOIDA_KM' => 500000, 'SOTIENTOITHIEU_NHANKM' => 0])
            ->assertSessionHasErrors('GIATRI_KM');

        $this->post('/admin/khuyen-mai', [...$coBan, 'SOTIENTOIDA_KM' => 500000, 'SOTIENTOITHIEU_NHANKM' => 2000000, 'san_pham' => ['SP001', 'SP002']])
            ->assertSessionHasNoErrors();

        $km = KhuyenMai::query()->where('MA_CODE', 'SALE-HE')->firstOrFail();
        $this->assertEqualsCanonicalizing(['SP001', 'SP002'], $km->sanPhams()->pluck('SANPHAM.MASP')->all());

        $this->put("/admin/khuyen-mai/{$km->MAKM}", [...$coBan, 'MA_CODE' => 'SALE-HE', 'LOAI_KM' => 'SoTienCoDinh', 'GIATRI_KM' => 300000, 'SOTIENTOITHIEU_NHANKM' => 1000000])
            ->assertSessionHasNoErrors();
        $km->refresh();
        $this->assertSame(300000.0, (float) $km->SOTIENTOIDA_KM, 'Giảm cố định thì tối đa bằng chính số tiền giảm');
        $this->assertSame(0, $km->sanPhams()->count());
    }

    public function test_phieu_nhap_tao_duyet_va_kho_hoan_tat_cong_ton(): void
    {
        $ton = (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON;

        $this->post('/admin/nhap-hang', [
            'MANCC' => 'NCC001', 'NGAY_DATMUA' => now()->format('Y-m-d\TH:i'), 'THUE_VAT' => 10, 'CHIETKHAU' => 5,
            'TRANGTHAI_THANHTOAN' => 'ChuaThanhToan',
            'dong' => [['MASP' => 'SP003', 'SOLUONG' => 4, 'DONGIA_NHAP' => 1000000], ['MASP' => '', 'SOLUONG' => 1, 'DONGIA_NHAP' => '']],
        ])->assertSessionHasNoErrors();

        $pn = PhieuNhapHang::query()->where('MANV', 'NV001')->latest('NGAYTAO')->latest('MAPNH')->firstOrFail();
        $this->assertSame('ChoDuyet', $pn->TRANGTHAI);
        $this->assertSame(4180000.0, (float) $pn->TONGCONG_PNH); // 4tr - 5% + 10% VAT
        $this->assertSame($ton, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON, 'Tạo phiếu chưa cộng tồn kho');

        $this->patch("/admin/nhap-hang/{$pn->MAPNH}/duyet");
        $this->assertSame('DaDuyet', $pn->refresh()->TRANGTHAI);

        $this->postJson("/kho/phieu-nhap/{$pn->MAPNH}/hoan-tat")->assertOk();
        $this->assertSame($ton + 4, (int) SanPham::query()->findOrFail('SP003')->SOLUONGTON);

        $this->get("/admin/nhap-hang/{$pn->MAPNH}/sua")->assertRedirect("/admin/nhap-hang/{$pn->MAPNH}");
        $this->delete("/admin/nhap-hang/{$pn->MAPNH}");
        $this->assertDatabaseHas('PHIEUNHAPHANG', ['MAPNH' => $pn->MAPNH]);
    }

    public function test_danh_muc_them_sua_va_khong_xoa_khi_dang_dung(): void
    {
        $this->post('/admin/danh-muc/loai', ['TENLOAI' => 'Loa'])->assertSessionHasNoErrors();
        $ma = DB::table('LOAISANPHAM')->where('TENLOAI', 'Loa')->value('MALOAI');
        $this->assertNotNull($ma);

        $this->put("/admin/danh-muc/loai/{$ma}", ['TENLOAI' => 'Loa Bluetooth']);
        $this->assertDatabaseHas('LOAISANPHAM', ['MALOAI' => $ma, 'TENLOAI' => 'Loa Bluetooth']);

        $this->delete("/admin/danh-muc/loai/{$ma}");
        $this->assertDatabaseMissing('LOAISANPHAM', ['MALOAI' => $ma]);

        $this->delete('/admin/danh-muc/loai/LSP001')->assertSessionHas('loai', 'danger');
        $this->assertDatabaseHas('LOAISANPHAM', ['MALOAI' => 'LSP001']);

        $this->post('/admin/danh-muc/khong-co', ['X' => 1])->assertNotFound();
    }

    public function test_ho_so_cap_nhat_thong_tin(): void
    {
        $this->put('/admin/ho-so', ['TENNV' => 'Quản Trị Viên', 'SDT_NV' => '0900000000'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('NHANVIEN', ['MANV' => 'NV001', 'TENNV' => 'Quản Trị Viên']);
    }

    public function test_nhan_vien_ban_hang_khong_vao_crud_quan_tri(): void
    {
        $this->actingAs($this->taiKhoan('TK_NV001'))->get('/admin/san-pham/them')->assertRedirect();
    }
}
