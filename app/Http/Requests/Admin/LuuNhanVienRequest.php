<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuuNhanVienRequest extends FormRequest
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
            'TENNV' => ['required', 'string', 'max:100'],
            'MACV' => ['required', 'exists:CHUCVU,MACV'],
            'SDT_NV' => ['nullable', 'string', 'regex:/^[0-9 +.]{9,15}$/'],
            'EMAIL_NV' => ['nullable', 'email', 'max:100',
                Rule::unique('NHANVIEN', 'EMAIL_NV')->ignore($this->route('nhanVien')?->MANV, 'MANV')],
            'DIACHI_NV' => ['nullable', 'string', 'max:200'],
            'NGAYVAOLAM' => ['nullable', 'date', 'before_or_equal:today'],
            'TRANGTHAI' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TENNV' => 'họ tên',
            'MACV' => 'chức vụ',
            'SDT_NV' => 'số điện thoại',
            'EMAIL_NV' => 'email',
            'DIACHI_NV' => 'địa chỉ',
            'NGAYVAOLAM' => 'ngày vào làm',
            'TRANGTHAI' => 'trạng thái',
        ];
    }
}
