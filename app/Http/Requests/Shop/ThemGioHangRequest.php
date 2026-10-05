<?php

namespace App\Http\Requests\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ThemGioHangRequest extends FormRequest
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
            'so_luong' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'so_luong.min' => 'Số lượng tối thiểu là 1.',
            'so_luong.max' => 'Mỗi lần chỉ thêm tối đa 99 sản phẩm.',
        ];
    }

    /**
     * Mặc định thêm 1 sản phẩm khi form không gửi số lượng.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(['so_luong' => 1]);
    }
}
