@extends('layouts.kho', ['title' => 'Đơn '.$hd->MAHD])

@push('styles')
@include('partials.kho.style-trang')
<link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@section('content')
<div class="wsh">
@include('partials.kho.sidebar')
<div class="wmn">
  <div class="kho-topbar">
    <button type="button" class="w-menu-btn" onclick="toggleKhoSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>
    <span style="display:flex;color:var(--wm)">{!! icon('truck', 18) !!}</span>
    <h2>Đơn {{ $hd->MAHD }}</h2>
    <div class="kto-user">
      <x-theme-toggle />
      <div style="text-align:right">
        <div style="font-size:12px;font-weight:700;color:var(--wt)">{{ auth()->user()->tenHienThi() }}</div>
        <div style="font-size:10px;color:var(--wm)">Nhân viên Kho &bull; {{ auth()->user()->MANV ?? '—' }}</div>
      </div>
      <div class="kto-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</div>
    </div>
  </div>

  <div class="wct">
    @if (session('thong_bao'))
      <div class="alert-msg">{!! icon('check') !!} {{ session('thong_bao') }}</div>
    @endif

    <div data-rt-vung="don" data-rt-khi="don:{{ $hd->MAHD }}">
    <div class="wph">
      <div>
        <h1>Đơn hàng {{ $hd->MAHD }}</h1>
        <p>{!! statusBadge($hd->TRANGTHAI) !!}</p>
      </div>
      <div class="ct-actions">
        <a href="{{ route('kho.donhang') }}" class="wbn wb-out">← Danh sách</a>
        @if ($hd->TRANGTHAI === 'DaXacNhan')
          <x-nut-hanh-dong :action="route('kho.donhang.xuat-kho', $hd->MAHD)" method="PATCH" class="wbn wb-suc"
                           :confirm="'Xác nhận xuất kho đơn '.$hd->MAHD.'?'">Xuất kho &amp; bàn giao</x-nut-hanh-dong>
        @endif
      </div>
    </div>

    @include('partials.chi-tiet.don-hang')
    </div>
  </div>
</div>
</div>
@endsection
