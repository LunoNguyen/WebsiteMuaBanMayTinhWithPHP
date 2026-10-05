<?php

namespace App\Http\Requests\Admin;

/**
 * Thông báo lỗi tiếng Việt dùng chung cho các form quản trị; tên trường lấy từ attributes().
 */
trait ThongBaoTiengViet
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->thongBaoChung();
    }

    /**
     * Thông báo theo luật; request cần thêm thông báo riêng thì gộp mảng này với của nó.
     *
     * @return array<string, string>
     */
    protected function thongBaoChung(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'required_if' => 'Vui lòng nhập :attribute.',
            'required_with' => 'Vui lòng nhập :attribute.',
            'string' => ':attribute không hợp lệ.',
            'max' => ':attribute vượt quá giới hạn cho phép (:max).',
            'min' => ':attribute phải tối thiểu :min.',
            'prohibited' => 'Không được đổi :attribute ở đây.',
            'integer' => ':attribute phải là số nguyên.',
            'numeric' => ':attribute phải là số.',
            'email' => ':attribute không đúng định dạng email.',
            'date' => ':attribute không phải ngày hợp lệ.',
            'before' => ':attribute phải trước :date.',
            'before_or_equal' => ':attribute không được sau hôm nay.',
            'after_or_equal' => ':attribute phải sau hoặc bằng :date.',
            'in' => ':attribute không hợp lệ.',
            'exists' => ':attribute không tồn tại.',
            'unique' => ':attribute đã được dùng.',
            'regex' => ':attribute không đúng định dạng.',
            'confirmed' => ':attribute nhập lại không khớp.',
            'array' => ':attribute không hợp lệ.',
            'distinct' => ':attribute bị trùng.',
            'boolean' => ':attribute không hợp lệ.',
        ];
    }
}
