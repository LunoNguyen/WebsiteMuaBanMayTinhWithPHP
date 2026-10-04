<?php
// nvbanhang/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="nvb-sb" style="width:220px;min-width:220px;background:var(--bg-card);border-right:1px solid var(--border);
  display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;font-family:'Inter',sans-serif;">

  <!-- Logo -->
  <div style="padding:15px 13px 11px;border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:center;gap:9px;">
      <a href="<?= NVB_URL ?>/index.php" style="display:flex;flex-direction:column;gap:4px;text-decoration:none" title="Về trang chính">
        <?= themeLogo(40) ?>
        <div style="font-size:10px;color:var(--text-muted);">Bán hàng &amp; CRM</div>
      </a>
    </div>
  </div>

  <!-- Nav -->
  <div style="padding:11px 9px 4px;">
    <div style="font-size:10px;color:var(--text-muted);padding:0 7px;margin-bottom:4px;">Nghiệp Vụ</div>

    <a href="<?= NVB_URL ?>/index.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:<?= $currentPage==='index'?'var(--green)':'var(--text-muted)' ?>;font-size:12.5px;font-weight:<?= $currentPage==='index'?'600':'500' ?>;
              text-decoration:none;background:<?= $currentPage==='index'?'rgba(21,128,61,.12)':'' ?>;">
      <span style="display:flex"><?= icon('cart') ?></span>
      <div>
        <div>Quản lý Đơn hàng</div>
        <div style="font-size:10px;color:var(--text-muted)">Xử lý &amp; Theo dõi đơn</div>
      </div>
      <?php
        try {
          $pend = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='ChoXacNhan'");
          if(!empty($pend['c'])): ?>
          <span style="margin-left:auto;background:var(--red-solid);color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;"><?= $pend['c'] ?></span>
        <?php endif;
        } catch(Exception $e) {}
      ?>
    </a>

    <a href="<?= NVB_URL ?>/khachhang.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:<?= $currentPage==='khachhang'?'var(--green)':'var(--text-muted)' ?>;font-size:12.5px;font-weight:<?= $currentPage==='khachhang'?'600':'500' ?>;
              text-decoration:none;background:<?= $currentPage==='khachhang'?'rgba(21,128,61,.12)':'' ?>;">
      <span style="display:flex"><?= icon('user') ?></span>
      <div>
        <div>Quản lý Khách hàng</div>
        <div style="font-size:10px;color:var(--text-muted)">Thông tin &amp; Lịch sử mua</div>
      </div>
    </a>
  </div>

  <!-- User & Logout -->
  <div style="margin-top:auto;padding:9px;">
    <div style="margin:9px;padding:9px 11px;border-radius:8px;background:rgba(21,128,61,.08);border:1px solid rgba(21,128,61,.2);">
      <div>
        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green-solid);margin-right:4px;animation:pl 2s infinite;"></span>
        <span style="font-size:12px;font-weight:700;color:var(--green)">● Online</span>
      </div>
      <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Nhân viên Bán hàng</div>
      <div style="font-size:10px;color:var(--text-muted);margin-top:5px;font-family:monospace;">
        <?= htmlspecialchars($_SESSION['tennv']??'NV Bán hàng') ?>
        &bull;
        <?= htmlspecialchars($_SESSION['manv']??'NV002') ?>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/auth/logout.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--red);font-size:12.5px;text-decoration:none;margin-top:5px;">
      <span style="display:flex"><?= icon('logout') ?></span> Đăng xuất
    </a>
  </div>
</aside>
<div class="w-backdrop" onclick="toggleNvbSidebar()"></div>
<style>@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}</style>
