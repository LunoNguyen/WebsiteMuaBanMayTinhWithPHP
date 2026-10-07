@extends('layouts.admin', ['title' => 'Đơn hàng '.$hd->MAHD, 'breadcrumb' => ['Kho', 'Xuất kho', $hd->MAHD]])

@section('content')
    @include('partials.quan-tri.chi-tiet-don', [
        'routeDs' => 'kho.donhang',
        'routeKhach' => null,
        'nut' => \App\Support\NutDonHang::cho($hd, 'kho'),
    ])
@endsection
