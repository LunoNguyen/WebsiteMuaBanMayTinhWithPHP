@extends('layouts.admin', ['title' => 'Khách hàng', 'breadcrumb' => ['Bán hàng', 'Khách hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Khách hàng</h1>
            <p>{{ formatNum($kpiTotal['c']) }} khách · {{ formatNum($kpiMoi['c']) }} khách mới tháng này · {{ formatNum($kpiVip['c']) }} khách VIP (chi tiêu từ 50 triệu)</p>
        </div>
    </div>

    @include('partials.thong-bao')

    @include('partials.quan-tri.bang-khach', ['routeXem' => 'banhang.khachhang.show', 'routeSua' => null, 'routeDon' => null])
@endsection
