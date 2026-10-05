<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuuKhachHangRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TENKH' => ['required', 'string', 'max:100'],
            'SDT_KH' => ['nullable', 'string', 'regex:/^[0-9 +.]{9,15}$/'],
            'EMAIL_KH' => ['nullable', 'email', 'max:100',
                Rule::unique('KHACHHANG', 'EMAIL_KH')->ignore($this->route('khachHang')?->MAKH, 'MAKH')],
            'DIACHI_KH' => ['nullable', 'string', 'max:200'],
            'NGAYSINH' => ['nullable', 'date', 'before:today'],
            'GIOITINH' => ['nullable', 'in:0,1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TENKH' => 'họ tên',
            'SDT_KH' => 'số điện thoại',
            'EMAIL_KH' => 'email',
            'DIACHI_KH' => 'địa chỉ',
            'NGAYSINH' => 'ngày sinh',
            'GIOITINH' => 'giới tính',
        ];
    }
}
