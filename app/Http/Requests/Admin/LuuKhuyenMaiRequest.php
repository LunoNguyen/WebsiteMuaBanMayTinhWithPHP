<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuuKhuyenMaiRequest extends FormRequest
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
     * Mã viết hoa, không dấu cách, trước khi kiểm tra trùng.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['MA_CODE' => strtoupper(trim((string) $this->input('MA_CODE')))]);
    }

    /**
     * Mọi mã phải có điều kiện (đơn tối thiểu) và mức giảm tối đa; giảm theo % thì từ 1 đến 100.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $khuyenMai = $this->route('khuyenMai');

        return [
            'TENKM' => ['required', 'string', 'max:200'],
            'MA_CODE' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('KHUYENMAI', 'MA_CODE')->ignore($khuyenMai?->MAKM, 'MAKM')],
            'LOAI_KM' => ['required', 'in:PhanTram,SoTienCoDinh'],
            'GIATRI_KM' => ['required', 'numeric', 'min:1', $this->input('LOAI_KM') === 'PhanTram' ? 'max:100' : 'max:9999999999'],
            'SOTIENTOIDA_KM' => ['required_if:LOAI_KM,PhanTram', 'nullable', 'numeric', 'min:1000', 'max:9999999999'],
            'SOTIENTOITHIEU_NHANKM' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'SOLUONG_MA' => ['nullable', 'integer', 'min:'.max(1, (int) $khuyenMai?->DA_SUDUNG), 'max:1000000'],
            'NGAYBD' => ['required', 'date'],
            'NGAYKT' => ['required', 'date', 'after_or_equal:NGAYBD'],
            'TRANGTHAI' => ['required', 'in:HoatDong,TamDung,HetHan'],
            'san_pham' => ['nullable', 'array'],
            'san_pham.*' => ['distinct', 'exists:SANPHAM,MASP'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TENKM' => 'tên chương trình',
            'MA_CODE' => 'mã code',
            'LOAI_KM' => 'loại giảm',
            'GIATRI_KM' => 'giá trị giảm',
            'SOTIENTOIDA_KM' => 'mức giảm tối đa',
            'SOTIENTOITHIEU_NHANKM' => 'giá trị đơn tối thiểu',
            'SOLUONG_MA' => 'số lượt dùng',
            'NGAYBD' => 'ngày bắt đầu',
            'NGAYKT' => 'ngày kết thúc',
            'TRANGTHAI' => 'trạng thái',
            'san_pham' => 'sản phẩm áp dụng',
            'san_pham.*' => 'sản phẩm áp dụng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'MA_CODE.regex' => 'Mã code chỉ gồm chữ không dấu, số, gạch ngang hoặc gạch dưới.',
            'NGAYKT.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'SOLUONG_MA.min' => 'Số lượt dùng không được nhỏ hơn số lượt đã dùng (:min).',
        ];
    }
}
