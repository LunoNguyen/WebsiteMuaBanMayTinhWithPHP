@extends('layouts.admin', ['title' => 'Đơn hàng '.$hd->MAHD, 'breadcrumb' => ['Quản lý', 'Đơn hàng', $hd->MAHD]])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@section('content')
    @include('partials.thong-bao')

    <div data-rt-vung="don" data-rt-khi="don:{{ $hd->MAHD }}">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Đơn hàng {{ $hd->MAHD }}</h1>
            <p>{!! statusBadge($hd->TRANGTHAI) !!}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.donhang') }}" class="btn btn-outline">← Danh sách</a>
            @if (in_array($hd->TRANGTHAI, \App\Models\HoaDon::HUY_DUOC, true))
                <x-nut-hanh-dong :action="route('admin.donhang.huy', $hd->MAHD)" method="PATCH" class="btn btn-outline" style="color:var(--red)"
                                 :confirm="'Huỷ đơn '.$hd->MAHD.'? Tồn kho và lượt dùng mã sẽ được hoàn lại.'">Huỷ đơn</x-nut-hanh-dong>
            @endif
            @if ($hd->thanhToan?->TRANGTHAI === 'ChoThanhToan' && $hd->TRANGTHAI !== 'DaHuy')
                <x-nut-hanh-dong :action="route('admin.donhang.thanh-toan', $hd->MAHD)" method="PATCH" class="btn btn-outline"
                                 :confirm="'Xác nhận đã nhận '.formatVND($hd->TONGTIEN_HD).' cho đơn '.$hd->MAHD.'?'">Đã nhận tiền</x-nut-hanh-dong>
            @endif
            @isset(\App\Models\HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI])
                <x-nut-hanh-dong :action="route('admin.donhang.buoc-tiep-theo', $hd->MAHD)" method="PATCH" class="btn btn-primary">
                    {{ \App\Models\HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI] }}
                </x-nut-hanh-dong>
            @endisset
        </div>
    </div>

    @include('partials.chi-tiet.don-hang', ['routeKhach' => 'admin.khachhang.show'])
    </div>
@endsection
