<?php
// ================================================================
// API Notifications - admin/api/notifications.php
// ================================================================
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$notifs = [];

// 1. Đơn hàng chờ xác nhận
$pending = dbFetchOne("SELECT COUNT(*) AS cnt FROM HOADON WHERE TRANGTHAI='ChoXacNhan'");
if ($pending && $pending['cnt'] > 0) {
    $notifs[] = [
        'icon' => '🛒',
        'text' => "<strong>{$pending['cnt']} đơn hàng</strong> đang chờ xác nhận",
        'time' => 'Vừa xong',
        'type' => 'order'
    ];
}

// 2. Sản phẩm sắp hết hàng
$lowStock = dbFetchOne("SELECT COUNT(*) AS cnt FROM SANPHAM WHERE SOLUONGTON <= 5 AND TRANGTHAI='DangBan'");
if ($lowStock && $lowStock['cnt'] > 0) {
    $notifs[] = [
        'icon' => '⚠️',
        'text' => "<strong>{$lowStock['cnt']} sản phẩm</strong> sắp hết hàng (≤5 cái)",
        'time' => date('H:i d/m/Y'),
        'type' => 'stock'
    ];
}

// 3. Phiếu nhập chưa thanh toán
$unpaid = dbFetchOne("SELECT COUNT(*) AS cnt, SUM(TONGCONG_PNH) AS tong FROM PHIEUNHAPHANG WHERE TRANGTHAI_THANHTOAN='ChuaThanhToan'");
if ($unpaid && $unpaid['cnt'] > 0) {
    $notifs[] = [
        'icon' => '💳',
        'text' => "<strong>{$unpaid['cnt']} phiếu nhập</strong> chưa thanh toán (" . number_format($unpaid['tong'],0,',','.') . "₫)",
        'time' => date('H:i d/m/Y'),
        'type' => 'payment'
    ];
}

// 4. Khuyến mãi sắp hết hạn (trong 3 ngày)
$expiring = dbFetchOne("SELECT COUNT(*) AS cnt FROM KHUYENMAI WHERE TRANGTHAI='HoatDong' AND NGAYKT BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)");
if ($expiring && $expiring['cnt'] > 0) {
    $notifs[] = [
        'icon' => '🎁',
        'text' => "<strong>{$expiring['cnt']} chương trình KM</strong> sắp hết hạn trong 3 ngày",
        'time' => date('H:i d/m/Y'),
        'type' => 'promo'
    ];
}

echo json_encode($notifs, JSON_UNESCAPED_UNICODE);
