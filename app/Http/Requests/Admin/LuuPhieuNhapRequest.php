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
     * Tự động chuẩn hoá dữ liệu trước khi kiểm tra:
     * - Tự động nhận diện NCC mới nếu chỉ gõ tên mà chưa chọn dropdown.
     * - Tự động nhận diện SP mới nếu chỉ gõ tên hoặc đơn giá mà chưa chọn dropdown.
     */
    protected function prepareForValidation(): void
    {
        // 1. Tự động xử lý MANCC
        $mancc  = trim((string) $this->input('MANCC', ''));
        $nccTen = trim((string) $this->input('ncc_moi_ten', ''));
        if ($mancc === '' && $nccTen !== '') {
            $existing = \Illuminate\Support\Facades\DB::table('NHACUNGCAP')
                ->where('TENNCC', $nccTen)
                ->value('MANCC');
            $this->merge(['MANCC' => $existing ?: '__new__']);
        }

        // 2. Tự động xử lý từng dòng hàng
        $dongRaw = (array) $this->input('dong', []);
        $dong = [];
        foreach ($dongRaw as $d) {
            if (! is_array($d)) {
                continue;
            }
            $masp     = trim((string) ($d['MASP'] ?? ''));
            $tenSpMoi = trim((string) ($d['TENSP_MOI'] ?? ''));
            $donGia   = isset($d['DONGIA_NHAP']) ? (float) $d['DONGIA_NHAP'] : null;

            // Nếu chưa có MASP nhưng có tên SP mới hoặc có đơn giá nhập
            if ($masp === '') {
                if ($tenSpMoi !== '') {
                    $existingSp = \Illuminate\Support\Facades\DB::table('SANPHAM')
                        ->where('TENSP', $tenSpMoi)
                        ->orWhere('MASP', $tenSpMoi)
                        ->value('MASP');
                    $d['MASP'] = $existingSp ?: '__new__';
                } elseif ($donGia !== null && $donGia > 0) {
                    $d['MASP']       = '__new__';
                    $d['TENSP_MOI'] = 'Sản phẩm mới ' . date('d/m/Y H:i');
                }
            }

            // Giữ lại dòng nếu có MASP
            if (! empty($d['MASP'])) {
                $d['SOLUONG']     = max(1, (int) ($d['SOLUONG'] ?? 1));
                $d['DONGIA_NHAP'] = max(0, (float) ($d['DONGIA_NHAP'] ?? 0));
                $dong[]           = $d;
            }
        }

        $this->merge(['dong' => $dong]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // NCC: chấp nhận mã NCC tồn tại hoặc "__new__" (tạo mới)
            'MANCC' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === '__new__') {
                        return;
                    }
                    if (! \Illuminate\Support\Facades\DB::table('NHACUNGCAP')->where('MANCC', $value)->exists()) {
                        $fail('Nhà cung cấp đã chọn không tồn tại trong hệ thống.');
                    }
                },
            ],
            'ncc_moi_ten' => [
                'nullable',
                'string',
                'max:200',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('MANCC') === '__new__' && ! filled($value)) {
                        $fail('Vui lòng nhập tên nhà cung cấp mới.');
                    }
                },
            ],
            'ncc_moi_diachi'            => ['nullable', 'string', 'max:300'],
            'ncc_moi_sdt'               => ['nullable', 'string', 'max:20'],
            'ncc_moi_email'             => ['nullable', 'email', 'max:100'],

            'NGAY_DATMUA'               => ['required', 'date'],
            'NGAYGIAO'                  => ['nullable', 'date', 'after_or_equal:NGAY_DATMUA'],
            'THUE_VAT'                  => ['required', 'numeric', 'min:0', 'max:100'],
            'CHIETKHAU'                 => ['required', 'numeric', 'min:0', 'max:100'],
            'TRANGTHAI_THANHTOAN'       => ['required', 'in:ChuaThanhToan,DaThanhToan,HoanTien'],
            'GHI_CHU'                   => ['nullable', 'string', 'max:2000'],
            'dong'                      => ['required', 'array', 'min:1', 'max:100'],
            // MASP mỗi dòng: chấp nhận mã SP tồn tại hoặc "__new__" (tạo mới)
            'dong.*.MASP' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === '__new__') {
                        return;
                    }
                    if (! \Illuminate\Support\Facades\DB::table('SANPHAM')->where('MASP', $value)->exists()) {
                        $fail('Sản phẩm đã chọn không tồn tại trong hệ thống.');
                    }
                },
            ],
            'dong.*.SOLUONG'            => ['required', 'integer', 'min:1', 'max:100000'],
            'dong.*.DONGIA_NHAP'        => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'dong.*.GHI_CHU'            => ['nullable', 'string', 'max:200'],
            // Thông tin SP mới (khi MASP=__new__)
            'dong.*.TENSP_MOI'          => ['nullable', 'string', 'max:300'],
            'dong.*.MALOAI_MOI'         => ['nullable', 'string', 'max:50'],
            'dong.*.TENLOAI_TU_NHAP'    => ['nullable', 'string', 'max:100'],
            'dong.*.MANSX_MOI'          => ['nullable', 'string', 'max:50'],
            'dong.*.TENNSX_TU_NHAP'     => ['nullable', 'string', 'max:100'],
            'dong.*.DONGIA_BAN_MOI'     => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'MANCC'                 => 'nhà cung cấp',
            'ncc_moi_ten'           => 'tên nhà cung cấp mới',
            'NGAY_DATMUA'           => 'ngày đặt mua',
            'NGAYGIAO'              => 'ngày giao dự kiến',
            'THUE_VAT'              => 'thuế VAT',
            'CHIETKHAU'             => 'chiết khấu',
            'TRANGTHAI_THANHTOAN'   => 'trạng thái thanh toán',
            'GHI_CHU'               => 'ghi chú',
            'dong'                  => 'danh sách sản phẩm',
            'dong.*.MASP'           => 'sản phẩm',
            'dong.*.SOLUONG'        => 'số lượng',
            'dong.*.DONGIA_NHAP'    => 'đơn giá nhập',
            'dong.*.GHI_CHU'        => 'ghi chú dòng',
            'dong.*.TENSP_MOI'      => 'tên sản phẩm mới',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->thongBaoChung(),
            'dong.required'         => 'Phiếu nhập cần ít nhất một sản phẩm.',
            'ncc_moi_ten.required'  => 'Vui lòng nhập tên nhà cung cấp mới.',
        ];
    }
}
