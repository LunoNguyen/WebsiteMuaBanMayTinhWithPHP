{{--
    Trang chi tiết đơn dùng chung (Admin, Bán hàng, Kho), theo phương án A:
    "← Đơn hàng" + tên đơn + trạng thái, nút thao tác bên phải; dưới là tiến trình và nội dung đơn.
    Biến: $hd (HoaDon đã napChiTiet), $routeDs (route danh sách), $routeKhach (route hồ sơ khách hoặc null),
          $nut: list [nhãn, url, kiểu ('chinh' | 'phu' | 'huy'), method, câu xác nhận|null, icon|null], đã lọc theo quyền.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@include('partials.thong-bao')

<div data-rt-vung="don" data-rt-khi="don:{{ $hd->MAHD }}">
    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route($routeDs) }}" class="qt-back">{!! icon('arrow-left', 14) !!} Đơn hàng</a>
            <h1>Đơn hàng {{ $hd->MAHD }}</h1>
            <p>{!! statusBadge($hd->TRANGTHAI) !!} &nbsp;Đặt lúc {{ $hd->NGAYLAP?->format('H:i d/m/Y') }}{{ $hd->nhanVien ? ' · NV xử lý: '.$hd->nhanVien->TENNV : '' }}</p>
        </div>
        <div class="page-header-right">
            @foreach ($nut as [$nhan, $url, $kieu, $method, $xacNhan, $bieuTuong])
                @php($lop = ['chinh' => 'btn btn-primary', 'phu' => 'btn btn-outline', 'huy' => 'btn btn-ghost-red'][$kieu])
                @if ($method === 'GET')
                    <a href="{{ $url }}" class="{{ $lop }}" target="_blank" rel="noopener">@if ($bieuTuong){!! icon($bieuTuong, 16) !!}@endif {{ $nhan }}</a>
                @else
                    <x-nut-hanh-dong :action="$url" :method="$method" :class="$lop" :confirm="$xacNhan">@if ($bieuTuong){!! icon($bieuTuong, 16) !!}@endif {{ $nhan }}</x-nut-hanh-dong>
                @endif
            @endforeach
        </div>
    </div>

    @include('partials.chi-tiet.don-hang', ['routeKhach' => $routeKhach])
</div>
