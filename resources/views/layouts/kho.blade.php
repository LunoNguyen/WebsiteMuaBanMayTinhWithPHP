@php($theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null)
<!DOCTYPE html>
<html lang="vi" @if ($theme) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NEXUS WMS - Quản lý kho vận">
    <title>{{ $title }} | NEXUS WMS</title>
    @include('partials.theme-head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/kho/kho.css') }}?v={{ filemtime(public_path('assets/kho/kho.css')) }}">
    @stack('styles')
</head>
<body>
@yield('content')

<script src="{{ asset('assets/kho/kho.js') }}?v={{ filemtime(public_path('assets/kho/kho.js')) }}"></script>
@stack('scripts')
@include('partials.realtime')
</body>
</html>
