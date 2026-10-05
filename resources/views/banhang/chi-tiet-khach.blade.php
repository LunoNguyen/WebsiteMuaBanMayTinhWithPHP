@extends('layouts.banhang', ['title' => 'Khách hàng '.$kh->MAKH])

@push('styles')
@include('partials.banhang.style-trang')
<link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@section('content')
<div class="shell">
  @include('partials.banhang.sidebar')
  <div class="main">
    <div class="topbar">
      <button type="button" class="w-menu-btn" onclick="toggleNvbSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>
      <span style="display:flex;color:var(--mt)">{!! icon('user', 18) !!}</span>
      <h2>Khách hàng</h2>
      <div class="tb-right">
        <x-theme-toggle />
        <div style="text-align:right">
          <div style="font-size:12px;font-weight:700;color:var(--tx)">{{ auth()->user()->tenHienThi() }}</div>
          <div style="font-size:10px;color:var(--mt)">NV Bán hàng</div>
        </div>
        <div class="tb-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</div>
      </div>
    </div>

    <div class="ct">
      <div class="ph">
        <div>
          <h1>{{ $kh->TENKH ?? $kh->MAKH }}</h1>
          <p>{{ $kh->MAKH }}{{ $kh->SDT_KH ? ' · '.$kh->SDT_KH : '' }}</p>
        </div>
        <a href="{{ route('banhang.khachhang') }}" class="btn btn-out">← Danh sách</a>
      </div>

      @include('partials.chi-tiet.khach-hang', ['routeDon' => 'banhang.donhang.show'])
    </div>
  </div>
</div>
@endsection
