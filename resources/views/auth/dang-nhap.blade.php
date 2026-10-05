@extends('layouts.shop', ['title' => 'Đăng nhập', 'anFlashLoi' => true])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Đăng nhập</span></nav>

    <div class="s-auth">
        <section class="s-card s-auth-card">
            <h1>Đăng nhập</h1>
            <p class="s-auth-sub">Dùng email và mật khẩu đã đăng ký. Nhân viên đăng nhập tại đây để vào trang quản lý.</p>

            <form method="POST" action="{{ route('login.store') }}" class="s-form" autocomplete="on">
                @csrf
                @if (request('tiep'))
                    <input type="hidden" name="tiep" value="{{ request('tiep') }}">
                @endif

                <div class="s-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           @class(['s-input', 'is-invalid' => $errors->has('email')]) placeholder="email@example.com">
                    @error('email') <div class="s-err">{{ $message }}</div> @enderror
                </div>

                <div class="s-field">
                    <label for="password">Mật khẩu</label>
                    <div class="s-pass">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               @class(['s-input', 'is-invalid' => $errors->has('password')])>
                        <button type="button" data-hien-mk="password" aria-label="Hiện mật khẩu">{!! icon('eye', 18) !!}</button>
                    </div>
                    @error('password') <div class="s-err">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Đăng nhập</button>
            </form>

            <p class="s-auth-switch">Chưa có tài khoản? <a href="{{ route('register', array_filter(['tiep' => request('tiep')])) }}">Đăng ký ngay</a></p>
        </section>

        @include('partials.shop.loi-ich-tai-khoan')
    </div>
@endsection
