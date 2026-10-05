<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DangNhapTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function taiKhoanVaoDuoc(): array
    {
        return [
            'admin' => ['TK_ADMIN1', '/admin'],
            'quản lý bán hàng (CV002)' => ['TK_NV001', '/ban-hang'],
            'quản lý kho (CV003)' => ['TK_NV002', '/kho'],
            'nhân viên giao hàng (CV007)' => ['TK_NV006', '/kho'],
        ];
    }

    #[DataProvider('taiKhoanVaoDuoc')]
    public function test_dang_nhap_chuyen_toi_trang_theo_vai_tro(string $maTaiKhoan, string $trang): void
    {
        $taiKhoan = $this->taiKhoan($maTaiKhoan);

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertRedirect($trang);

        $this->assertAuthenticatedAs($taiKhoan);
    }

    public function test_hash_2a_tu_cong_cu_khac_van_dang_nhap_duoc_va_duoc_ma_hoa_lai(): void
    {
        $taiKhoan = $this->taiKhoan('TK_ADMIN1');
        $hash2a = '$2a$'.substr(password_hash(self::MAT_KHAU_TEST, PASSWORD_BCRYPT, ['cost' => 4]), 4);
        // Ghi thẳng vào bảng: cast "hashed" của model sẽ băm lại chuỗi $2a$
        DB::table('TAIKHOAN')->where('MATK', 'TK_ADMIN1')->update(['MATKHAU' => $hash2a]);

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertRedirect('/admin');

        $this->assertAuthenticated();
        $this->assertStringStartsWith('$2y$', $taiKhoan->refresh()->MATKHAU);
    }

    public function test_sai_mat_khau_bi_tu_choi(): void
    {
        $taiKhoan = $this->taiKhoan('TK_ADMIN1');

        $this->from('/dang-nhap')
            ->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => 'sai-mat-khau'])
            ->assertRedirect('/dang-nhap')
            ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng!']);

        $this->assertGuest();
    }

    public function test_chuc_vu_chua_cap_quyen_khong_vao_duoc(): void
    {
        $taiKhoan = $this->taiKhoan('TK_NV005'); // Kế toán (CV006)

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_khach_hang_dang_nhap_ve_cua_hang_va_khong_vao_trang_quan_ly(): void
    {
        $taiKhoan = $this->taiKhoan('TK_KH001');

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($taiKhoan);
        $this->get('/admin')->assertRedirect('/');
    }

    public function test_khach_hang_dang_nhap_quay_lai_trang_dang_xem(): void
    {
        $taiKhoan = $this->taiKhoan('TK_KH001');

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST, 'tiep' => '/san-pham/SP001'])
            ->assertRedirect('/san-pham/SP001');

        $this->post('/dang-xuat');
        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST, 'tiep' => '//evil.example'])
            ->assertRedirect('/');
    }

    public function test_tai_khoan_bi_khoa_khong_vao_duoc(): void
    {
        $taiKhoan = $this->taiKhoan('TK_ADMIN1');
        $taiKhoan->update(['TRANGTHAI' => 'KhoaTamThoi']);

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sai_qua_nhieu_lan_bi_tam_chan(): void
    {
        $taiKhoan = $this->taiKhoan('TK_ADMIN1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => 'sai']);
        }

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dang_xuat(): void
    {
        $this->actingAs($this->taiKhoan('TK_ADMIN1'))
            ->post('/dang-xuat')
            ->assertRedirect('/dang-nhap');

        $this->assertGuest();
    }

    public function test_trang_dang_nhap_va_dang_ky_dung_khung_cua_hang(): void
    {
        $this->get('/dang-nhap?tiep=/san-pham/SP001')
            ->assertOk()
            ->assertSee('Bạn cần tìm laptop, PC, linh kiện...?')
            ->assertSee('name="tiep" value="/san-pham/SP001"', false);

        $this->get('/dang-ky')->assertOk()->assertSee('Bạn cần tìm laptop, PC, linh kiện...?')->assertSee('Tạo tài khoản');
    }

    public function test_sai_mat_khau_chi_bao_loi_mot_lan_duoi_o_email(): void
    {
        $taiKhoan = $this->taiKhoan('TK_KH001');

        $this->from('/dang-nhap')->followingRedirects()
            ->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => 'sai-mat-khau'])
            ->assertSee('class="s-err"', false)
            ->assertDontSee('s-flash err', false);
    }
}
