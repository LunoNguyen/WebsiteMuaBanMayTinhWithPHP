<?php
// admin/includes/topbar.php
$breadcrumb = $breadcrumb ?? ['Trang chủ'];
$pageSubtitle = $pageSubtitle ?? '';
?>
<!-- TOPBAR -->
<header class="topbar">
  <button type="button" class="topbar-btn topbar-menu-btn" onclick="toggleSidebar()" aria-label="Mở menu"><?= icon('menu', 18) ?></button>

  <!-- Breadcrumb -->
  <div class="topbar-breadcrumb">
    <span>Hệ thống Nexus</span>
    <span class="sep">/</span>
    <?php foreach($breadcrumb as $i => $crumb): ?>
      <?php if($i === count($breadcrumb)-1): ?>
        <span class="current"><?= e($crumb) ?></span>
      <?php else: ?>
        <span><?= e($crumb) ?></span>
        <span class="sep">/</span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Search Bar -->
  <div class="topbar-search">
    <span class="search-icon"><?= icon('search', 15) ?></span>
    <input type="text" id="globalSearch" placeholder="Tìm kiếm sản phẩm, đơn hàng, khách hàng..." />
  </div>

  <!-- Actions -->
  <div class="topbar-actions">
    <!-- Date info -->
    <div class="date-chip" style="display:none;gap:6px" id="dateChip">
      <span>📅</span>
      <span>Hôm nay <strong id="todayDate"><?= date('d/m/Y') ?></strong></span>
    </div>

    <!-- Sáng / tối -->
    <?= themeToggle() ?>

    <!-- Notifications -->
    <div class="topbar-btn" onclick="toggleNotif()" title="Thông báo" id="notifBtn">
      <?= icon('bell', 18) ?>
      <span class="topbar-notif-dot" id="notifDot"></span>
    </div>

    <!-- Settings -->
    <a href="<?= ADMIN_URL ?>/settings.php" class="topbar-btn" title="Cài đặt"><?= icon('settings', 18) ?></a>

    <!-- Profile -->
    <div class="topbar-profile" onclick="window.location='<?= ADMIN_URL ?>/profile.php'">
      <div class="av">AD</div>
      <span><?= e($_SESSION['tennv'] ?? 'Administrator') ?></span>
      <span style="color:var(--text-muted);display:flex"><?= icon('chevron', 14) ?></span>
    </div>
  </div>
</header>

<!-- Notification Panel (hidden by default) -->
<div id="notifPanel" style="position:fixed;top:68px;right:20px;width:320px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);z-index:200;display:none;box-shadow:var(--shadow)">
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <strong style="font-size:14px">🔔 Thông báo</strong>
    <span onclick="document.getElementById('notifPanel').style.display='none'" style="cursor:pointer;color:var(--text-muted)">✕</span>
  </div>
  <div style="padding:8px 0;max-height:300px;overflow-y:auto" id="notifList">
    <div style="padding:12px 16px;font-size:13px;color:var(--text-muted);text-align:center">Đang tải...</div>
  </div>
</div>

<script>
function toggleNotif() {
  const p = document.getElementById('notifPanel');
  p.style.display = p.style.display === 'none' ? 'block' : 'none';
  if(p.style.display === 'block') loadNotifs();
}

function loadNotifs() {
  fetch('<?= ADMIN_URL ?>/api/notifications.php')
    .then(r => r.json())
    .then(data => {
      const list = document.getElementById('notifList');
      if(!data.length) {
        list.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted)">Không có thông báo mới</div>';
        return;
      }
      list.innerHTML = data.map(n => `
        <div style="padding:10px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px;align-items:flex-start">
          <span style="font-size:18px">${n.icon}</span>
          <div>
            <div style="font-size:13px;color:var(--text-primary)">${n.text}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px">${n.time}</div>
          </div>
        </div>
      `).join('');
    }).catch(() => {});
}

// Global search
document.getElementById('globalSearch').addEventListener('keydown', function(e) {
  if(e.key === 'Enter' && this.value.trim()) {
    window.location = '<?= ADMIN_URL ?>/sanpham.php?q=' + encodeURIComponent(this.value.trim());
  }
});
</script>
