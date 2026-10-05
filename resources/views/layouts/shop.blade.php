@php($theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null)
<!DOCTYPE html>
<html lang="vi" @if ($theme) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NEXUS Computer Store - Laptop, PC, màn hình, linh kiện chính hãng">
    <title>{{ isset($title) ? $title.' | ' : '' }}NEXUS Computer Store</title>
    @include('partials.theme-head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/shop/shop.css') }}?v={{ filemtime(public_path('assets/shop/shop.css')) }}">
</head>
<body>
@include('partials.shop.header')

<main>
    <div class="wrap">
        @if (session('thong_bao'))
            <div class="s-flash">{!! icon('check-circle', 18) !!} {{ session('thong_bao') }}</div>
        @endif
        {{-- Trang có báo lỗi ngay dưới từng ô (đăng nhập, đăng ký) thì không lặp lại ở đây --}}
        @if ($errors->any() && empty($anFlashLoi))
            <div class="s-flash err">{!! icon('alert', 18) !!} {{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>
</main>

@include('partials.shop.footer')
@include('partials.shop.chatbot')

<script src="{{ asset('assets/shop/shop.js') }}?v={{ filemtime(public_path('assets/shop/shop.js')) }}"></script>
@stack('scripts')
@include('partials.realtime')
</body>
</html>
