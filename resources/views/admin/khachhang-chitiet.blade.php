@extends('layouts.admin', ['title' => 'Khách hàng '.$kh->MAKH, 'breadcrumb' => ['Quản lý', 'Khách hàng', $kh->MAKH]])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('admin.khachhang') }}" class="qt-back">{!! icon('arrow-left', 14) !!} Khách hàng</a>
            <h1>{{ $kh->TENKH ?? $kh->MAKH }}</h1>
            <p>{{ $kh->MAKH }}{{ $kh->SDT_KH ? ' · '.$kh->SDT_KH : '' }}</p>
        </div>
        <div class="page-header-right">
            @unless ($kh->taiKhoan)
                <a href="{{ route('admin.taikhoan.create', ['makh' => $kh->MAKH]) }}" class="btn btn-outline">Tạo tài khoản</a>
            @endunless
            @if ($tomTat['so_don'] === 0 && ! $kh->taiKhoan)
                <x-nut-hanh-dong :action="route('admin.khachhang.destroy', $kh->MAKH)" method="DELETE" class="btn btn-outline"
                                 :confirm="'Xoá khách hàng '.$kh->MAKH.'?'" style="color:var(--red)">Xoá</x-nut-hanh-dong>
            @endif
            <a href="{{ route('admin.khachhang.edit', $kh->MAKH) }}" class="btn btn-primary">Sửa thông tin</a>
        </div>
    </div>

    @include('partials.thong-bao')
    @include('partials.chi-tiet.khach-hang', ['routeDon' => 'admin.donhang.show'])
@endsection
