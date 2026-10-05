<?php

use App\Enums\VaiTro;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Kênh realtime
|--------------------------------------------------------------------------
|
| "cua-hang" là kênh công khai (giá, tồn kho), không cần khai ở đây.
|
*/

// Nhân viên (quản trị, bán hàng, kho): đơn mới, đơn đổi trạng thái, tồn kho cho dashboard
Broadcast::channel('quan-tri', function (TaiKhoan $taiKhoan): bool {
    $vaiTro = $taiKhoan->vaiTro();

    return $vaiTro !== null && $vaiTro !== VaiTro::KhachHang;
});

// Khách chỉ nghe được đơn hàng của chính tài khoản mình
Broadcast::channel('don-hang.{maTaiKhoan}', function (TaiKhoan $taiKhoan, string $maTaiKhoan): bool {
    return $taiKhoan->MATK === $maTaiKhoan;
});
