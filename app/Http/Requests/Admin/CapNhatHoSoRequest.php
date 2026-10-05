<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CapNhatHoSoRequest extends FormRequest
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
     * Thông tin cá nhân của nhân viên đang đăng nhập (chức vụ, trạng thái do quản lý đổi ở mục Nhân viên).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TENNV' => ['required', 'string', 'max:100'],
            'SDT_NV' => ['nullable', 'string', 'regex:/^[0-9 +.]{9,15}$/'],
            'DIACHI_NV' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TENNV' => 'họ tên',
            'SDT_NV' => 'số điện thoại',
            'DIACHI_NV' => 'địa chỉ',
        ];
    }
}
