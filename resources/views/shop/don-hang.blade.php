@extends('layouts.shop', ['title' => 'Đơn hàng của tôi'])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Đơn hàng của tôi</span></nav>

    <div class="s-account">
        @include('partials.shop.tai-khoan-menu')

        <div>
            <section class="s-card s-panel" style="padding-bottom:8px" data-rt-vung="ds-don" data-rt-khi="don">
                <h2>Đơn hàng của tôi</h2>
                <p>Theo dõi trạng thái và chi tiết các đơn đã đặt.</p>

                @if ($donHang->isEmpty())
                    <div class="s-empty">
                        {!! icon('receipt', 40) !!}
                        <p>Bạn chưa có đơn hàng nào.</p>
                        <a href="{{ route('sanpham.index') }}" class="btn btn-primary">Mua sắm ngay</a>
                    </div>
                @else
                    <div style="overflow-x:auto;margin:0 -24px">
                        <table class="s-orders">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th class="hide-sm">Ngày đặt</th>
                                    <th class="hide-sm">Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="hide-sm">Thanh toán</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($donHang as $hd)
                                    <tr>
                                        <td><strong>{{ $hd->MAHD }}</strong></td>
                                        <td class="hide-sm">{{ $hd->NGAYLAP?->format('d/m/Y H:i') }}</td>
                                        <td class="hide-sm">{{ $hd->chi_tiets_count }}</td>
                                        <td><strong>{{ formatVND($hd->TONGTIEN_HD) }}</strong></td>
                                        <td>{!! statusBadge($hd->TRANGTHAI) !!}</td>
                                        <td class="hide-sm">{!! $hd->thanhToan ? statusBadge($hd->thanhToan->TRANGTHAI, 'thanhtoan') : '—' !!}</td>
                                        <td><a href="{{ route('donhang.show', $hd->MAHD) }}" class="btn btn-outline btn-sm">Xem</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($donHang->lastPage() > 1)
                        <nav class="s-pager" aria-label="Phân trang">
                            @foreach ($donHang->getUrlRange(1, $donHang->lastPage()) as $so => $url)
                                @if ($so === $donHang->currentPage())
                                    <span class="on">{{ $so }}</span>
                                @else
                                    <a href="{{ $url }}">{{ $so }}</a>
                                @endif
                            @endforeach
                        </nav>
                    @endif
                @endif
            </section>
        </div>
    </div>
@endsection
