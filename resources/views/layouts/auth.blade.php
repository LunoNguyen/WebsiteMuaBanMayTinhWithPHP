@php($theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null)
<!DOCTYPE html>
<html lang="vi" @if ($theme) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | NEXUS System</title>
    @include('partials.theme-head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/auth/auth.css') }}?v={{ filemtime(public_path('assets/auth/auth.css')) }}">
</head>
<body>
<div class="theme-corner"><x-theme-toggle /></div>
<div class="page">
    <div class="card">
        <div class="logo">
            <x-logo :height="52" />
            <div class="s">Hệ thống Quản lý Bán Máy Tính</div>
        </div>

        @yield('content')
    </div>
</div>

<script>
function togglePass(id) {
    const p = document.getElementById(id);
    p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
@stack('scripts')
</body>
</html>
