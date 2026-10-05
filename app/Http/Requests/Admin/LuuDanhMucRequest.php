<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\DanhMucController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LuuDanhMucRequest extends FormRequest
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
     * Luật lấy theo bảng danh mục đang sửa (khai trong DanhMucController::BANG).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return collect(DanhMucController::BANG[$this->route('bang')]['truong'] ?? [])
            ->map(fn (array $truong): array => $truong['luat'])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(DanhMucController::BANG[$this->route('bang')]['truong'] ?? [])
            ->map(fn (array $truong): string => mb_strtolower($truong['nhan']))
            ->all();
    }
}
