@props(['sp'])

@php($duocMua = auth()->user()?->LOAI_TAIKHOAN === 'KhachHang')
@php($conHang = $sp->conHang())
<article class="s-card p-card" data-sp="{{ $sp->MASP }}">
    <a href="{{ route('sanpham.show', $sp->MASP) }}" class="p-img" aria-label="{{ $sp->TENSP }}">
        {!! icon(str_contains($sp->TENSP, 'PC') ? 'pc' : 'laptop', 48) !!}
        @if ($url = $sp->anhUrl())
            <img src="{{ $url }}" alt="{{ $sp->TENSP }}" loading="lazy" onerror="this.remove()">
        @endif
    </a>
    <div class="p-brand">{{ $sp->nhaSanXuat?->TENNSX }}</div>
    <a href="{{ route('sanpham.show', $sp->MASP) }}" class="p-name">{{ $sp->TENSP }}</a>
    @if ($cauHinh = array_slice($sp->cauHinhTomTat(), 0, 3))
        <div class="p-specs">
            @foreach ($cauHinh as $muc)
                <span title="{{ $muc }}">{{ $muc }}</span>
            @endforeach
        </div>
    @endif
    <div class="p-price" data-sp-gia>{{ formatVND($sp->DONGIA_SP) }}</div>
    <div @class(['p-stock', 'out' => ! $conHang]) data-sp-ton>{{ $conHang ? 'Còn '.formatNum($sp->SOLUONGTON).' sản phẩm' : 'Tạm hết hàng' }}</div>

    {{-- Cả hai nút đều có sẵn, realtime bật / tắt khi tồn kho đổi --}}
    <button type="button" class="btn btn-outline btn-sm btn-block p-cta" disabled data-sp-het @if ($conHang) hidden @endif>Tạm hết hàng</button>
    @if ($duocMua)
        <form method="POST" action="{{ route('giohang.store', $sp->MASP) }}" data-sp-mua @unless ($conHang) hidden @endunless>
            @csrf
            <button type="submit" class="btn btn-primary btn-sm btn-block">{!! icon('cart', 15) !!} Thêm vào giỏ</button>
        </form>
    @else
        <a href="{{ route('login', ['tiep' => route('sanpham.show', $sp->MASP, false)]) }}" class="btn btn-outline btn-sm btn-block p-cta" data-sp-mua @unless ($conHang) hidden @endunless>Đăng nhập để mua</a>
    @endif
</article>
