<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaiAnhSanPhamRequest extends FormRequest
{
    /**
     * Quyền đã được middleware "vaitro:Admin" kiểm tra.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ảnh JPG/PNG/WEBP/GIF, kiểm tra theo nội dung file, tối đa theo config/minio.php.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'anh' => ['required', 'file', 'mimes:'.implode(',', config('minio.mimes')), 'max:'.config('minio.max_kb')],
            'chinh' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'anh.required' => 'Chưa chọn ảnh.',
            'anh.mimes' => 'Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF.',
            'anh.max' => 'File quá lớn (tối đa '.(config('minio.max_kb') / 1024).' MB).',
        ];
    }
}
