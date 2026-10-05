<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LuuPhieuNhapRequest extends FormRequest
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
     * Bỏ các dòng sản phẩm để trống trước khi kiểm tra.
     */
    protected function prepareForValidation(): void
    {
        $dong = collect((array) $this->input('dong', []))
            ->filter(fn ($d): bool => is_array($d) && filled($d['MASP'] ?? null))
            ->values()
            ->all();

        $this->merge(['dong' => $dong]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'MANCC' => ['required', 'exists:NHACUNGCAP,MANCC'],
            'NGAY_DATMUA' => ['required', 'date'],
            'NGAYGIAO' => ['nullable', 'date', 'after_or_equal:NGAY_DATMUA'],
            'THUE_VAT' => ['required', 'numeric', 'min:0', 'max:100'],
            'CHIETKHAU' => ['required', 'numeric', 'min:0', 'max:100'],
            'TRANGTHAI_THANHTOAN' => ['required', 'in:ChuaThanhToan,DaThanhToan,HoanTien'],
            'GHI_CHU' => ['nullable', 'string', 'max:2000'],
            'dong' => ['required', 'array', 'min:1', 'max:100'],
            'dong.*.MASP' => ['required', 'distinct', 'exists:SANPHAM,MASP'],
            'dong.*.SOLUONG' => ['required', 'integer', 'min:1', 'max:100000'],
            'dong.*.DONGIA_NHAP' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'dong.*.GHI_CHU' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'MANCC' => 'nhà cung cấp',
            'NGAY_DATMUA' => 'ngày đặt mua',
            'NGAYGIAO' => 'ngày giao dự kiến',
            'THUE_VAT' => 'thuế VAT',
            'CHIETKHAU' => 'chiết khấu',
            'TRANGTHAI_THANHTOAN' => 'trạng thái thanh toán',
            'GHI_CHU' => 'ghi chú',
            'dong' => 'danh sách sản phẩm',
            'dong.*.MASP' => 'sản phẩm',
            'dong.*.SOLUONG' => 'số lượng',
            'dong.*.DONGIA_NHAP' => 'đơn giá nhập',
            'dong.*.GHI_CHU' => 'ghi chú dòng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'dong.required' => 'Phiếu nhập cần ít nhất một sản phẩm.',
            'dong.*.MASP.distinct' => 'Một sản phẩm chỉ nhập một dòng, hãy gộp số lượng.',
        ];
    }
}
