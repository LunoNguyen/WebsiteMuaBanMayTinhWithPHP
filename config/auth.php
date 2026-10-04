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
 * Chức vụ (NHANVIEN.MACV) -> khu vực làm việc. Chức vụ không có trong bảng thì không vào được hệ thống.
 * Muốn cấp quyền cho chức vụ khác thì thêm một dòng ở đây.
 */
const ROLE_BY_CHUCVU = [
    'CV001' => 'Admin',        // Giám đốc
    'CV002' => 'NhanVienBan',  // Quản lý bán hàng
    'CV004' => 'NhanVienBan',  // Nhân viên bán hàng
    'CV005' => 'NhanVienBan',  // Nhân viên thu ngân
    'CV009' => 'NhanVienBan',  // Chăm sóc khách hàng
    'CV003' => 'NhanVienKho',  // Quản lý kho
    'CV007' => 'NhanVienKho',  // Nhân viên giao hàng
];

/** Các role được vào trang nội bộ. */
const INTERNAL_ROLES = ['Admin', 'NhanVienBan', 'NhanVienKho'];

/**
 * Xác định role từ loại tài khoản và chức vụ.
 * Trả về role nội bộ, hoặc null kèm lý do khi tài khoản không được vào hệ thống.
 */
function resolveRole(array $tk, ?string &$reason = null): ?string {
    switch ($tk['LOAI_TAIKHOAN'] ?? '') {
        case 'Admin':
            return 'Admin';
        case 'NhanVien':
            if (empty($tk['MANV'])) {
                $reason = 'Tài khoản chưa được gắn với nhân viên nào.';
                return null;
            }
            if (isset($tk['NV_TRANGTHAI']) && (int)$tk['NV_TRANGTHAI'] !== 1) {
                $reason = 'Nhân viên đã nghỉ việc, không thể đăng nhập.';
                return null;
            }
            $role = ROLE_BY_CHUCVU[$tk['MACV'] ?? ''] ?? null;
            if (!$role) {
                $reason = 'Chức vụ ' . ($tk['TENCV'] ?? '') . ' chưa được cấp quyền vào hệ thống.';
            }
            return $role;
        case 'KhachHang':
            $reason = 'Tài khoản khách hàng không đăng nhập được vào trang quản lý.';
            return null;
    }
    $reason = 'Loại tài khoản không hợp lệ.';
    return null;
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
        default:
            // Role không có trang nội bộ: xoá phiên để trang đăng nhập không chuyển vòng
            $_SESSION = [];
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
