<?php

namespace App\Http\Requests\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DatHangRequest extends FormRequest
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
            'TEN_NGUOINHAN' => ['required', 'string', 'max:100'],
            'SDT_NGUOINHAN' => ['required', 'string', 'regex:/^[0-9 +.]{9,15}$/'],
            'PHUONG_THUC_GH' => ['required', 'in:GiaoHang,TaiQuay'],
            'DIACHI_GIAOHANG' => ['required_if:PHUONG_THUC_GH,GiaoHang', 'nullable', 'string', 'max:300'],
            'PHUONG_THUC' => ['required', 'in:COD,ChuyenKhoan,QR'],
            'GHI_CHU' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TEN_NGUOINHAN.required' => 'Vui lòng nhập tên người nhận.',
            'SDT_NGUOINHAN.required' => 'Vui lòng nhập số điện thoại.',
            'SDT_NGUOINHAN.regex' => 'Số điện thoại không hợp lệ.',
            'DIACHI_GIAOHANG.required_if' => 'Vui lòng nhập địa chỉ giao hàng.',
            'PHUONG_THUC.in' => 'Phương thức thanh toán không hợp lệ.',
        ];
    }
}
