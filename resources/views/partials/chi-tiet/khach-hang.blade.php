{{--
    Hồ sơ khách hàng dùng chung (Admin, Bán hàng).
    Biến: $kh (KhachHang, đã load taiKhoan), $tomTat (KhachHang::tomTatMuaHang), $donHang (HoaDon, có thanhToan + chiTiets_count),
          $routeDon (tên route xem chi tiết đơn).
--}}
<div class="ct-wrap">
    <div class="ct-stats">
        <div class="ct-stat"><span>Số đơn hàng</span><strong>{{ formatNum($tomTat['so_don']) }}</strong></div>
        <div class="ct-stat"><span>Tổng chi tiêu (đơn đã giao)</span><strong>{{ formatVND($tomTat['tong_chi']) }}</strong></div>
        <div class="ct-stat"><span>Mua gần nhất</span><strong>{{ $tomTat['lan_cuoi'] ? \Illuminate\Support\Carbon::parse($tomTat['lan_cuoi'])->format('d/m/Y') : '—' }}</strong></div>
    </div>

    <div class="ct-grid">
        <section class="ct-card">
            <div class="ct-card-hd"><h3>Thông tin</h3></div>
            <div class="ct-body">
                <dl class="ct-dl">
                    <dt>Mã khách hàng</dt><dd>{{ $kh->MAKH }}</dd>
                    <dt>Họ tên</dt><dd>{{ $kh->TENKH ?? '—' }}</dd>
                    <dt>Số điện thoại</dt><dd>{{ $kh->SDT_KH ?? '—' }}</dd>
                    <dt>Email</dt><dd>{{ $kh->EMAIL_KH ?? '—' }}</dd>
                    <dt>Ngày sinh</dt><dd>{{ $kh->NGAYSINH?->format('d/m/Y') ?? '—' }}</dd>
                    <dt>Giới tính</dt><dd>{{ $kh->GIOITINH === null ? '—' : ($kh->GIOITINH ? 'Nam' : 'Nữ') }}</dd>
                    <dt>Địa chỉ</dt><dd>{{ $kh->DIACHI_KH ?? '—' }}</dd>
                </dl>
            </div>
        </section>

        <section class="ct-card">
            <div class="ct-card-hd"><h3>Tài khoản</h3></div>
            <div class="ct-body">
                @if ($kh->taiKhoan)
                    <dl class="ct-dl">
                        <dt>Mã tài khoản</dt><dd>{{ $kh->taiKhoan->MATK }}</dd>
                        <dt>Email đăng nhập</dt><dd>{{ $kh->taiKhoan->EMAIL_TK }}</dd>
                        <dt>Trạng thái</dt><dd>{{ ['HoatDong' => 'Hoạt động', 'KhoaTamThoi' => 'Khoá tạm thời', 'KhoaVinhVien' => 'Khoá vĩnh viễn'][$kh->taiKhoan->TRANGTHAI] ?? $kh->taiKhoan->TRANGTHAI }}</dd>
                        <dt>Ngày tạo</dt><dd>{{ $kh->taiKhoan->NGAYTAO?->format('d/m/Y') }}</dd>
                    </dl>
                @else
                    <p style="margin:0;font-size:13px;color:var(--text-muted)">Khách mua tại quầy, chưa có tài khoản đăng nhập.</p>
                @endif
            </div>
        </section>
    </div>

    <section class="ct-card">
        <div class="ct-card-hd"><h3>Đơn hàng gần đây</h3></div>
        @if ($donHang->isEmpty())
            <div class="ct-empty">Khách chưa có đơn hàng nào.</div>
        @else
            <div class="ct-table-wrap">
                <table class="ct-table">
                    <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th class="num">Sản phẩm</th><th class="num">Tổng tiền</th><th>Trạng thái</th><th>Thanh toán</th></tr></thead>
                    <tbody>
                        @foreach ($donHang as $hd)
                            <tr>
                                <td><a href="{{ route($routeDon, $hd->MAHD) }}"><strong>{{ $hd->MAHD }}</strong></a></td>
                                <td>{{ $hd->NGAYLAP?->format('d/m/Y H:i') }}</td>
                                <td class="num">{{ $hd->chi_tiets_count }}</td>
                                <td class="num"><strong>{{ formatVND($hd->TONGTIEN_HD) }}</strong></td>
                                <td>{!! statusBadge($hd->TRANGTHAI) !!}</td>
                                <td>{!! $hd->thanhToan ? statusBadge($hd->thanhToan->TRANGTHAI, 'thanhtoan') : '—' !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
