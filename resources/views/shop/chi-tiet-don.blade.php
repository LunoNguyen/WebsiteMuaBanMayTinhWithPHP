@extends('layouts.shop', ['title' => 'Đơn '.$hd->MAHD])

@php
    $buoc = ['ChoXacNhan' => 'Chờ xác nhận', 'DaXacNhan' => 'Đã xác nhận', 'DangGiao' => 'Đang giao', 'DaGiao' => 'Đã giao', 'HoanThanh' => 'Hoàn thành'];
    $viTri = array_search($hd->TRANGTHAI, array_keys($buoc), true);
    $tt = $hd->thanhToan;
    $nh = config('cuahang.ngan_hang');
    $canChuyenKhoan = $tt && $tt->PHUONG_THUC !== 'COD' && $tt->TRANGTHAI === 'ChoThanhToan' && $hd->TRANGTHAI !== 'DaHuy';
@endphp

@section('content')
    <nav class="s-crumb">
        <a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!}
        <a href="{{ route('donhang.index') }}">Đơn hàng của tôi</a> {!! icon('chevron-right', 14) !!}
        <span>{{ $hd->MAHD }}</span>
    </nav>

    <div class="s-account">
        @include('partials.shop.tai-khoan-menu')

        <div data-rt-vung="don" data-rt-khi="don:{{ $hd->MAHD }}">
            <section class="s-card s-panel">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center">
                    <div>
                        <h2>Đơn hàng {{ $hd->MAHD }}</h2>
                        <span class="s-hint">Đặt lúc {{ $hd->NGAYLAP?->format('H:i d/m/Y') }}</span>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <a href="{{ route('donhang.in', $hd->MAHD) }}" target="_blank" style="padding:4px 12px;border:1px solid #ccc;border-radius:20px;text-decoration:none;color:#555;font-size:14px;background:#f9f9f9">🖨️ In hóa đơn</a>
                        {!! statusBadge($hd->TRANGTHAI) !!}
                        @if ($hd->TRANGTHAI === 'ChoXacNhan')
                            <x-nut-hanh-dong :action="route('donhang.huy', $hd->MAHD)" method="PATCH" class="btn btn-danger btn-sm"
                                             confirm="Huỷ đơn {{ $hd->MAHD }}? Thao tác này không hoàn tác được.">Huỷ đơn</x-nut-hanh-dong>
                        @endif
                    </div>
                </div>

                @if ($hd->TRANGTHAI === 'DaHuy')
                    <div class="s-flash err" style="margin:16px 0 0">Đơn hàng đã bị huỷ.</div>
                @else
                    <div class="s-steps">
                        @foreach (array_values($buoc) as $i => $nhan)
                            <div @class(['s-step', 'done' => $viTri !== false && $i <= $viTri])>{{ $nhan }}</div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="s-card s-panel">
                <h2>Sản phẩm</h2>
                <p>{{ $hd->chiTiets->sum('SOLUONG') }} sản phẩm</p>
                @foreach ($hd->chiTiets as $ct)
                    <div class="s-mini" style="border-top:1px solid var(--border);padding:12px 0">
                        <span class="s-line-img">{!! icon(str_contains((string) $ct->sanPham?->TENSP, 'PC') ? 'pc' : 'laptop', 22) !!}</span>
                        <span style="flex:1;min-width:0">
                            @if ($ct->sanPham)
                                <a href="{{ route('sanpham.show', $ct->MASP) }}" class="s-line-name">{{ $ct->sanPham->TENSP }}</a>
                            @else
                                {{ $ct->MASP }}
                            @endif
                            <div class="s-line-sub">{{ formatVND($ct->DONGIA_LUCAT) }} × {{ $ct->SOLUONG }}</div>
                        </span>
                        <strong>{{ formatVND($ct->THANHTIEN) }}</strong>
                    </div>
                @endforeach
                <div style="border-top:1px solid var(--border);padding-top:8px;max-width:360px;margin-left:auto">
                    <div class="s-sum-row"><span>Tạm tính</span><strong>{{ formatVND($hd->TONGTIEN_TRUOCGIAM) }}</strong></div>
                    <div class="s-sum-row"><span>Giảm giá</span><strong>−{{ formatVND($hd->TONGTIEN_GIAM) }}</strong></div>
                    <div class="s-sum-total"><span>Tổng cộng</span><strong>{{ formatVND($hd->TONGTIEN_HD) }}</strong></div>
                </div>
            </section>

            <div class="s-form" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:16px">
                <section class="s-card s-panel" style="margin:0">
                    <h2>Nhận hàng</h2>
                    <p>{{ $hd->PHUONG_THUC_GH === 'TaiQuay' ? 'Nhận tại cửa hàng' : 'Giao tận nơi' }}</p>
                    <div class="s-perks" style="margin:0">
                        <div>{!! icon('user', 16) !!} {{ $hd->TEN_NGUOINHAN }}</div>
                        <div>{!! icon('phone', 16) !!} {{ $hd->SDT_NGUOINHAN }}</div>
                        @if ($hd->DIACHI_GIAOHANG)
                            <div>{!! icon('map-pin', 16) !!} {{ $hd->DIACHI_GIAOHANG }}</div>
                        @endif
                        @if ($hd->GHI_CHU)
                            <div>{!! icon('message', 16) !!} {{ $hd->GHI_CHU }}</div>
                        @endif
                    </div>
                </section>

                <section class="s-card s-panel" style="margin:0">
                    <h2>Thanh toán</h2>
                    @if ($tt)
                        <p>{{ \App\Http\Controllers\Shop\ThanhToanController::PHUONG_THUC[$tt->PHUONG_THUC] ?? $tt->PHUONG_THUC }}</p>
                        {!! statusBadge($tt->TRANGTHAI, 'thanhtoan') !!}
                        @if ($canChuyenKhoan)
                            <div class="s-bank">
                                @if ($tt->PHUONG_THUC === 'QR')
                                    <img src="https://img.vietqr.io/image/{{ $nh['bin'] }}-{{ $nh['so_tai_khoan'] }}-compact.png?amount={{ (int) $hd->TONGTIEN_HD }}&addInfo={{ rawurlencode($tt->NOI_DUNG_CK) }}&accountName={{ rawurlencode($nh['chu_tai_khoan']) }}"
                                         alt="Mã QR chuyển khoản" width="180" height="180" style="background:#fff;border-radius:8px;margin-bottom:8px" onerror="this.remove()">
                                @endif
                                <div>Ngân hàng: <strong>{{ $nh['ten'] }}</strong></div>
                                <div>Số tài khoản: <strong>{{ $nh['so_tai_khoan'] }}</strong></div>
                                <div>Chủ tài khoản: <strong>{{ $nh['chu_tai_khoan'] }}</strong></div>
                                <div>Số tiền: <strong>{{ formatVND($hd->TONGTIEN_HD) }}</strong></div>
                                <div>Nội dung: <span class="s-code" style="margin:0">{{ $tt->NOI_DUNG_CK }}</span></div>
                            </div>
                        @endif
                    @else
                        <p>Chưa có thông tin thanh toán.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection
