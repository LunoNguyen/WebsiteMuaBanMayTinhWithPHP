@extends('layouts.shop')

@php
    $iconLoai = fn (string $ten): string => match (true) {
        str_contains($ten, 'Laptop') => 'laptop',
        str_contains($ten, 'PC'), str_contains($ten, 'All-in-One') => 'pc',
        str_contains($ten, 'Màn hình') => 'monitor',
        str_contains($ten, 'Bàn phím') => 'keyboard',
        str_contains($ten, 'Chuột') => 'mouse',
        str_contains($ten, 'Tai nghe') => 'headphones',
        str_contains($ten, 'RAM') => 'memory',
        str_contains($ten, 'SSD') => 'harddrive',
        default => 'cpu',
    };
    $noiBat = $banChay->first();
    $moiNhat = $moiVe->first();
@endphp

@section('content')
    {{-- Banner --}}
    <section class="s-hero">
        <div class="s-hero-main">
            <img class="s-hero-art" src="{{ asset('assets/img/logo-mark-dark.png') }}" alt="" aria-hidden="true">
            <span class="tag">NEXUS Computer Store</span>
            <h1>Laptop, PC và linh kiện chính hãng cho mọi nhu cầu</h1>
            <p>Từ máy văn phòng gọn nhẹ đến dàn máy gaming, workstation. Giao hàng toàn quốc, bảo hành chính hãng.</p>
            <a href="{{ route('sanpham.index') }}" class="btn btn-lg">Xem tất cả sản phẩm {!! icon('chevron-right', 18) !!}</a>
        </div>
        <div class="s-hero-side">
            @if ($noiBat)
                <a href="{{ route('sanpham.show', $noiBat->MASP) }}" class="s-card" data-sp="{{ $noiBat->MASP }}">
                    <span>Bán chạy nhất</span>
                    <strong>{{ $noiBat->TENSP }}</strong>
                    <span class="price" data-sp-gia>{{ formatVND($noiBat->DONGIA_SP) }}</span>
                </a>
            @endif
            @if ($moiNhat)
                <a href="{{ route('sanpham.show', $moiNhat->MASP) }}" class="s-card" data-sp="{{ $moiNhat->MASP }}">
                    <span>Mới về</span>
                    <strong>{{ $moiNhat->TENSP }}</strong>
                    <span class="price" data-sp-gia>{{ formatVND($moiNhat->DONGIA_SP) }}</span>
                </a>
            @endif
        </div>
    </section>

    {{-- Danh mục --}}
    <section class="s-section">
        <div class="s-section-hd"><h2>Danh mục sản phẩm</h2></div>
        <div class="s-cats">
            @foreach ($danhMuc as $loai)
                <a href="{{ route('sanpham.index', ['loai' => [$loai->MALOAI]]) }}" class="s-card s-cat">
                    <span class="s-cat-ico">{!! icon($iconLoai($loai->TENLOAI), 20) !!}</span>
                    <span>
                        <strong>{{ $loai->TENLOAI }}</strong>
                        <span>{{ $loai->san_phams_count > 0 ? $loai->san_phams_count.' sản phẩm' : 'Sắp có hàng' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ưu đãi: chỉ mã còn hiệu lực --}}
    @if ($khuyenMai->isNotEmpty())
        <section class="s-section">
            <div class="s-section-hd"><h2>Ưu đãi hot</h2></div>
            <div class="s-promos">
                @foreach ($khuyenMai as $km)
                    <div class="s-card s-promo">
                        <div class="s-promo-val">
                            {{ $km->LOAI_KM === 'PhanTram' ? '-'.rtrim(rtrim(number_format((float) $km->GIATRI_KM, 2), '0'), '.').'%' : formatVND($km->GIATRI_KM) }}
                        </div>
                        <div>
                            <strong>{{ $km->TENKM }}</strong>
                            <span>
                                @if ((float) $km->SOTIENTOITHIEU_NHANKM > 0) Đơn từ {{ formatVND($km->SOTIENTOITHIEU_NHANKM) }} @endif
                                @if ($km->SOTIENTOIDA_KM) · Giảm tối đa {{ formatVND($km->SOTIENTOIDA_KM) }} @endif
                                @if ($km->NGAYKT) · HSD {{ $km->NGAYKT->format('d/m/Y') }} @endif
                            </span>
                            <div class="s-code">{{ $km->MA_CODE }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @foreach ([
        ['Sản phẩm bán chạy', $banChay, route('sanpham.index')],
        ['Laptop', $laptop, route('sanpham.index', ['loai' => [$danhMuc->firstWhere('TENLOAI', 'Laptop')?->MALOAI]])],
        ['PC & máy bộ', $pc, route('sanpham.index', ['loai' => $danhMuc->filter(fn ($l) => str_contains($l->TENLOAI, 'PC') || str_contains($l->TENLOAI, 'All-in-One'))->pluck('MALOAI')->all()])],
        ['Mới về', $moiVe, route('sanpham.index', ['sap_xep' => 'moi'])],
    ] as [$tieuDe, $ds, $xemThem])
        @if ($ds->isNotEmpty())
            <section class="s-section">
                <div class="s-section-hd">
                    <h2>{{ $tieuDe }}</h2>
                    <a href="{{ $xemThem }}">Xem tất cả →</a>
                </div>
                <div class="s-grid">
                    @foreach ($ds as $sp)
                        <x-the-san-pham :sp="$sp" />
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    {{-- Thương hiệu --}}
    @if ($thuongHieu->isNotEmpty())
        <section class="s-section">
            <div class="s-section-hd"><h2>Thương hiệu</h2></div>
            <div class="s-brands">
                @foreach ($thuongHieu as $nsx)
                    <a href="{{ route('sanpham.index', ['hang' => [$nsx->MANSX]]) }}" class="s-card s-brand">{{ $nsx->TENNSX }}</a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
