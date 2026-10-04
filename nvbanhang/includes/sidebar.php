<?php
// nvbanhang/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside style="width:220px;min-width:220px;background:#161b22;border-right:1px solid #30363d;
  display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;font-family:'Inter',sans-serif;">

  <!-- Logo -->
  <div style="padding:15px 13px 11px;border-bottom:1px solid #30363d;">
    <div style="display:flex;align-items:center;gap:9px;">
      <div style="width:33px;height:33px;border-radius:8px;background:linear-gradient(135deg,#22c55e,#16a34a);
        display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;">S</div>
      <div>
        <div style="font-size:14px;font-weight:800;color:#e6edf3;">NEXUS Sales</div>
        <div style="font-size:10px;color:#8b949e;letter-spacing:1px;text-transform:uppercase;">Bán hàng &amp; CRM</div>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <div style="padding:11px 9px 4px;">
    <div style="font-size:10px;color:#8b949e;letter-spacing:1.2px;text-transform:uppercase;padding:0 7px;margin-bottom:4px;">Nghiệp Vụ</div>

    <a href="<?= NVB_URL ?>/index.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:<?= $currentPage==='index'?'#22c55e':'#8b949e' ?>;font-size:12.5px;font-weight:<?= $currentPage==='index'?'600':'500' ?>;
              text-decoration:none;background:<?= $currentPage==='index'?'rgba(34,197,94,.12)':'' ?>;">
      <span style="font-size:14px">🛒</span>
      <div>
        <div>Quản lý Đơn hàng</div>
        <div style="font-size:10px;color:#8b949e">Xử lý &amp; Theo dõi đơn</div>
      </div>
      <?php
        try {
          $pend = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='ChoXacNhan'");
          if(!empty($pend['c'])): ?>
          <span style="margin-left:auto;background:#ef4444;color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;"><?= $pend['c'] ?></span>
        <?php endif;
        } catch(Exception $e) {}
      ?>
    </a>

    <a href="<?= NVB_URL ?>/khachhang.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:<?= $currentPage==='khachhang'?'#22c55e':'#8b949e' ?>;font-size:12.5px;font-weight:<?= $currentPage==='khachhang'?'600':'500' ?>;
              text-decoration:none;background:<?= $currentPage==='khachhang'?'rgba(34,197,94,.12)':'' ?>;">
      <span style="font-size:14px">👤</span>
      <div>
        <div>Quản lý Khách hàng</div>
        <div style="font-size:10px;color:#8b949e">Thông tin &amp; Lịch sử mua</div>
      </div>
    </a>
  </div>

  <!-- User & Logout -->
  <div style="margin-top:auto;padding:9px;">
    <div style="margin:9px;padding:9px 11px;border-radius:8px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);">
      <div>
        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#22c55e;margin-right:4px;animation:pl 2s infinite;"></span>
        <span style="font-size:12px;font-weight:700;color:#22c55e">● Online</span>
      </div>
      <div style="font-size:10px;color:#8b949e;margin-top:2px;">Nhân viên Bán hàng</div>
      <div style="font-size:10px;color:#8b949e;margin-top:5px;font-family:monospace;">
        <?= htmlspecialchars($_SESSION['tennv']??'NV Bán hàng') ?>
        &bull;
        <?= htmlspecialchars($_SESSION['manv']??'NV002') ?>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/auth/logout.php"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:#ef4444;font-size:12.5px;text-decoration:none;margin-top:5px;">
      <span>🚪</span> Đăng xuất
    </a>
  </div>
</aside>
<style>@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}</style>
