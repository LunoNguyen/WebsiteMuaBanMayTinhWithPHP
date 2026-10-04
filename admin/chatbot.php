<?php
// ================================================================
// Chatbot history - admin/chatbot.php
// ================================================================
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Lịch sử Chatbot';
$breadcrumb = ['Hệ thống', 'Chatbot'];

$search = trim($_GET['q'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$selectedPhien = intval($_GET['maphien'] ?? 0);

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(kh.TENKH LIKE ? OR tk.EMAIL_TK LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss';
}

$whereSQL = implode(' AND ', $where);
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM PHIEN_CHATBOT pc LEFT JOIN KHACHHANG kh ON pc.MAKH=kh.MAKH LEFT JOIN TAIKHOAN tk ON pc.MATK=tk.MATK WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total/$perPage));
$offset = ($page-1)*$perPage;

$phienList = dbFetch("SELECT pc.*, kh.TENKH, tk.EMAIL_TK,
    (SELECT COUNT(*) FROM LICHSU_CHATBOT WHERE MAPHIEN=pc.MAPHIEN) AS so_tin
    FROM PHIEN_CHATBOT pc
    LEFT JOIN KHACHHANG kh ON pc.MAKH=kh.MAKH
    LEFT JOIN TAIKHOAN  tk ON pc.MATK=tk.MATK
    WHERE $whereSQL ORDER BY pc.THOIGIAN_BD DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

// Chi tiết phiên đang xem
$messages = [];
$currentPhien = null;
if ($selectedPhien) {
    $currentPhien = dbFetchOne("SELECT pc.*, kh.TENKH, tk.EMAIL_TK FROM PHIEN_CHATBOT pc LEFT JOIN KHACHHANG kh ON pc.MAKH=kh.MAKH LEFT JOIN TAIKHOAN tk ON pc.MATK=tk.MATK WHERE pc.MAPHIEN=?", [$selectedPhien], 'i');
    $messages = dbFetch("SELECT * FROM LICHSU_CHATBOT WHERE MAPHIEN=? ORDER BY THOIGIAN ASC", [$selectedPhien], 'i');
}

// Stats
$statsTotal   = dbFetchOne("SELECT COUNT(*) AS cnt FROM PHIEN_CHATBOT");
$statsToday   = dbFetchOne("SELECT COUNT(*) AS cnt FROM PHIEN_CHATBOT WHERE DATE(THOIGIAN_BD)=CURDATE()");
$statsActive  = dbFetchOne("SELECT COUNT(*) AS cnt FROM PHIEN_CHATBOT WHERE TRANGTHAI='DangChat'");

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>Lịch sử Chatbot</h1>
          <p>Xem lại các cuộc hội thoại của khách hàng với chatbot AI</p>
        </div>
      </div>

      <!-- Stats -->
      <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px"><?= icon('message') ?></span>
          <div><div style="font-size:12px;color:var(--text-muted)">Tổng phiên chat</div><div style="font-size:20px;font-weight:700"><?= $statsTotal['cnt'] ?? 0 ?></div></div>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px"><?= icon('calendar') ?></span>
          <div><div style="font-size:12px;color:var(--text-muted)">Hôm nay</div><div style="font-size:20px;font-weight:700"><?= $statsToday['cnt'] ?? 0 ?></div></div>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px"><?= icon('dot') ?></span>
          <div><div style="font-size:12px;color:var(--text-muted)">Đang chat</div><div style="font-size:20px;font-weight:700;color:var(--green)"><?= $statsActive['cnt'] ?? 0 ?></div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:<?= $selectedPhien ? '360px 1fr' : '1fr' ?>;gap:20px">
        <!-- Danh sách phiên -->
        <div class="card">
          <div class="card-header">
            <h3>Danh sách phiên chat</h3>
            <form method="GET" style="display:flex;gap:6px">
              <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm khách..." class="form-control" style="width:160px;padding:6px 10px" />
              <button type="submit" class="btn btn-sm btn-primary"><?= icon('search') ?></button>
            </form>
          </div>
          <div style="padding:0">
            <?php foreach($phienList as $phien):
              $isActive = $phien['TRANGTHAI'] === 'DangChat';
              $isSelected = $phien['MAPHIEN'] == $selectedPhien;
            ?>
            <a href="?maphien=<?= $phien['MAPHIEN'] ?><?= $search ? '&q='.urlencode($search) : '' ?>"
               style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);text-decoration:none;transition:var(--transition);background:<?= $isSelected?'var(--blue-glow)':'' ?>;<?= $isSelected?'border-left:3px solid var(--blue)':'' ?>"
               onmouseenter="this.style.background='var(--bg-card-hover)'"
               onmouseleave="this.style.background='<?= $isSelected?'var(--blue-glow)':'' ?>'">
              <div style="width:38px;height:38px;border-radius:50%;background:var(--bg-input);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;border:1px solid var(--border);position:relative">
                👤
                <?php if($isActive): ?>
                <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;background:var(--green);border-radius:50%;border:2px solid var(--bg-sidebar)"></div>
                <?php endif; ?>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:13px;color:var(--text-primary)">
                  <?= e($phien['TENKH'] ?? $phien['EMAIL_TK'] ?? 'Khách ẩn danh') ?>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                  <?= date('d/m/Y H:i', strtotime($phien['THOIGIAN_BD'])) ?>
                  &bull; <?= $phien['so_tin'] ?> tin nhắn
                </div>
              </div>
              <?php if($isActive): ?>
              <span style="background:rgba(21,128,61,0.1);color:var(--green);border:1px solid rgba(21,128,61,0.3);padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;flex-shrink:0">LIVE</span>
              <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php if(empty($phienList)): ?>
            <div class="empty-state"><div class="empty-icon"><?= icon('bot') ?></div><p>Chưa có phiên chat nào</p></div>
            <?php endif; ?>
          </div>
          <?php if($pages > 1): ?>
          <div class="pagination" style="padding:10px 16px">
            <?php if($page>1): ?><a href="?page=<?= $page-1 ?>&q=<?= urlencode($search) ?>" class="page-link">‹</a><?php endif; ?>
            <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
              <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>" class="page-link <?= $p===$page?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if($page<$pages): ?><a href="?page=<?= $page+1 ?>&q=<?= urlencode($search) ?>" class="page-link">›</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <?php if($selectedPhien && $currentPhien): ?>
        <!-- Chi tiết phiên -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>Phiên #<?= $selectedPhien ?> — <?= e($currentPhien['TENKH'] ?? $currentPhien['EMAIL_TK'] ?? 'Khách ẩn danh') ?></h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:3px">
                <?= date('d/m/Y H:i', strtotime($currentPhien['THOIGIAN_BD'])) ?>
                <?= $currentPhien['THOIGIAN_KT'] ? ' → '.date('H:i', strtotime($currentPhien['THOIGIAN_KT'])) : ' (Đang chat)' ?>
              </p>
            </div>
            <a href="chatbot.php" class="btn btn-sm btn-outline">Đóng</a>
          </div>
          <div style="padding:16px;max-height:500px;overflow-y:auto;display:flex;flex-direction:column;gap:12px">
            <?php foreach($messages as $msg):
              $isBot = $msg['NGUOI_GUI'] === 'Bot';
            ?>
            <div style="display:flex;<?= $isBot?'':'flex-direction:row-reverse' ?>;gap:10px;align-items:flex-end">
              <div style="width:32px;height:32px;border-radius:50%;background:<?= $isBot?'var(--blue-solid)':'var(--green-solid)' ?>;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
                <?= $isBot ? '🤖' : '👤' ?>
              </div>
              <div style="max-width:70%">
                <div style="background:<?= $isBot?'var(--bg-card)':'rgba(58,86,228,0.15)' ?>;border:1px solid <?= $isBot?'var(--border)':'rgba(58,86,228,0.3)' ?>;border-radius:<?= $isBot?'4px 12px 12px 12px':'12px 4px 12px 12px' ?>;padding:10px 14px;font-size:13px;color:var(--text-primary);line-height:1.5">
                  <?= nl2br(e($msg['NOI_DUNG'])) ?>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;text-align:<?= $isBot?'left':'right' ?>">
                  <?= date('H:i', strtotime($msg['THOIGIAN'])) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($messages)): ?>
            <div class="empty-state"><div class="empty-icon"><?= icon('message') ?></div><p>Chưa có tin nhắn nào</p></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
