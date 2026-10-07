@extends('layouts.admin', ['title' => 'Nhập hàng', 'breadcrumb' => ['Kho', 'Nhập hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Nhập hàng</h1>
            <p>Đã nhập {{ formatVND($tongNhapThang) }} trong tháng này · {{ formatNum($dem[1][2]) }} phiếu chờ duyệt</p>
        </div>
        <div class="page-header-right">
            <button type="button" class="btn btn-outline" onclick="exportTableCSV('pnhTable','phieunhap')">{!! icon('download', 16) !!} Xuất CSV</button>
            <a href="{{ route('admin.nhaphang.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Tạo phiếu nhập</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" :dang="$trangthai" />

    <div class="card">
        <form method="GET" class="qt-toolbar">
            @if ($trangthai !== '')
                <input type="hidden" name="trangthai" value="{{ $trangthai }}">
            @endif
            <x-qt.tim :value="$search" placeholder="Tìm mã phiếu hoặc nhà cung cấp" />
            <x-qt.chon name="mancc" :value="$mancc" :options="['' => 'Mọi nhà cung cấp'] + $nccList" />
            <x-qt.chon name="tttt" :value="$tttt" :options="['' => 'Mọi thanh toán'] + array_map(fn ($tt) => $tt[1], $ttMap)" />
        </form>

        @if (empty($phieunhap))
            <div class="qt-empty">
                {!! icon('inbox', 40) !!}
                <h3>Không có phiếu nhập nào khớp điều kiện lọc</h3>
                <a href="{{ route('admin.nhaphang') }}" class="btn btn-outline">Xoá bộ lọc</a>
            </div>
        @else
            <div class="table-wrapper">
                <table id="pnhTable">
                    <thead>
                        <tr><th>Mã phiếu</th><th>Nhà cung cấp</th><th>Ngày đặt</th><th class="num">Số lượng</th><th class="num">Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($phieunhap as $pnh)
                            @php
                                [$mauSt, $nhanSt] = $statusMap[$pnh['TRANGTHAI']] ?? ['var(--text-secondary)', $pnh['TRANGTHAI']];
                                [$mauTt, $nhanTt] = $ttMap[$pnh['TRANGTHAI_THANHTOAN']] ?? ['var(--text-secondary)', '—'];
                            @endphp
                            <tr>
                                <td><a href="{{ route('admin.nhaphang.show', $pnh['MAPNH']) }}" class="qt-ma">{{ $pnh['MAPNH'] }}</a><span class="qt-sub">{{ $pnh['TENNV'] ?? '' }}</span></td>
                                <td>{{ $pnh['TENNCC'] ?? '—' }}<span class="qt-sub">{{ $pnh['MANCC'] }}</span></td>
                                <td>
                                    {{ $pnh['NGAY_DATMUA'] ? date('d/m/Y', strtotime($pnh['NGAY_DATMUA'])) : '—' }}
                                    @if ($pnh['NGAYNHAN'])
                                        <span class="qt-sub">nhận {{ date('d/m/Y', strtotime($pnh['NGAYNHAN'])) }}</span>
                                    @endif
                                </td>
                                <td class="num">{{ formatNum($pnh['tong_sl'] ?? 0) }}<span class="qt-sub">{{ $pnh['so_san_pham'] }} mã</span></td>
                                <td class="num"><strong>{{ formatVND($pnh['TONGCONG_PNH'] ?? 0) }}</strong><span class="qt-sub">VAT {{ (float) $pnh['THUE_VAT'] }}% · CK {{ (float) $pnh['CHIETKHAU'] }}%</span></td>
                                <td><span class="badge-tt" style="--c:{{ $mauTt }}">{{ $nhanTt }}</span></td>
                                <td><span class="badge-tt" style="--c:{{ $mauSt }}">{{ $nhanSt }}</span></td>
                                <td>
                                    <div class="qt-actions">
                                        <a href="{{ route('admin.nhaphang.show', $pnh['MAPNH']) }}" class="btn-icon" title="Chi tiết" aria-label="Chi tiết {{ $pnh['MAPNH'] }}">{!! icon('eye', 15) !!}</a>
                                        @if ($pnh['TRANGTHAI'] === 'ChoDuyet')
                                            <a href="{{ route('admin.nhaphang.edit', $pnh['MAPNH']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $pnh['MAPNH'] }}">{!! icon('pencil', 15) !!}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="phiếu" />
        @endif
    </div>
@endsection
