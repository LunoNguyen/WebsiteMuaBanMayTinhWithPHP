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

    public function test_tai_khoan_khach_hang_khong_vao_trang_quan_ly(): void
    {
        $taiKhoan = $this->taiKhoan('TK_KH001');

        $this->post('/dang-nhap', ['email' => $taiKhoan->EMAIL_TK, 'password' => self::MAT_KHAU_TEST])
            ->assertSessionHasErrors(['email' => 'Tài khoản khách hàng không đăng nhập được vào trang quản lý.']);

        $this->assertGuest();
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
}
