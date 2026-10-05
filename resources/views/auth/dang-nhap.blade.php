@extends('layouts.auth', ['title' => 'Đăng nhập'])

@section('content')
    <div class="hd">
        <h1>Chào mừng trở lại</h1>
        <p>Đăng nhập để truy cập theo vai trò của bạn</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-err">{!! icon('x') !!} {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" id="loginForm" autocomplete="off">
        @csrf
        <div class="fg">
            <label for="email">Email đăng nhập</label>
            <div class="iw">
                <span class="iw-ico">{!! icon('mail') !!}</span>
                <input type="email" id="email" name="email" placeholder="email@phongvu.com"
                       value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <div class="fg">
            <label for="password">Mật khẩu</label>
            <div class="iw">
                <span class="iw-ico">{!! icon('lock') !!}</span>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <button type="button" class="eye" onclick="togglePass('password')" title="Hiện/Ẩn mật khẩu">{!! icon('eye') !!}</button>
            </div>
        </div>

        <button type="submit" class="btn-sub">Đăng nhập vào hệ thống</button>
    </form>

    <div class="divider">hoặc</div>
    <div class="bottom-link">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay →</a>
    </div>
@endsection
