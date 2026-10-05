@extends('layouts.shop', ['title' => 'Thanh toán'])

@php($giaoHang = old('PHUONG_THUC_GH', 'GiaoHang'))

@section('content')
    <nav class="s-crumb">
        <a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!}
        <a href="{{ route('giohang.index') }}">Giỏ hàng</a> {!! icon('chevron-right', 14) !!}
        <span>Thanh toán</span>
    </nav>

    <form method="POST" action="{{ route('thanhtoan.store') }}" class="s-cart">
        @csrf
        <div>
            <section class="s-card s-panel">
                <h2>Thông tin nhận hàng</h2>
                <p>Đã điền sẵn từ hồ sơ của bạn, có thể sửa riêng cho đơn này.</p>
                <div class="s-form">
                    <div class="row2">
                        <div class="s-field">
                            <label for="ten">Họ tên người nhận <span class="req">*</span></label>
                            <input class="s-input" id="ten" name="TEN_NGUOINHAN" value="{{ old('TEN_NGUOINHAN', $khachHang?->TENKH) }}" required maxlength="100" autocomplete="name">
                            @error('TEN_NGUOINHAN') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                        <div class="s-field">
                            <label for="sdt">Số điện thoại <span class="req">*</span></label>
                            <input class="s-input" id="sdt" name="SDT_NGUOINHAN" value="{{ old('SDT_NGUOINHAN', $khachHang?->SDT_KH) }}" required inputmode="tel" autocomplete="tel">
                            @error('SDT_NGUOINHAN') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="s-field">
                        <label>Hình thức nhận hàng</label>
                        <div class="s-choice">
                            <label><input type="radio" name="PHUONG_THUC_GH" value="GiaoHang" @checked($giaoHang === 'GiaoHang') data-giao-hang>
                                <span>Giao tận nơi <small>Miễn phí giao hàng toàn quốc</small></span></label>
                            <label><input type="radio" name="PHUONG_THUC_GH" value="TaiQuay" @checked($giaoHang === 'TaiQuay') data-giao-hang>
                                <span>Nhận tại cửa hàng <small>Cửa hàng giữ hàng trong 3 ngày</small></span></label>
                        </div>
                    </div>

                    <div class="s-field" id="oDiaChi" @if ($giaoHang === 'TaiQuay') hidden @endif>
                        <label for="diachi">Địa chỉ giao hàng <span class="req">*</span></label>
                        <input class="s-input" id="diachi" name="DIACHI_GIAOHANG" value="{{ old('DIACHI_GIAOHANG', $khachHang?->DIACHI_KH) }}" maxlength="300" autocomplete="street-address">
                        @error('DIACHI_GIAOHANG') <div class="s-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="s-field">
                        <label for="ghichu">Ghi chú</label>
                        <textarea class="s-textarea" id="ghichu" name="GHI_CHU" rows="2" maxlength="500" placeholder="Ví dụ: giao giờ hành chính">{{ old('GHI_CHU') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="s-card s-panel">
                <h2>Phương thức thanh toán</h2>
                <p>Với chuyển khoản / QR, thông tin tài khoản hiện ở trang đơn hàng sau khi đặt.</p>
                <div class="s-choice">
                    @foreach ($phuongThuc as $ma => $nhan)
                        <label><input type="radio" name="PHUONG_THUC" value="{{ $ma }}" @checked(old('PHUONG_THUC', 'COD') === $ma)> <span>{{ $nhan }}</span></label>
                    @endforeach
                </div>
                @error('PHUONG_THUC') <div class="s-err">{{ $message }}</div> @enderror
            </section>
        </div>

        <aside class="s-card s-sum">
            <h3>Đơn hàng ({{ $dong->sum('SOLUONG') }} sản phẩm)</h3>
            @foreach ($dong as $d)
                <div class="s-mini">
                    <span class="s-line-img">{!! icon(str_contains($d->sanPham->TENSP, 'PC') ? 'pc' : 'laptop', 22) !!}</span>
                    <span style="flex:1;min-width:0">{{ $d->sanPham->TENSP }} <span class="s-hint">× {{ $d->SOLUONG }}</span></span>
                    <strong>{{ formatVND($d->sanPham->DONGIA_SP * $d->SOLUONG) }}</strong>
                </div>
            @endforeach

            <div style="border-top:1px solid var(--border);margin-top:8px;padding-top:8px">
                <div class="s-sum-row"><span>Tạm tính</span><strong>{{ formatVND($tamTinh) }}</strong></div>
                <div class="s-sum-row">
                    <span>Giảm giá @if ($khuyenMai) <span class="s-code">{{ $khuyenMai->MA_CODE }}</span> @endif</span>
                    <strong>{{ $giam > 0 ? '−'.formatVND($giam) : formatVND(0) }}</strong>
                </div>
                <div class="s-sum-row"><span>Phí vận chuyển</span><strong>Miễn phí</strong></div>
                <div class="s-sum-total"><span>Tổng thanh toán</span><strong>{{ formatVND($tong) }}</strong></div>
            </div>
            @error('ma_code') <div class="s-err">{{ $message }}</div> @enderror
            @error('so_luong') <div class="s-err">{{ $message }}</div> @enderror

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:16px">Đặt hàng</button>
            <a href="{{ route('giohang.index') }}" class="btn btn-ghost btn-block" style="margin-top:6px">Quay lại giỏ hàng</a>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-giao-hang]').forEach(function (r) {
            r.addEventListener('change', function () {
                var tanNoi = document.querySelector('[data-giao-hang]:checked').value === 'GiaoHang';
                document.getElementById('oDiaChi').hidden = !tanNoi;
                document.getElementById('diachi').required = tanNoi;
            });
        });
        document.getElementById('diachi').required = !document.getElementById('oDiaChi').hidden;
    </script>
@endpush
