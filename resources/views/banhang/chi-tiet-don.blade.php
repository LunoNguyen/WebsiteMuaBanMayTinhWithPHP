@extends('layouts.banhang', ['title' => 'Đơn '.$hd->MAHD])

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
      <span style="display:flex;color:var(--mt)">{!! icon('cart', 18) !!}</span>
      <h2>Đơn {{ $hd->MAHD }}</h2>
      <div class="tb-right">
        <x-theme-toggle />
        <div style="text-align:right">
          <div style="font-size:12px;font-weight:700;color:var(--tx)">{{ auth()->user()->tenHienThi() }}</div>
          <div style="font-size:10px;color:var(--mt)">NV Bán hàng &bull; {{ auth()->user()->MANV ?? '—' }}</div>
        </div>
        <div class="tb-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</div>
      </div>
    </div>

    <div class="ct">
      @if (session('thong_bao'))
        <div class="amsg {{ session('loai') === 'info' ? 'info' : 'ok' }}">
          {!! icon(session('loai') === 'info' ? 'bulb' : 'check') !!} {{ session('thong_bao') }}
        </div>
      @endif

    <div data-rt-vung="don" data-rt-khi="don:{{ $hd->MAHD }}">
      <div class="ph">
        <div>
          <h1>Đơn hàng {{ $hd->MAHD }}</h1>
          <p>{!! statusBadge($hd->TRANGTHAI) !!}</p>
        </div>
        <div class="ct-actions">
          <a href="{{ route('banhang.donhang') }}" class="btn btn-out">← Danh sách</a>
          @if (in_array($hd->TRANGTHAI, \App\Models\HoaDon::HUY_DUOC, true))
            <x-nut-hanh-dong :action="route('banhang.donhang.huy', $hd->MAHD)" method="PATCH" class="btn btn-sm-red"
                             :confirm="'Huỷ đơn '.$hd->MAHD.'?'">Huỷ đơn</x-nut-hanh-dong>
          @endif
          @isset(\App\Models\HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI])
            <x-nut-hanh-dong :action="route('banhang.donhang.buoc-tiep-theo', $hd->MAHD)" method="PATCH" class="btn btn-pri">
              {{ \App\Models\HoaDon::NHAN_BUOC_TIEP_THEO[$hd->TRANGTHAI] }}
            </x-nut-hanh-dong>
          @endisset
        </div>
      </div>

      @include('partials.chi-tiet.don-hang', ['routeKhach' => 'banhang.khachhang.show'])
    </div>
    </div>
  </div>
</div>
@endsection
