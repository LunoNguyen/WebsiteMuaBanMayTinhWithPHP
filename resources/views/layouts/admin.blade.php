@php($theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null)
<!DOCTYPE html>
<html lang="vi" @if ($theme) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Hệ thống quản trị bán máy tính - {{ $title ?? 'Quản trị' }}">
    <title>{{ $title ?? 'Quản trị' }} | NEXUS Quản trị</title>

    @include('partials.theme-head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link rel="stylesheet" href="{{ asset('assets/admin/admin.css') }}?v={{ filemtime(public_path('assets/admin/admin.css')) }}">
    @stack('styles')
</head>
<body>
<div style="display:flex;min-height:100vh">
    @include('partials.quan-tri.sidebar')

    <div class="main-wrapper">
        @include('partials.quan-tri.topbar', ['breadcrumb' => $breadcrumb ?? [$title ?? 'Trang chủ']])

        <main class="page-content">
            @yield('content')
        </main>
    </div>
</div>

<div id="toast-container"></div>
<script src="{{ asset('assets/admin/admin.js') }}?v={{ filemtime(public_path('assets/admin/admin.js')) }}"></script>
@stack('scripts')
@include('partials.realtime')
</body>
</html>
