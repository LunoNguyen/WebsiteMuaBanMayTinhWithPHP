<?php
// ==============================================================
// Cấu hình chung toàn hệ thống
// ==============================================================

define('SITE_NAME', 'NEXUS System');
define('SITE_VERSION', '2.0');
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', BASE_PATH . '/admin');

// Đường dẫn URL - Laragon chạy port 80
define('BASE_URL', 'http://localhost/Nhom04');
define('ADMIN_URL', BASE_URL . '/admin');
define('KHO_URL',   BASE_URL . '/kho');
define('NVB_URL',   BASE_URL . '/nvbanhang');

// Cảnh báo tồn kho thấp
define('TONKHO_CANHBAO', 10);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Auto-include DB + Auth
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/auth.php';

// Helper format tiền Việt Nam
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function formatNum($n) {
    return number_format($n, 0, ',', '.');
}

// Lấy ngày hiện tại
function today() {
    return date('d/m/Y');
}

// Escape output
function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Badge trạng thái
function statusBadge($status, $type = 'hoadon') {
    $map = [
        'hoadon' => [
            'ChoXacNhan' => ['#f59e0b','Chờ Xác Nhận'],
            'DaXacNhan'  => ['#3b82f6','Đã Xác Nhận'],
            'DangGiao'   => ['#8b5cf6','Đang Giao'],
            'DaGiao'     => ['#10b981','Đã Giao'],
            'HoanThanh'  => ['#22c55e','Hoàn Thành'],
            'DaHuy'      => ['#ef4444','Đã Hủy'],
        ],
        'thanhtoan' => [
            'ChoThanhToan'=> ['#f59e0b','Chờ TT'],
            'DaThanhToan' => ['#22c55e','Đã TT'],
            'ThatBai'     => ['#ef4444','Thất Bại'],
            'DaHoanTien'  => ['#6b7280','Hoàn Tiền'],
        ],
        'sanpham' => [
            'DangBan'  => ['#22c55e','Đang Bán'],
            'HetHang'  => ['#ef4444','Hết Hàng'],
            'NgungBan' => ['#6b7280','Ngừng Bán'],
        ],
        'khuyenmai' => [
            'HoatDong' => ['#22c55e','Hoạt Động'],
            'TamDung'  => ['#f59e0b','Tạm Dừng'],
            'HetHan'   => ['#6b7280','Hết Hạn'],
        ],
    ];
    $info = $map[$type][$status] ?? ['#6b7280', $status];
    return "<span style='background:".e($info[0])."22;color:".e($info[0]).";border:1px solid ".e($info[0])."44;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap'>".e($info[1])."</span>";
}
