{{--
    Bảng đơn hàng dùng chung (Admin, Bán hàng, Kho), cột theo phương án A:
    Mã · ngày | Khách hàng | Tổng tiền | Nhận hàng | Thanh toán | Trạng thái | thao tác.
    Biến: $donhang (mảng dòng), $routeXem (route chi tiết), $nutTiep: fn(array $dong): ?array{0: string nhãn, 1: string url}
          $rong (câu khi danh sách rỗng), $coLoc (đang lọc hay không).
--}}
@php
    $ptTT = ['COD' => 'COD', 'ChuyenKhoan' => 'Chuyển khoản', 'QR' => 'QR', 'TienMat' => 'Tiền mặt'];
    $bayGio = now();
@endphp
@if (empty($donhang))
    <div class="qt-empty">
        {!! icon('inbox', 40) !!}
        <h3>{{ $coLoc ? 'Không có đơn nào khớp bộ lọc' : ($rong ?? 'Chưa có đơn hàng nào') }}</h3>
        @if ($coLoc)
            <p>Thử bỏ bớt điều kiện lọc.</p>
            <a href="{{ url()->current() }}" class="btn btn-outline">Xoá bộ lọc</a>
        @endif
    </div>
@else
    <div class="table-wrapper">
        <table id="bangDon">
            <thead>
                <tr><th>Mã · ngày đặt</th><th>Khách hàng</th><th class="num">Tổng tiền</th><th>Nhận hàng</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($donhang as $dh)
                    @php
                        $ngay = $dh['NGAYLAP'] ? \Illuminate\Support\Carbon::parse($dh['NGAYLAP']) : null;
                        $phutCho = $dh['TRANGTHAI'] === 'ChoXacNhan' && $ngay ? (int) $ngay->diffInMinutes($bayGio) : 0;
                        $nut = $nutTiep($dh);
                    @endphp
                    <tr>
                        <td><a href="{{ route($routeXem, $dh['MAHD']) }}" class="qt-ma">{{ $dh['MAHD'] }}</a><span class="qt-sub">{{ $ngay?->format('d/m/Y H:i') ?? '—' }}</span></td>
                        <td>
                            {{ $dh['TEN_NGUOINHAN'] ?? $dh['TENKH'] ?? 'Khách lẻ' }}
                            <span class="qt-sub">{{ $dh['SDT_NGUOINHAN'] ?? $dh['SDT_KH'] ?? '' }}{{ $dh['TENNV'] ? ' · NV: '.$dh['TENNV'] : ' · Đặt online' }}</span>
                            @if ($phutCho >= 120)
                                <span class="qt-late">{!! icon('clock', 12) !!} Chờ {{ $phutCho >= 1440 ? intdiv($phutCho, 1440).' ngày' : intdiv($phutCho, 60).' giờ '.($phutCho % 60).' phút' }}</span>
                            @endif
                        </td>
                        <td class="num"><strong>{{ formatVND($dh['TONGTIEN_HD'] ?? 0) }}</strong></td>
                        <td>{{ ($dh['PHUONG_THUC_GH'] ?? '') === 'TaiQuay' ? 'Tại quầy' : 'Giao hàng' }}<span class="qt-sub">{{ $ptTT[$dh['PT_TT'] ?? ''] ?? '—' }}</span></td>
                        <td>{!! ($dh['TT_TT'] ?? null) ? statusBadge($dh['TT_TT'], 'thanhtoan') : '—' !!}</td>
                        <td>{!! statusBadge($dh['TRANGTHAI']) !!}</td>
                        <td>
                            <div class="qt-actions">
                                @if ($nut)
                                    <x-nut-hanh-dong :action="$nut[1]" method="PATCH" class="btn btn-primary btn-sm"
                                                     :confirm="$nut[0].' đơn '.$dh['MAHD'].'?'">{{ $nut[0] }}</x-nut-hanh-dong>
                                @endif
                                <a href="{{ route($routeXem, $dh['MAHD']) }}" class="btn-icon" title="Xem chi tiết" aria-label="Xem chi tiết {{ $dh['MAHD'] }}">{!! icon('eye', 15) !!}</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
