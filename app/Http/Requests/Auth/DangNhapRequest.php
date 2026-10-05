<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DangNhapRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập đầy đủ thông tin!',
            'email.email' => 'Email không hợp lệ!',
            'password.required' => 'Vui lòng nhập đầy đủ thông tin!',
        ];
    }

    /**
     * Khoá giới hạn số lần thử: theo email và địa chỉ IP.
     */
    public function throttleKey(): string
    {
        return mb_strtolower((string) $this->string('email')).'|'.$this->ip();
    }
}
