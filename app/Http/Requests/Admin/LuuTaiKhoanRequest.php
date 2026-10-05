<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuuTaiKhoanRequest extends FormRequest
{
    use ThongBaoTiengViet;

    /**
     * Quyền đã được middleware "vaitro:Admin" kiểm tra.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Tạo mới: chọn loại và gắn với nhân viên / khách hàng chưa có tài khoản, mật khẩu bắt buộc.
     * Sửa: đổi email, trạng thái; mật khẩu để trống là giữ nguyên.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taiKhoan = $this->route('taiKhoan');
        $laTao = $taiKhoan === null;

        return [
            'LOAI_TAIKHOAN' => [$laTao ? 'required' : 'prohibited', 'in:Admin,NhanVien,KhachHang'],
            'MANV' => [$laTao ? 'required_if:LOAI_TAIKHOAN,Admin,NhanVien' : 'prohibited', 'nullable', 'exists:NHANVIEN,MANV',
                Rule::unique('TAIKHOAN', 'MANV')],
            'MAKH' => [$laTao ? 'required_if:LOAI_TAIKHOAN,KhachHang' : 'prohibited', 'nullable', 'exists:KHACHHANG,MAKH',
                Rule::unique('TAIKHOAN', 'MAKH')],
            'EMAIL_TK' => ['required', 'email', 'max:100', Rule::unique('TAIKHOAN', 'EMAIL_TK')->ignore($taiKhoan?->MATK, 'MATK')],
            'password' => [$laTao ? 'required' : 'nullable', 'string', 'min:6', 'max:72', 'confirmed'],
            'TRANGTHAI' => ['required', 'in:HoatDong,KhoaTamThoi,KhoaVinhVien'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'LOAI_TAIKHOAN' => 'loại tài khoản',
            'MANV' => 'nhân viên',
            'MAKH' => 'khách hàng',
            'EMAIL_TK' => 'email đăng nhập',
            'password' => 'mật khẩu',
            'TRANGTHAI' => 'trạng thái',
        ];
    }

    /**
     * Thông báo riêng cho trường hợp người đã có tài khoản.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'MANV.unique' => 'Nhân viên này đã có tài khoản.',
            'MAKH.unique' => 'Khách hàng này đã có tài khoản.',
        ];
    }
}
