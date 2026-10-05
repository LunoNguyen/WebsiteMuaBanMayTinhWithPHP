<?php

namespace App\Http\Requests\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CapNhatTaiKhoanRequest extends FormRequest
{
    /**
     * Quyền đã được middleware "vaitro:KhachHang" kiểm tra.
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
            'DIACHI_KH' => ['nullable', 'string', 'max:200'],
            'NGAYSINH' => ['nullable', 'date', 'before:today'],
            'GIOITINH' => ['nullable', 'in:0,1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TENKH.required' => 'Vui lòng nhập họ tên.',
            'SDT_KH.regex' => 'Số điện thoại không hợp lệ.',
            'NGAYSINH.before' => 'Ngày sinh không hợp lệ.',
        ];
    }
}
