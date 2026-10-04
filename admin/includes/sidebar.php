<?php
// admin/includes/sidebar.php
// Xác định trang đang active
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar" id="sidebar">
  <!-- Logo -->
  <div class="sidebar-logo">
    <a href="<?= ADMIN_URL ?>/index.php" class="logo-text" title="Về trang tổng quan">
      <?= themeLogo(40) ?>
      <span>ADMIN PORTAL</span>
    </a>
    <span class="sidebar-badge">v2.0</span>
  </div>

  <!-- User Info -->
  <div class="sidebar-user">
    <div class="user-avatar">AD</div>
    <div class="user-info">
      <strong><?= e($_SESSION['tennv'] ?? 'Admin') ?></strong>
      <span>● Online</span>
    </div>
    <div class="user-status-dot"></div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <div class="nav-section-label">Tổng Quan</div>
    <a href="<?= ADMIN_URL ?>/index.php" class="nav-item <?= ($currentPage === 'index') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('dashboard') ?></span> Tổng quan
    </a>

    <div class="nav-section-label">Quản Lý</div>
    <a href="<?= ADMIN_URL ?>/nhanvien.php" class="nav-item <?= ($currentPage === 'nhanvien') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('users') ?></span> Quản lý Nhân viên
    </a>
    <a href="<?= ADMIN_URL ?>/taikhoan.php" class="nav-item <?= ($currentPage === 'taikhoan') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('key') ?></span> Quản lý Tài khoản
    </a>
    <a href="<?= ADMIN_URL ?>/sanpham.php" class="nav-item <?= ($currentPage === 'sanpham') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('laptop') ?></span> Quản lý Sản phẩm
    </a>
    <a href="<?= ADMIN_URL ?>/nhaphang.php" class="nav-item <?= ($currentPage === 'nhaphang') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('package') ?></span> Quản lý Nhập hàng
    </a>
    <a href="<?= ADMIN_URL ?>/khuyenmai.php" class="nav-item <?= ($currentPage === 'khuyenmai') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('tag') ?></span> Voucher &amp; Khuyến mãi
    </a>
    <a href="<?= ADMIN_URL ?>/donhang.php" class="nav-item <?= ($currentPage === 'donhang') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('cart') ?></span> Quản lý Đơn hàng
      <?php
        // Đếm đơn hàng chờ xác nhận
        try {
          $pendingOrders = dbFetchOne("SELECT COUNT(*) AS cnt FROM HOADON WHERE TRANGTHAI='ChoXacNhan'");
          if ($pendingOrders && $pendingOrders['cnt'] > 0): ?>
            <span class="nav-badge"><?= $pendingOrders['cnt'] ?></span>
          <?php endif;
        } catch(Exception $e) {}
      ?>
    </a>
    <a href="<?= ADMIN_URL ?>/khachhang.php" class="nav-item <?= ($currentPage === 'khachhang') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('user') ?></span> Quản lý Khách hàng
    </a>

    <div class="nav-section-label">Hệ Thống</div>
    <a href="<?= ADMIN_URL ?>/chatbot.php" class="nav-item <?= ($currentPage === 'chatbot') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('bot') ?></span> Lịch sử Chatbot
    </a>
    <a href="<?= ADMIN_URL ?>/baocao.php" class="nav-item <?= ($currentPage === 'baocao') ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon('chart') ?></span> Báo cáo & Thống kê
    </a>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item" style="color:var(--red)">
      <span class="nav-icon"><?= icon('logout') ?></span> Đăng xuất
    </a>
  </div>
</aside>
<div class="sidebar-backdrop" onclick="toggleSidebar()"></div>
