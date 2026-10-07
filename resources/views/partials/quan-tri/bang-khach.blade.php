{{--
    Card danh sách khách hàng dùng chung (Admin, Bán hàng): thanh tìm + bảng + phân trang.
    Biến: $khachhang, $search, $page, $pages, $total, $perPage, $routeXem, $routeSua (null nếu không được sửa),
          $routeDon (null nếu không có trang lọc đơn theo khách).
--}}
@php
    $mauAvatar = ['#3a56e4', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#c81e1e'];
    $hangCua = fn (float $chi): array => match (true) {
        $chi >= 50_000_000 => ['VIP', 'var(--orange)'],
        $chi >= 20_000_000 => ['Vàng', 'var(--green)'],
        $chi >= 5_000_000 => ['Bạc', 'var(--text-secondary)'],
        default => ['Đồng', 'var(--text-muted)'],
    };
@endphp
<div class="card">
    <form method="GET" class="qt-toolbar">
        <x-qt.tim :value="$search" placeholder="Tìm tên, mã, SĐT hoặc email khách" />
    </form>

    @if (empty($khachhang))
        <div class="qt-empty">
            {!! icon('users', 40) !!}
            <h3>{{ $search !== '' ? 'Không có khách nào khớp "'.$search.'"' : 'Chưa có khách hàng nào' }}</h3>
            @if ($search !== '')
                <a href="{{ url()->current() }}" class="btn btn-outline">Xoá tìm kiếm</a>
            @endif
        </div>
    @else
        <div class="table-wrapper">
            <table id="bangKhach">
                <thead>
                    <tr><th>Khách hàng</th><th>Liên hệ</th><th class="num">Đơn hàng</th><th class="num">Chi tiêu</th><th>Mua gần nhất</th><th>Hạng</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($khachhang as $i => $kh)
                        @php
                            $chi = (float) ($kh['tong_chi_tieu'] ?? $kh['tong_chi'] ?? 0);
                            [$hang, $mau] = $hangCua($chi);
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span class="user-avatar" style="width:34px;height:34px;font-size:12px;background:{{ $mauAvatar[crc32($kh['MAKH']) % count($mauAvatar)] }};color:#fff">{{ mb_strtoupper(mb_substr($kh['TENKH'] ?? 'KH', 0, 2)) }}</span>
                                    <span>
                                        <a href="{{ route($routeXem, $kh['MAKH']) }}" style="color:var(--text-primary);font-weight:600">{{ $kh['TENKH'] ?? '—' }}</a>
                                        <span class="qt-sub">{{ $kh['MAKH'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $kh['SDT_KH'] ?? '—' }}<span class="qt-sub">{{ $kh['EMAIL_KH'] ?? '' }}</span></td>
                            <td class="num">{{ formatNum($kh['so_hd'] ?? 0) }}</td>
                            <td class="num"><strong>{{ formatVND($chi) }}</strong></td>
                            <td>{{ ! empty($kh['lan_mua_cuoi']) ? date('d/m/Y', strtotime($kh['lan_mua_cuoi'])) : '—' }}</td>
                            <td><span class="badge-tt" style="--c:{{ $mau }}">{{ $hang }}</span></td>
                            <td>
                                <div class="qt-actions">
                                    <a href="{{ route($routeXem, $kh['MAKH']) }}" class="btn-icon" title="Hồ sơ" aria-label="Hồ sơ {{ $kh['TENKH'] }}">{!! icon('eye', 15) !!}</a>
                                    @if ($routeSua)
                                        <a href="{{ route($routeSua, $kh['MAKH']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $kh['TENKH'] }}">{!! icon('pencil', 15) !!}</a>
                                    @endif
                                    @if ($routeDon)
                                        <a href="{{ route($routeDon, ['makh' => $kh['MAKH']]) }}" class="btn-icon" title="Đơn hàng của khách" aria-label="Đơn hàng của {{ $kh['TENKH'] }}">{!! icon('cart', 15) !!}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="khách" />
    @endif
</div>
