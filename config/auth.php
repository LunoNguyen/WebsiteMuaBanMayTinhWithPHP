<?php
// ================================================================
// config/auth.php - Kiểm tra phân quyền theo Role
// ================================================================

/**
 * Yêu cầu đăng nhập & kiểm tra role.
 * Gọi ở đầu mỗi trang: requireRole(['Admin']) hoặc requireRole(['Admin','NhanVienBan'])
 *
 * Các role hợp lệ trong DB:
 *  - Admin
 *  - NhanVienBan  (Nhân viên bán hàng)
 *  - NhanVienKho  (Nhân viên kho)
 *  - KhachHang    (không vào được trang nội bộ)
 */
function requireRole(array $allowedRoles): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $loginUrl = defined('BASE_URL') ? BASE_URL . '/auth/login.php' : '/auth/login.php';

    if (empty($_SESSION['matk'])) {
        header('Location: ' . $loginUrl);
        exit;
    }

    $role = $_SESSION['loai'] ?? '';
    if (!in_array($role, $allowedRoles)) {
        // Chuyển đến trang đúng của role đó
        redirectByRole($role);
        exit;
    }
}

/**
 * Redirect đến dashboard của role hiện tại.
 */
function redirectByRole(string $role): void {
    $base = defined('BASE_URL') ? BASE_URL : '';
    switch ($role) {
        case 'Admin':
            header('Location: ' . $base . '/admin/index.php'); break;
        case 'NhanVienBan':
            header('Location: ' . $base . '/nvbanhang/index.php'); break;
        case 'NhanVienKho':
            header('Location: ' . $base . '/kho/index.php'); break;
        case 'KhachHang':
            header('Location: ' . $base . '/public/index.php'); break;
        default:
            header('Location: ' . $base . '/auth/login.php'); break;
    }
    exit;
}

/**
 * Lấy tên hiển thị của role.
 */
function getRoleLabel(string $role): string {
    return [
        'Admin'        => 'Quản trị viên',
        'NhanVienBan'  => 'Nhân viên bán hàng',
        'NhanVienKho'  => 'Nhân viên kho',
        'KhachHang'    => 'Khách hàng',
    ][$role] ?? $role;
}
