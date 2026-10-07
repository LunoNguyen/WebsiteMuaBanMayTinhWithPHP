@extends('layouts.admin', ['title' => 'Đơn hàng '.$hd->MAHD, 'breadcrumb' => ['Bán hàng', 'Đơn hàng', $hd->MAHD]])

@section('content')
    @include('partials.quan-tri.chi-tiet-don', [
        'routeDs' => 'banhang.donhang',
        'routeKhach' => 'banhang.khachhang.show',
        'nut' => \App\Support\NutDonHang::cho($hd, 'banhang'),
    ])
@endsection
