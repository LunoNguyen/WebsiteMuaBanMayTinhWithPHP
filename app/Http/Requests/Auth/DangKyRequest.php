<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DangKyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * bcrypt chỉ dùng 72 byte đầu của mật khẩu, nên chặn mật khẩu dài hơn.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hoten' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:TAIKHOAN,EMAIL_TK'],
            'sdt' => ['nullable', 'string', 'max:15'],
            'password' => ['required', 'string', 'min:6', 'confirmed', function (string $attribute, mixed $value, \Closure $fail): void {
                if (strlen((string) $value) > 72) {
                    $fail('Mật khẩu quá dài (tối đa 72 byte)!');
                }
            }],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hoten.required' => 'Vui lòng điền đầy đủ thông tin bắt buộc!',
            'email.required' => 'Vui lòng điền đầy đủ thông tin bắt buộc!',
            'password.required' => 'Vui lòng điền đầy đủ thông tin bắt buộc!',
            'email.email' => 'Email không hợp lệ!',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng dùng email khác!',
            'password.min' => 'Mật khẩu phải ít nhất 6 ký tự!',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp!',
            'terms.accepted' => 'Vui lòng đồng ý với điều khoản dịch vụ!',
        ];
    }
}
