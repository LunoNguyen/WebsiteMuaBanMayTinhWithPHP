<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn {{ $hd->MAHD }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info h1 { margin: 0; color: #2c3e50; }
        .invoice-details { text-align: right; }
        .customer-info { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; }
        .totals { text-align: right; margin-top: 20px; }
        .totals p { margin: 5px 0; font-size: 16px; }
        .totals h3 { margin: 10px 0; color: #e74c3c; }
        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; padding: 0; }
            .btn-print { display: none; }
        }
        .btn-print { background: #3498db; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-size: 16px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <button class="btn-print" onclick="window.print()">🖨️ In Hóa Đơn</button>

        <div class="header">
            <div class="company-info">
                <h1>NEXUS COMPUTER</h1>
                <p>Địa chỉ: 123 Đường Công Nghệ, Quận 1, TP.HCM<br>SĐT: 0123 456 789</p>
            </div>
            <div class="invoice-details">
                <h2>HÓA ĐƠN BÁN HÀNG</h2>
                <p><strong>Mã HĐ:</strong> {{ $hd->MAHD }}</p>
                <p><strong>Ngày lập:</strong> {{ $hd->NGAYLAP->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="customer-info">
            <h3>Thông tin khách hàng</h3>
            <p><strong>Khách hàng:</strong> {{ $hd->TEN_NGUOINHAN ?? ($hd->khachHang->TENKH ?? 'Khách lẻ') }}</p>
            <p><strong>Điện thoại:</strong> {{ $hd->SDT_NGUOINHAN ?? ($hd->khachHang->SDT_KH ?? '') }}</p>
            <p><strong>Địa chỉ giao hàng:</strong> {{ $hd->DIACHI_GIAOHANG ?? 'Mua tại cửa hàng' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Đơn Giá</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hd->chiTiets as $index => $chitiet)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $chitiet->sanPham->TENSP ?? 'Sản phẩm' }}</td>
                    <td>{{ $chitiet->SOLUONG }}</td>
                    <td>{{ number_format($chitiet->DONGIA_LUCAT) }} ₫</td>
                    <td>{{ number_format($chitiet->THANHTIEN) }} ₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <p>Tổng tiền hàng: <strong>{{ number_format($hd->TONGTIEN_TRUOCGIAM) }} ₫</strong></p>
            @if($hd->TONGTIEN_GIAM > 0)
            <p>Giảm giá: <strong>-{{ number_format($hd->TONGTIEN_GIAM) }} ₫</strong></p>
            @endif
            <h3>TỔNG CỘNG: {{ number_format($hd->TONGTIEN_HD) }} ₫</h3>
            <p><em>(Đã bao gồm VAT nếu có)</em></p>
        </div>
        
        <div style="margin-top: 50px; text-align: center;">
            <p>Cảm ơn quý khách đã mua sắm tại Nexus Computer!</p>
        </div>
    </div>
</body>
</html>
