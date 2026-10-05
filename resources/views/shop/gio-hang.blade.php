@extends('layouts.shop', ['title' => 'Giỏ hàng'])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Giỏ hàng</span></nav>

    @if ($dong->isEmpty())
        <div class="s-card s-empty">
            {!! icon('cart', 44) !!}
            <h2>Giỏ hàng của bạn đang trống</h2>
            <p>Xem các sản phẩm đang bán và chọn món bạn cần nhé.</p>
            <a href="{{ route('sanpham.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="s-cart" data-rt-vung="gio" data-rt-khi="{{ $dong->map(fn ($d) => 'sp:'.$d->MASP)->implode(' ') }}">
            <div class="s-card">
                @foreach ($dong as $d)
                    @php($sp = $d->sanPham)
                    <div class="s-line">
                        <a href="{{ route('sanpham.show', $sp->MASP) }}" class="s-line-img" aria-label="{{ $sp->TENSP }}">
                            {!! icon(str_contains($sp->TENSP, 'PC') ? 'pc' : 'laptop', 32) !!}
                            @if ($url = $sp->anhUrl())
                                <img src="{{ $url }}" alt="" loading="lazy" onerror="this.remove()">
                            @endif
                        </a>
                        <div>
                            <a href="{{ route('sanpham.show', $sp->MASP) }}" class="s-line-name">{{ $sp->TENSP }}</a>
                            <div class="s-line-sub">{{ $sp->nhaSanXuat?->TENNSX }} · Đơn giá {{ formatVND($sp->DONGIA_SP) }}</div>
                            @if ($d->SOLUONG > $sp->SOLUONGTON)
                                <div class="s-err">Chỉ còn {{ formatNum($sp->SOLUONGTON) }} sản phẩm trong kho.</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('giohang.update', $d->MACTGH) }}">
                            @csrf
                            @method('PATCH')
                            <div class="s-qty" data-qty data-auto-submit>
                                <button type="button" data-minus aria-label="Bớt">−</button>
                                <input type="number" name="so_luong" value="{{ $d->SOLUONG }}" min="1" max="{{ max(1, min(99, $sp->SOLUONGTON)) }}" aria-label="Số lượng">
                                <button type="button" data-plus aria-label="Thêm">+</button>
                            </div>
                        </form>
                        <div class="s-line-total">{{ formatVND($sp->DONGIA_SP * $d->SOLUONG) }}</div>
                        <x-nut-hanh-dong :action="route('giohang.destroy', $d->MACTGH)" method="DELETE" class="btn btn-ghost btn-icon" title="Xoá khỏi giỏ"
                                         confirm="Bỏ {{ $sp->TENSP }} khỏi giỏ hàng?">{!! icon('trash', 16) !!}</x-nut-hanh-dong>
                    </div>
                @endforeach
            </div>

            <aside class="s-card s-sum">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="s-sum-row"><span>Tạm tính ({{ $dong->sum('SOLUONG') }} sản phẩm)</span><strong>{{ formatVND($tamTinh) }}</strong></div>

                @if ($khuyenMai)
                    <div class="s-applied">
                        <span>{!! icon('tag', 15) !!} <strong>{{ $khuyenMai->MA_CODE }}</strong> · −{{ formatVND($giam) }}</span>
                        <x-nut-hanh-dong :action="route('giohang.bo-ma')" method="DELETE" class="btn btn-ghost btn-sm">Bỏ mã</x-nut-hanh-dong>
                    </div>
                @else
                    <form method="POST" action="{{ route('giohang.ap-ma') }}" class="s-coupon">
                        @csrf
                        <input class="s-input" name="ma_code" placeholder="Nhập mã giảm giá" value="{{ old('ma_code') }}" aria-label="Mã giảm giá" required>
                        <button type="submit" class="btn btn-outline">Áp dụng</button>
                    </form>
                    @if ($loi = session('loi_ma') ?? $errors->first('ma_code'))
                        <div class="s-err">{{ $loi }}</div>
                    @endif
                    @if ($goiY->isNotEmpty())
                        <div class="s-coupons">
                            <div class="s-hint">Mã bạn có thể dùng (mỗi đơn một mã):</div>
                            @foreach ($goiY->take(4) as $km)
                                <div class="s-coupon-item">
                                    <span class="s-code">{{ $km->MA_CODE }}</span>
                                    <span>{{ $km->TENKM }}</span>
                                    <small>{{ implode(' · ', $dieuKien($km)) }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                <div class="s-sum-row"><span>Giảm giá</span><strong>{{ $giam > 0 ? '−'.formatVND($giam) : formatVND(0) }}</strong></div>
                <div class="s-sum-row"><span>Phí vận chuyển</span><strong>Miễn phí</strong></div>
                <div class="s-sum-total"><span>Tổng cộng</span><strong>{{ formatVND($tong) }}</strong></div>

                <a href="{{ route('thanhtoan.create') }}" class="btn btn-primary btn-lg btn-block" style="margin-top:16px">Tiến hành đặt hàng</a>
                <a href="{{ route('sanpham.index') }}" class="btn btn-ghost btn-block" style="margin-top:6px">Tiếp tục mua sắm</a>
            </aside>
        </div>
    @endif
@endsection
