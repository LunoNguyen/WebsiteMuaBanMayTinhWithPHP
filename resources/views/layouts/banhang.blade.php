@php($theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null)
<!DOCTYPE html>
<html lang="vi" @if ($theme) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NEXUS Sales - Bán hàng & CRM">
    <title>{{ $title }} | NEXUS Sales</title>
    @include('partials.theme-head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/banhang/banhang.css') }}?v={{ filemtime(public_path('assets/banhang/banhang.css')) }}">
    @stack('styles')
</head>
<body>
@yield('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/vn.min.js"></script>
<script src="{{ asset('assets/banhang/banhang.js') }}?v={{ filemtime(public_path('assets/banhang/banhang.js')) }}"></script>
@stack('scripts')
</body>
</html>
