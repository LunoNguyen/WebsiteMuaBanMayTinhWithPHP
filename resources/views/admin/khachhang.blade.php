@extends('layouts.admin', ['title' => 'Khách hàng', 'breadcrumb' => ['Bán hàng', 'Khách hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý khách hàng</h1>
            <p>{{ formatNum($total) }} khách{{ $search !== '' ? ' khớp "'.$search.'"' : '' }}</p>
        </div>
        <div class="page-header-right">
            <button type="button" class="btn btn-outline" onclick="exportTableCSV('bangKhach', 'khachhang_{{ date('Ymd') }}')">{!! icon('download', 16) !!} Xuất CSV</button>
            <a href="{{ route('admin.khachhang.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Thêm khách hàng</a>
        </div>
    </div>

    @include('partials.thong-bao')

    @include('partials.quan-tri.bang-khach', ['routeXem' => 'admin.khachhang.show', 'routeSua' => 'admin.khachhang.edit', 'routeDon' => 'admin.donhang'])
@endsection
