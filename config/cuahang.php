<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tài khoản nhận chuyển khoản
    |--------------------------------------------------------------------------
    |
    | Hiện ở trang đơn hàng khi khách chọn Chuyển khoản / QR. Mã QR dựng theo
    | chuẩn VietQR (img.vietqr.io) từ mã BIN ngân hàng và số tài khoản.
    |
    */

    'ngan_hang' => [
        'ten' => env('CUAHANG_NGAN_HANG', 'Vietcombank'),
        'bin' => env('CUAHANG_NGAN_HANG_BIN', '970436'),
        'so_tai_khoan' => env('CUAHANG_SO_TAI_KHOAN', '0123456789'),
        'chu_tai_khoan' => env('CUAHANG_CHU_TAI_KHOAN', 'CONG TY NEXUS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Realtime (Laravel Reverb)
    |--------------------------------------------------------------------------
    |
    | Địa chỉ WebSocket trình duyệt kết nối tới. Để trống host thì dùng chính
    | tên miền của trang và cổng REVERB_PORT (chạy trên máy: nhom04.test:8080).
    | Chạy qua Cloudflare Tunnel thì mở thêm một tunnel cho cổng 8080 rồi điền
    | tên miền của tunnel đó, cổng 443, scheme https.
    |
    */

    'realtime' => [
        'bat' => env('BROADCAST_CONNECTION') === 'reverb' && filled(env('REVERB_APP_KEY')),
        'key' => env('REVERB_APP_KEY'),
        'host' => env('REVERB_PUBLIC_HOST') ?: null,
        'port' => (int) (env('REVERB_PUBLIC_PORT') ?: env('REVERB_PORT', 8080)),
        'scheme' => env('REVERB_PUBLIC_SCHEME') ?: null,
    ],

];
