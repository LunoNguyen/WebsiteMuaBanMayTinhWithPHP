<link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ filemtime(public_path('assets/css/theme.css')) }}">
{{-- Chưa chọn chế độ thì theo hệ điều hành; đặt trước khi vẽ để không nháy trắng --}}
<script>(function(){var d=document.documentElement;if(!d.dataset.theme){d.dataset.theme=(window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light';}})();</script>
<script src="{{ asset('assets/js/theme.js') }}?v={{ filemtime(public_path('assets/js/theme.js')) }}" defer></script>
