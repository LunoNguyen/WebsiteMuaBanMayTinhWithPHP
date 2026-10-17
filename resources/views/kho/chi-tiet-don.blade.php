@extends('layouts.admin', ['title' => 'Đơn hàng '.$hd->MAHD, 'breadcrumb' => ['Kho', 'Xuất kho', $hd->MAHD]])

@section('content')
    @php
        $chuaThanhToanNcc = $hd->pnhChuaThanhToanNCC();
    @endphp

    @if ($chuaThanhToanNcc->isNotEmpty())
        <div style="background:#fef2f2;border:1px solid #f87171;border-radius:10px;padding:16px 20px;margin-bottom:20px;color:#991b1b;box-shadow:0 2px 6px rgba(239,68,68,0.1)">
            <div style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;margin-bottom:8px">
                <span style="font-size:20px">🚫</span>
                <span>CHẶN XUẤT KHO: Đơn hàng có sản phẩm thuộc phiếu nhập chưa thanh toán cho Nhà Cung Cấp!</span>
            </div>
            <p style="font-size:13px;margin:0 0 12px 0;color:#7f1d1d;line-height:1.5">
                Nhân viên kho không thể xuất kho đơn hàng này cho đến khi các phiếu nhập từ nhà cung cấp tương ứng được thanh toán đầy đủ. Vui lòng liên hệ bộ phận liên quan để hoàn tất thanh toán.
            </p>
            <div style="background:#fff;border-radius:8px;border:1px solid #fca5a5;overflow:hidden">
                <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left">
                    <thead style="background:#fee2e2;border-bottom:1px solid #fecaca;color:#991b1b">
                        <tr>
                            <th style="padding:9px 14px">Mã phiếu nhập</th>
                            <th style="padding:9px 14px">Nhà cung cấp</th>
                            <th style="padding:9px 14px">Mặt hàng liên quan</th>
                            <th style="padding:9px 14px">Trạng thái thanh toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chuaThanhToanNcc as $p)
                            <tr style="border-bottom:1px solid #fee2e2">
                                <td style="padding:9px 14px;font-weight:700;color:#b91c1c">
                                    {{ $p->MAPNH }}
                                </td>
                                <td style="padding:9px 14px">{{ $p->TENNCC ?: $p->MANCC }}</td>
                                <td style="padding:9px 14px">
                                    <strong style="color:var(--text-primary)">{{ $p->MASP }}</strong> · {{ $p->TENSP }}
                                </td>
                                <td style="padding:9px 14px">
                                    <span style="display:inline-block;padding:3px 10px;border-radius:12px;background:#fee2e2;color:#b91c1c;font-weight:700;font-size:12px">Chưa thanh toán</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @include('partials.quan-tri.chi-tiet-don', [
        'routeDs' => 'kho.donhang',
        'routeKhach' => null,
        'nut' => \App\Support\NutDonHang::cho($hd, 'kho'),
    ])
@endsection
