{{--
    Realtime qua Laravel Reverb (WebSocket). Đặt cuối <body> của mọi layout.
    Reverb chưa chạy thì trang vẫn dùng bình thường, chỉ không tự cập nhật.
--}}
@if (config('cuahang.realtime.bat'))
    @php
        $taiKhoan = auth()->user();
        $vaiTro = $taiKhoan?->vaiTro();
        $cauHinhRt = [
            'key' => config('cuahang.realtime.key'),
            // "localhost" trên Windows hay thử IPv6 (::1) trước rồi mới về IPv4; Reverb chỉ nghe IPv4 nên nối thẳng 127.0.0.1
            'host' => config('cuahang.realtime.host') ?? (request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost()),
            'port' => config('cuahang.realtime.port'),
            'tls' => (config('cuahang.realtime.scheme') ?? (request()->secure() ? 'https' : 'http')) === 'https',
            'nhanVien' => $vaiTro !== null && $vaiTro !== \App\Enums\VaiTro::KhachHang,
            'kenhDon' => $vaiTro === \App\Enums\VaiTro::KhachHang ? 'don-hang.'.$taiKhoan->MATK : null,
        ];
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/realtime.css') }}?v={{ filemtime(public_path('assets/css/realtime.css')) }}">
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.1.6/dist/echo.iife.js" defer></script>
    <script>window.NEXUS_REALTIME = @json($cauHinhRt);</script>
    <script src="{{ asset('assets/js/realtime.js') }}?v={{ filemtime(public_path('assets/js/realtime.js')) }}" defer></script>
@endif
