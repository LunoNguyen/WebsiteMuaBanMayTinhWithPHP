@extends('layouts.shop', ['title' => $sp->TENSP])

@php
    $duocMua = auth()->user()?->LOAI_TAIKHOAN === 'KhachHang';
    $anhUrls = $sp->anhs->map(fn ($a) => app(\App\Services\MinioStorage::class)->url($a->URL_ANH))->filter()->values();
    $thongSo = array_filter([
        'Thương hiệu' => $sp->nhaSanXuat?->TENNSX,
        'Loại sản phẩm' => $sp->loaiSanPham?->TENLOAI,
        'CPU' => $sp->moTa?->CPU,
        'RAM' => $sp->moTa?->RAM,
        'Ổ cứng' => $sp->moTa?->ROM,
        'Màn hình' => $sp->moTa?->MANHINH,
        'Card đồ hoạ' => $sp->moTa?->VGA,
        'Pin' => $sp->moTa?->PIN,
        'Khác' => $sp->moTa?->KHAC,
        'Đơn vị tính' => $sp->DONVT,
    ]);
@endphp

@section('content')
    <nav class="s-crumb">
        <a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!}
        <a href="{{ route('sanpham.index', ['loai' => [$sp->MALOAI]]) }}">{{ $sp->loaiSanPham?->TENLOAI }}</a> {!! icon('chevron-right', 14) !!}
        <span>{{ $sp->TENSP }}</span>
    </nav>

    <div class="s-card s-detail" data-sp="{{ $sp->MASP }}">
        <div>
            <div class="s-gallery-main">
                {!! icon(str_contains($sp->TENSP, 'PC') ? 'pc' : 'laptop', 96) !!}
                @if ($anhUrls->isNotEmpty())
                    <img id="anhChinh" src="{{ $anhUrls->first() }}" alt="{{ $sp->TENSP }}" onerror="this.remove()">
                @endif
            </div>
            @if ($anhUrls->count() > 1)
                <div class="s-thumbs">
                    @foreach ($anhUrls as $i => $url)
                        <button type="button" data-thumb="{{ $url }}" @class(['on' => $i === 0]) aria-label="Ảnh {{ $i + 1 }}">
                            <img src="{{ $url }}" alt="" loading="lazy" onerror="this.remove()">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h1>{{ $sp->TENSP }}</h1>
            <div class="s-meta">
                <span>Mã: <strong>{{ $sp->MASP }}</strong></span>
                <span>Thương hiệu: <strong>{{ $sp->nhaSanXuat?->TENNSX }}</strong></span>
                <span data-sp-ton>{{ $sp->conHang() ? 'Còn '.formatNum($sp->SOLUONGTON).' sản phẩm' : 'Tạm hết hàng' }}</span>
            </div>

            <div class="s-big-price" data-sp-gia>{{ formatVND($sp->DONGIA_SP) }}</div>

            @if ($cauHinh = $sp->cauHinhTomTat())
                <div class="p-specs" style="margin-top:12px">
                    @foreach ($cauHinh as $muc)
                        <span>{{ $muc }}</span>
                    @endforeach
                </div>
            @endif

            <div class="s-buy" data-sp-het @if ($sp->conHang()) hidden @endif><button type="button" class="btn btn-outline btn-lg" disabled>Tạm hết hàng</button></div>
            @if ($duocMua)
                <form method="POST" action="{{ route('giohang.store', $sp->MASP) }}" class="s-buy" data-sp-mua @unless ($sp->conHang()) hidden @endunless>
                    @csrf
                    <div class="s-qty" data-qty>
                        <button type="button" data-minus aria-label="Bớt">−</button>
                        <input type="number" name="so_luong" value="1" min="1" max="{{ max(1, min(99, $sp->SOLUONGTON)) }}" aria-label="Số lượng" data-sp-so-luong>
                        <button type="button" data-plus aria-label="Thêm">+</button>
                    </div>
                    <button type="submit" class="btn btn-outline btn-lg">{!! icon('cart', 18) !!} Thêm vào giỏ</button>
                    <button type="submit" name="mua_ngay" value="1" class="btn btn-primary btn-lg">Mua ngay</button>
                </form>
            @else
                <div class="s-buy" data-sp-mua @unless ($sp->conHang()) hidden @endunless>
                    <a href="{{ route('login', ['tiep' => request()->getRequestUri()]) }}" class="btn btn-primary btn-lg">Đăng nhập để mua</a>
                    <span class="s-hint">Chưa có tài khoản? <a href="{{ route('register') }}" style="color:var(--blue)">Đăng ký</a></span>
                </div>
            @endif

            <div class="s-perks">
                <div>{!! icon('shield', 16) !!} Bảo hành chính hãng</div>
                <div>{!! icon('truck', 16) !!} Giao hàng toàn quốc, hoặc nhận tại cửa hàng</div>
                <div>{!! icon('refresh', 16) !!} Đổi mới trong 7 ngày nếu lỗi nhà sản xuất</div>
            </div>
        </div>
    </div>

    @if ($thongSo)
        <section class="s-section">
            <div class="s-section-hd"><h2>Thông số kỹ thuật</h2></div>
            <div class="s-card" style="overflow:hidden">
                <table class="s-specs">
                    @foreach ($thongSo as $ten => $giaTri)
                        <tr><th>{{ $ten }}</th><td>{{ $giaTri }}</td></tr>
                    @endforeach
                </table>
            </div>
        </section>
    @endif

    @if ($lienQuan->isNotEmpty())
        <section class="s-section">
            <div class="s-section-hd"><h2>Sản phẩm cùng loại</h2></div>
            <div class="s-grid">
                @foreach ($lienQuan as $khac)
                    <x-the-san-pham :sp="$khac" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
