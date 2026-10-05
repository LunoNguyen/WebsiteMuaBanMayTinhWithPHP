@extends('layouts.shop', ['title' => 'Tài khoản của tôi'])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Tài khoản</span></nav>

    <div class="s-account">
        @include('partials.shop.tai-khoan-menu')

        <div>
            <section class="s-card s-panel">
                <h2>Thông tin cá nhân</h2>
                <p>Dùng để điền sẵn khi đặt hàng. Email đăng nhập: <strong>{{ $taiKhoan->EMAIL_TK }}</strong></p>

                <form method="POST" action="{{ route('taikhoan.update') }}" class="s-form">
                    @csrf
                    @method('PUT')
                    <div class="row2">
                        <div class="s-field">
                            <label for="ten">Họ tên <span class="req">*</span></label>
                            <input class="s-input" id="ten" name="TENKH" value="{{ old('TENKH', $khachHang->TENKH) }}" required maxlength="100" autocomplete="name">
                            @error('TENKH') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                        <div class="s-field">
                            <label for="sdt">Số điện thoại</label>
                            <input class="s-input" id="sdt" name="SDT_KH" value="{{ old('SDT_KH', $khachHang->SDT_KH) }}" inputmode="tel" autocomplete="tel">
                            @error('SDT_KH') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row2">
                        <div class="s-field">
                            <label for="ngaysinh">Ngày sinh</label>
                            <input class="s-input" type="date" id="ngaysinh" name="NGAYSINH" value="{{ old('NGAYSINH', $khachHang->NGAYSINH?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}">
                            @error('NGAYSINH') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                        <div class="s-field">
                            <label for="gioitinh">Giới tính</label>
                            @php($gt = old('GIOITINH', $khachHang->GIOITINH === null ? '' : (int) $khachHang->GIOITINH))
                            <select class="s-select" id="gioitinh" name="GIOITINH" style="width:100%">
                                <option value="" @selected($gt === '')>Không muốn nêu</option>
                                <option value="1" @selected((string) $gt === '1')>Nam</option>
                                <option value="0" @selected((string) $gt === '0')>Nữ</option>
                            </select>
                            @error('GIOITINH') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="s-field">
                        <label for="diachi">Địa chỉ</label>
                        <input class="s-input" id="diachi" name="DIACHI_KH" value="{{ old('DIACHI_KH', $khachHang->DIACHI_KH) }}" maxlength="200" autocomplete="street-address">
                        @error('DIACHI_KH') <div class="s-err">{{ $message }}</div> @enderror
                    </div>
                    <div><button type="submit" class="btn btn-primary">Lưu thay đổi</button></div>
                </form>
            </section>

            <section class="s-card s-panel">
                <h2>Đổi mật khẩu</h2>
                <p>Mật khẩu mới từ 6 ký tự.</p>

                <form method="POST" action="{{ route('taikhoan.mat-khau') }}" class="s-form" style="max-width:420px">
                    @csrf
                    @method('PUT')
                    <div class="s-field">
                        <label for="mkcu">Mật khẩu hiện tại</label>
                        <input class="s-input" type="password" id="mkcu" name="mat_khau_cu" required autocomplete="current-password">
                        @error('mat_khau_cu', 'doiMatKhau') <div class="s-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="s-field">
                        <label for="mkmoi">Mật khẩu mới</label>
                        <input class="s-input" type="password" id="mkmoi" name="password" required minlength="6" maxlength="72" autocomplete="new-password">
                        @error('password', 'doiMatKhau') <div class="s-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="s-field">
                        <label for="mkxn">Nhập lại mật khẩu mới</label>
                        <input class="s-input" type="password" id="mkxn" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <div><button type="submit" class="btn btn-outline">Đổi mật khẩu</button></div>
                </form>
            </section>
        </div>
    </div>
@endsection
