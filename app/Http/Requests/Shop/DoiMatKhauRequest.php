<?php

namespace App\Http\Requests\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DoiMatKhauRequest extends FormRequest
{
    /**
     * Lỗi đổi mật khẩu tách riêng để không lẫn với form thông tin cá nhân.
     *
     * @var string
     */
    protected $errorBag = 'doiMatKhau';

    /**
     * Quyền đã được middleware "vaitro:KhachHang" kiểm tra.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * bcrypt chỉ dùng 72 byte đầu của mật khẩu, nên giới hạn 72 ký tự.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mat_khau_cu' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed', 'different:mat_khau_cu'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mat_khau_cu.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.min' => 'Mật khẩu mới phải ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
