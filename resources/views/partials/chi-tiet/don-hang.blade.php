{{--
    Chi tiết đơn hàng dùng chung (Admin, Kho, Bán hàng). Biến: $hd (HoaDon đã gọi napChiTiet()), $routeKhach (tuỳ chọn, route hồ sơ khách).
--}}
@php
    $buoc = ['ChoXacNhan' => 'Chờ xác nhận', 'DaXacNhan' => 'Đã xác nhận', 'DangGiao' => 'Đang giao', 'DaGiao' => 'Đã giao', 'HoanThanh' => 'Hoàn thành'];
    $viTri = array_search($hd->TRANGTHAI, array_keys($buoc), true);
    $tt = $hd->thanhToan;
    $routeKhach ??= null;
@endphp
<div class="ct-wrap">
    <div class="ct-card">
        @if ($hd->TRANGTHAI === 'DaHuy')
            <div class="ct-note">Đơn đã huỷ. Tồn kho và lượt dùng mã giảm giá đã được hoàn lại.</div>
        @else
            <div class="ct-steps">
                @foreach (array_values($buoc) as $i => $tenBuoc)
                    <div @class(['ct-step', 'done' => $viTri !== false && $i <= $viTri])>{{ $tenBuoc }}</div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="ct-grid">
        <section class="ct-card">
            <div class="ct-card-hd"><h3>Sản phẩm ({{ $hd->chiTiets->sum('SOLUONG') }})</h3></div>
            <div class="ct-table-wrap">
                <table class="ct-table">
                    <thead><tr><th>Sản phẩm</th><th class="num">SL</th><th class="num">Đơn giá</th><th class="num">Thành tiền</th></tr></thead>
                    <tbody>
                        @foreach ($hd->chiTiets as $ct)
                            <tr>
                                <td>{{ $ct->sanPham?->TENSP ?? $ct->MASP }}<span class="ct-sub">{{ $ct->MASP }}</span></td>
                                <td class="num">{{ formatNum($ct->SOLUONG) }}</td>
                                <td class="num">{{ formatVND($ct->DONGIA_LUCAT) }}</td>
                                <td class="num"><strong>{{ formatVND($ct->THANHTIEN) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ct-sum">
                <div><span>Tạm tính</span><strong>{{ formatVND($hd->TONGTIEN_TRUOCGIAM) }}</strong></div>
                <div>
                    <span>Giảm giá @foreach ($hd->khuyenMais as $km)<code>{{ $km->MA_CODE }}</code>@endforeach</span>
                    <strong>{{ (float) $hd->TONGTIEN_GIAM > 0 ? '−'.formatVND($hd->TONGTIEN_GIAM) : formatVND(0) }}</strong>
                </div>
                <div class="ct-total"><span>Tổng cộng</span><strong>{{ formatVND($hd->TONGTIEN_HD) }}</strong></div>
            </div>
        </section>

        <div class="ct-wrap">
            <section class="ct-card">
                <div class="ct-card-hd"><h3>Nhận hàng</h3></div>
                <div class="ct-body">
                    <dl class="ct-dl">
                        <dt>Khách hàng</dt>
                        <dd>
                            @if ($hd->khachHang && $routeKhach)
                                <a href="{{ route($routeKhach, $hd->MAKH) }}">{{ $hd->khachHang->TENKH }}</a>
                            @else
                                {{ $hd->khachHang?->TENKH ?? 'Khách lẻ' }}
                            @endif
                        </dd>
                        <dt>Người nhận</dt><dd>{{ $hd->TEN_NGUOINHAN ?? '—' }}</dd>
                        <dt>Điện thoại</dt><dd>{{ $hd->SDT_NGUOINHAN ?? '—' }}</dd>
                        <dt>Hình thức</dt><dd>{{ $hd->PHUONG_THUC_GH === 'TaiQuay' ? 'Nhận tại cửa hàng' : 'Giao tận nơi' }}</dd>
                        @if ($hd->DIACHI_GIAOHANG)<dt>Địa chỉ</dt><dd>{{ $hd->DIACHI_GIAOHANG }}</dd>@endif
                        @if ($hd->GHI_CHU)<dt>Ghi chú</dt><dd>{{ $hd->GHI_CHU }}</dd>@endif
                        <dt>Ngày đặt</dt><dd>{{ $hd->NGAYLAP?->format('d/m/Y H:i') }}</dd>
                        <dt>Nhân viên</dt><dd>{{ $hd->nhanVien?->TENNV ?? ($hd->MATK ? 'Khách tự đặt online' : '—') }}</dd>
                    </dl>
                </div>
            </section>

            <section class="ct-card">
                <div class="ct-card-hd"><h3>Thanh toán</h3>{!! $tt ? statusBadge($tt->TRANGTHAI, 'thanhtoan') : '' !!}</div>
                <div class="ct-body">
                    @if ($tt)
                        <dl class="ct-dl">
                            <dt>Phương thức</dt><dd>{{ \App\Http\Controllers\Shop\ThanhToanController::PHUONG_THUC[$tt->PHUONG_THUC] ?? $tt->PHUONG_THUC }}</dd>
                            <dt>Số tiền</dt><dd>{{ formatVND($tt->SOTIEN) }}</dd>
                            @if ($tt->NOI_DUNG_CK)<dt>Nội dung CK</dt><dd><code>{{ $tt->NOI_DUNG_CK }}</code></dd>@endif
                            @if ($tt->NGAY_THANHTOAN)<dt>Ngày trả</dt><dd>{{ $tt->NGAY_THANHTOAN->format('d/m/Y H:i') }}</dd>@endif
                        </dl>
                    @else
                        <p style="margin:0;font-size:13px;color:var(--text-muted)">Chưa có thông tin thanh toán.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
