@php
    $tenKhach = $hd->khachHang?->TENKH ?: $hd->TEN_NGUOINHAN;
    $emailKhach = $hd->khachHang?->EMAIL_KH ?: $hd->taiKhoan?->EMAIL_TK;
    $sdtKhach = $hd->khachHang?->SDT_KH ?: $hd->SDT_NGUOINHAN;

    $nhanPhuongThucTT = match ($hd->thanhToan?->PHUONG_THUC) {
        'COD' => 'Thanh toán khi nhận hàng (COD)',
        'ChuyenKhoan' => 'Chuyển khoản ngân hàng',
        'QR' => 'Thanh toán qua VietQR',
        'TienMat' => 'Thanh toán tiền mặt tại quầy',
        default => $hd->thanhToan?->PHUONG_THUC ?? 'Thanh toán khi nhận hàng (COD)',
    };

    $nhanPhuongThucGH = in_array($hd->PHUONG_THUC_GH, ['TaiQuay', 'NhanTaiQuay'], true)
        ? 'Nhận trực tiếp tại cửa hàng'
        : 'Giao hàng tận nơi';

    $diaChiNhan = in_array($hd->PHUONG_THUC_GH, ['TaiQuay', 'NhanTaiQuay'], true)
        ? 'Nhận tại cửa hàng NEXUS Computer (123 Đường Công Nghệ, Quận 1, TP.HCM)'
        : ($hd->DIACHI_GIAOHANG ?: ($hd->khachHang?->DIACHI_KH ?: 'Đang cập nhật'));

    $maKhuyenMai = $hd->khuyenMais->first()?->MA_CODE;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $hd->MAHD }} từ NEXUS Computer</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f6f8fa; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #222222; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f6f8fa; padding: 24px 12px;">
        <tr>
            <td align="center">
                <!-- Khung chính bức thư -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 640px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
                    <tr>
                        <td style="padding: 32px 36px; font-size: 14px; line-height: 1.6; color: #333333;">
                            
                            <!-- Lời chào đầu thư -->
                            <p style="margin: 0 0 8px 0;">Xin chào <strong>{{ $tenKhach }}</strong>,</p>
                            <p style="margin: 0 0 8px 0;">
                                Cảm ơn Anh/chị đã đặt hàng tại <span style="background-color: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: 600;">NEXUS Computer</span>!
                            </p>
                            <p style="margin: 0 0 18px 0;">
                                Đơn hàng <a href="{{ route('donhang.show', $hd->MAHD) }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">#{{ $hd->MAHD }}</a> của anh/chị đã được ghi nhận thành công ngày <strong>{{ $hd->NGAYLAP?->format('d/m/Y') ?? now()->format('d/m/Y') }}</strong>.
                            </p>

                            <hr style="border: none; border-top: 1px solid #d1d5db; margin: 18px 0;">

                            <!-- DÒNG 1: Thông tin mua hàng & Địa chỉ nhận hàng (Nằm trên 1 dòng - 2 cột 50%) -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                                <tr>
                                    <td width="50%" valign="top" style="padding-right: 16px;">
                                        <div style="font-weight: 700; color: #111827; margin-bottom: 6px; font-size: 14px;">Thông tin mua hàng</div>
                                        <div style="color: #374151;">{{ $tenKhach }}</div>
                                        @if ($emailKhach)
                                            <div><a href="mailto:{{ $emailKhach }}" style="color: #2563eb; text-decoration: underline;">{{ $emailKhach }}</a></div>
                                        @endif
                                        @if ($sdtKhach)
                                            <div><a href="tel:{{ $sdtKhach }}" style="color: #2563eb; text-decoration: underline;">{{ $sdtKhach }}</a></div>
                                        @endif
                                    </td>
                                    <td width="50%" valign="top" style="padding-left: 16px;">
                                        <div style="font-weight: 700; color: #111827; margin-bottom: 6px; font-size: 14px;">Địa chỉ nhận hàng</div>
                                        <div style="color: #374151;">{{ $hd->TEN_NGUOINHAN }}</div>
                                        <div style="color: #374151;">{{ $diaChiNhan }}</div>
                                        @if ($hd->SDT_NGUOINHAN)
                                            <div><a href="tel:{{ $hd->SDT_NGUOINHAN }}" style="color: #2563eb; text-decoration: underline;">{{ $hd->SDT_NGUOINHAN }}</a></div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- DÒNG 2: Phương thức thanh toán & Phương thức vận chuyển (Nằm trên 1 dòng - 2 cột 50%) -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 22px;">
                                <tr>
                                    <td width="50%" valign="top" style="padding-right: 16px;">
                                        <div style="font-weight: 700; color: #111827; margin-bottom: 4px; font-size: 14px;">Phương thức thanh toán</div>
                                        <div style="color: #374151; font-size: 13px;">{{ $nhanPhuongThucTT }}</div>
                                    </td>
                                    <td width="50%" valign="top" style="padding-left: 16px;">
                                        <div style="font-weight: 700; color: #111827; margin-bottom: 4px; font-size: 14px;">Phương thức vận chuyển</div>
                                        <div style="color: #374151; font-size: 13px;">{{ $nhanPhuongThucGH }}</div>
                                    </td>
                                </tr>
                            </table>

                            <!-- KHỐI THÔNG TIN ĐƠN HÀNG -->
                            <div style="font-weight: 700; color: #111827; margin-bottom: 8px; font-size: 14px;">Thông tin đơn hàng</div>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px; font-size: 13px; color: #4b5563;">
                                <tr>
                                    <td width="50%" valign="top" style="padding-right: 16px;">
                                        Mã đơn hàng: <strong style="color: #111827;">#{{ $hd->MAHD }}</strong>
                                    </td>
                                    <td width="50%" valign="top" style="padding-left: 16px;">
                                        Ngày đặt hàng: <strong style="color: #111827;">{{ $hd->NGAYLAP?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</strong>
                                    </td>
                                </tr>
                            </table>

                            <!-- DANH SÁCH SẢN PHẨM -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin-bottom: 16px;">
                                @foreach ($hd->chiTiets as $ct)
                                    @php
                                        $sp = $ct->sanPham;
                                        $tenSp = $sp?->TENSP ?? $ct->MASP;
                                        $cauHinh = $sp ? implode(' · ', $sp->cauHinhTomTat()) : '';
                                        $cidSrc = null;

                                        // Nhúng ảnh sản phẩm vào email (nếu có trong MinIO hoặc local) để hiển thị trực tiếp trong Gmail
                                        if (isset($message) && $sp) {
                                            try {
                                                $anhChinh = $sp->anhs->firstWhere('LA_ANH_CHINH', true) ?? $sp->anhs->first();
                                                $duongDanAnh = $anhChinh?->URL_ANH;
                                                if ($duongDanAnh) {
                                                    $resMinio = app(\App\Services\MinioStorage::class)->get($duongDanAnh);
                                                    if ($resMinio?->successful()) {
                                                        $mime = (string) $resMinio->header('Content-Type') ?: 'image/jpeg';
                                                        $cidSrc = $message->embedData($resMinio->body(), basename($duongDanAnh), $mime);
                                                    } else {
                                                        $localImg = public_path('assets/img/products/'.basename($duongDanAnh));
                                                        if (file_exists($localImg)) {
                                                            $cidSrc = $message->embed($localImg);
                                                        }
                                                    }
                                                }
                                            } catch (\Throwable) {
                                                $cidSrc = null;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td width="72" valign="top" style="padding: 14px 12px 14px 0;">
                                            @if ($cidSrc)
                                                <img src="{{ $cidSrc }}" alt="{{ $tenSp }}" width="60" height="60" style="display: block; width: 60px; height: 60px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 6px; background-color: #f9fafb; padding: 2px;">
                                            @else
                                                <div style="width: 60px; height: 60px; border: 1px solid #e5e7eb; border-radius: 6px; background-color: #f9fafb; text-align: center; line-height: 60px; font-size: 24px;">
                                                    💻
                                                </div>
                                            @endif
                                        </td>
                                        <td valign="top" style="padding: 14px 0;">
                                            <a href="{{ route('sanpham.show', $ct->MASP) }}" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 14px;">{{ $tenSp }}</a>
                                            @if ($cauHinh !== '')
                                                <div style="color: #6b7280; font-size: 12px; margin-top: 2px;">{{ $cauHinh }}</div>
                                            @endif
                                            <div style="color: #9ca3af; font-size: 12px; margin-top: 1px;">SKU: {{ $ct->MASP }}</div>
                                            <div style="margin-top: 6px; font-size: 13px; color: #1f2937;">
                                                <span>{{ number_format((float) $ct->DONGIA_LUCAT, 0, ',', '.') }} VND</span>
                                                <span style="color: #6b7280; margin-left: 16px;">x {{ $ct->SOLUONG }}</span>
                                                <strong style="float: right; color: #111827;">{{ number_format((float) $ct->THANHTIEN, 0, ',', '.') }} VND</strong>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <!-- BẢNG TÍNH TỔNG TIỀN (Căn phải giống mẫu) -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td width="30%"></td>
                                    <td width="70%">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 13px; color: #374151;">
                                            <tr>
                                                <td style="padding: 4px 0;">Giảm giá{{ $maKhuyenMai ? " ({$maKhuyenMai})" : '' }}:</td>
                                                <td align="right" style="padding: 4px 0; font-weight: 600;">
                                                    {{ (float) $hd->TONGTIEN_GIAM > 0 ? '-'.number_format((float) $hd->TONGTIEN_GIAM, 0, ',', '.').' VND' : '0 VND' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">Giá trừ khuyến mãi:</td>
                                                <td align="right" style="padding: 4px 0; font-weight: 700; color: #111827;">
                                                    {{ number_format((float) $hd->TONGTIEN_HD, 0, ',', '.') }} VND
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">Phí vận chuyển:</td>
                                                <td align="right" style="padding: 4px 0; font-weight: 700; color: #111827;">
                                                    0 VND
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0 4px 0; border-top: 1px solid #e5e7eb; font-weight: 700; font-size: 14px; color: #111827;">Thành tiền:</td>
                                                <td align="right" style="padding: 10px 0 4px 0; border-top: 1px solid #e5e7eb; font-weight: 700; font-size: 15px; color: #dc2626;">
                                                    {{ number_format((float) $hd->TONGTIEN_HD, 0, ',', '.') }} VND
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Chân trang (Footer) -->
                            <p style="margin: 0 0 20px 0; font-size: 13px; color: #4b5563;">
                                Nếu Anh/chị có bất kỳ câu hỏi nào, xin liên hệ với chúng tôi tại
                                <a href="mailto:{{ config('mail.from.address') }}" style="color: #2563eb; text-decoration: underline;">{{ config('mail.from.address') }}</a>
                            </p>

                            <div style="text-align: right; font-size: 13px;">
                                <div style="font-style: italic; color: #6b7280; margin-bottom: 4px;">Trân trọng,</div>
                                <div style="font-weight: 700; color: #111827;">
                                    Ban quản trị cửa hàng <span style="background-color: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;">NEXUS Computer</span>
                                </div>
                            </div>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
