<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaoDonHangRequest extends FormRequest
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
        $this->merge([
            'san_pham' => collect((array) $this->input('san_pham', []))
                ->filter(fn ($d): bool => is_array($d) && filled($d['MASP'] ?? null))
                ->values()
                ->all(),
            'MA_CODE' => $this->filled('MA_CODE') ? strtoupper(trim((string) $this->input('MA_CODE'))) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'MAKH' => ['nullable', 'exists:KHACHHANG,MAKH'],
            'TEN_NGUOINHAN' => ['required', 'string', 'max:100'],
            'SDT_NGUOINHAN' => ['required', 'string', 'regex:/^[0-9 +.]{9,15}$/'],
            'PHUONG_THUC_GH' => ['required', 'in:GiaoHang,TaiQuay'],
            'DIACHI_GIAOHANG' => ['required_if:PHUONG_THUC_GH,GiaoHang', 'nullable', 'string', 'max:300'],
            'PHUONG_THUC' => ['required', 'in:COD,ChuyenKhoan,QR'],
            'GHI_CHU' => ['nullable', 'string', 'max:500'],
            'MA_CODE' => ['nullable', 'string', 'max:50'],
            'san_pham' => ['required', 'array', 'min:1', 'max:50'],
            'san_pham.*.MASP' => ['required', 'distinct', 'exists:SANPHAM,MASP'],
            'san_pham.*.SOLUONG' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'MAKH' => 'khách hàng',
            'TEN_NGUOINHAN' => 'tên người nhận',
            'SDT_NGUOINHAN' => 'số điện thoại',
            'PHUONG_THUC_GH' => 'hình thức nhận hàng',
            'DIACHI_GIAOHANG' => 'địa chỉ giao hàng',
            'PHUONG_THUC' => 'phương thức thanh toán',
            'GHI_CHU' => 'ghi chú',
            'MA_CODE' => 'mã giảm giá',
            'san_pham' => 'sản phẩm',
            'san_pham.*.MASP' => 'sản phẩm',
            'san_pham.*.SOLUONG' => 'số lượng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'san_pham.required' => 'Đơn hàng cần ít nhất một sản phẩm.',
            'san_pham.*.MASP.distinct' => 'Một sản phẩm chỉ chọn một dòng, hãy tăng số lượng.',
        ];
    }
}
