<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LuuSanPhamRequest extends FormRequest
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
     * Thông tin sản phẩm và cấu hình (bảng MOTA). Tồn kho chỉ nhập lúc tạo, sau đó đi qua phiếu nhập.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TENSP' => ['required', 'string', 'max:200'],
            'MALOAI' => ['required', 'exists:LOAISANPHAM,MALOAI'],
            'MANSX' => ['required', 'exists:NHASANXUA,MANSX'],
            'MANCC' => ['nullable', 'exists:NHACUNGCAP,MANCC'],
            'DONVT' => ['nullable', 'string', 'max:50'],
            'DONGIA_SP' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'SOLUONGTON' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'min:0', 'max:1000000'],
            'TRANGTHAI' => ['required', 'in:DangBan,HetHang,NgungBan'],
            'CPU' => ['nullable', 'string', 'max:100'],
            'RAM' => ['nullable', 'string', 'max:100'],
            'ROM' => ['nullable', 'string', 'max:100'],
            'MANHINH' => ['nullable', 'string', 'max:100'],
            'VGA' => ['nullable', 'string', 'max:100'],
            'PIN' => ['nullable', 'string', 'max:100'],
            'KHAC' => ['nullable', 'string', 'max:2000'],
            'GHI_CHU_GIA' => ['nullable', 'string', 'max:200'],
            'anh' => ['nullable', 'array', 'max:10'],
            'anh.*' => ['file', 'mimes:'.implode(',', config('minio.mimes')), 'max:'.config('minio.max_kb')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TENSP' => 'tên sản phẩm',
            'MALOAI' => 'loại sản phẩm',
            'MANSX' => 'nhà sản xuất',
            'MANCC' => 'nhà cung cấp',
            'DONVT' => 'đơn vị tính',
            'DONGIA_SP' => 'giá bán',
            'SOLUONGTON' => 'số lượng tồn',
            'TRANGTHAI' => 'trạng thái',
            'MANHINH' => 'màn hình',
            'KHAC' => 'thông tin khác',
            'GHI_CHU_GIA' => 'ghi chú đổi giá',
            'anh' => 'ảnh sản phẩm',
            'anh.*' => 'ảnh',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'anh.max' => 'Mỗi lần tải tối đa 10 ảnh.',
            'anh.*.mimes' => 'Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF.',
            'anh.*.max' => 'Mỗi ảnh tối đa '.(config('minio.max_kb') / 1024).' MB.',
        ];
    }
}
