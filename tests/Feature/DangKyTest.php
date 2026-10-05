<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DangKyTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function duLieu(string $email): array
    {
        return [
            'hoten' => 'Khách Test',
            'email' => $email,
            'sdt' => '0900000000',
            'password' => 'matkhau-moi-1',
            'password_confirmation' => 'matkhau-moi-1',
            'terms' => '1',
        ];
    }

    public function test_dang_ky_tao_khach_hang_va_tai_khoan_ma_hoa_bcrypt(): void
    {
        $this->post('/dang-ky', $this->duLieu('khach.test@example.test'))
            ->assertRedirect('/dang-ky')
            ->assertSessionHas('dang_ky_email', 'khach.test@example.test');

        $taiKhoan = TaiKhoan::query()->where('EMAIL_TK', 'khach.test@example.test')->firstOrFail();

        $this->assertSame('KhachHang', $taiKhoan->LOAI_TAIKHOAN);
        $this->assertStringStartsWith('$2y$', $taiKhoan->MATKHAU);
        $this->assertTrue(Hash::check('matkhau-moi-1', $taiKhoan->MATKHAU));
        $this->assertSame('Khách Test', KhachHang::query()->findOrFail($taiKhoan->MAKH)->TENKH);
    }

    public function test_hai_lan_dang_ky_lien_tiep_khong_trung_ma(): void
    {
        $this->post('/dang-ky', $this->duLieu('khach.mot@example.test'));
        $this->post('/dang-ky', $this->duLieu('khach.hai@example.test'));

        $ma = TaiKhoan::query()->whereIn('EMAIL_TK', ['khach.mot@example.test', 'khach.hai@example.test'])->pluck('MAKH');

        $this->assertCount(2, $ma);
        $this->assertCount(2, $ma->unique());
    }

    public function test_email_da_dung_bi_tu_choi(): void
    {
        $this->post('/dang-ky', $this->duLieu('nguyenvana@gmail.com'))
            ->assertSessionHasErrors(['email' => 'Email này đã được sử dụng. Vui lòng dùng email khác!']);
    }

    public function test_mat_khau_xac_nhan_khong_khop(): void
    {
        $this->post('/dang-ky', ['password_confirmation' => 'khac'] + $this->duLieu('khach.ba@example.test'))
            ->assertSessionHasErrors('password');
    }
}
