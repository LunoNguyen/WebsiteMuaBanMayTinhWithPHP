@extends('layouts.admin', ['title' => 'Khách hàng '.$kh->MAKH, 'breadcrumb' => ['Bán hàng', 'Khách hàng', $kh->MAKH]])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('banhang.khachhang') }}" class="qt-back">{!! icon('arrow-left', 14) !!} Khách hàng</a>
            <h1>{{ $kh->TENKH ?? $kh->MAKH }}</h1>
            <p>{{ $kh->MAKH }}{{ $kh->SDT_KH ? ' · '.$kh->SDT_KH : '' }}</p>
        </div>
    </div>

    @include('partials.chi-tiet.khach-hang', ['routeDon' => 'banhang.donhang.show'])
@endsection
