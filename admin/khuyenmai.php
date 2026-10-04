<?php
// ================================================================
// Quản lý Khuyến mãi - admin/khuyenmai.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Voucher & Khuyến mãi';
$breadcrumb = ['Quản lý', 'Khuyến mãi'];

$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? '';

if ($action === 'toggle' && !empty($_GET['makm'])) {
    $makm = $_GET['makm'];
    $km = dbFetchOne("SELECT TRANGTHAI FROM KHUYENMAI WHERE MAKM=?", [$makm], 's');
    if ($km) {
        $newTT = $km['TRANGTHAI'] === 'HoatDong' ? 'TamDung' : 'HoatDong';
        dbExecute("UPDATE KHUYENMAI SET TRANGTHAI=? WHERE MAKM=?", [$newTT, $makm], 'ss');
        $msg = "Đã cập nhật trạng thái khuyến mãi!";
    }
}

if ($action === 'delete' && !empty($_GET['makm'])) {
    $makm = $_GET['makm'];
    $used = dbFetchOne("SELECT COUNT(*) AS cnt FROM HOADON_KHUYENMAI WHERE MAKM=?", [$makm], 's');
    if ($used && $used['cnt'] > 0) {
        $msg = "Không thể xóa khuyến mãi đã áp dụng vào hóa đơn!";
        $msgType = 'error';
    } else {
        dbExecute("DELETE FROM CT_KHUYENMAI WHERE MAKM=?", [$makm], 's');
        dbExecute("DELETE FROM KHUYENMAI WHERE MAKM=?", [$makm], 's');
        $msg = "Đã xóa khuyến mãi thành công!";
    }
}

// Filters
$search    = trim($_GET['q'] ?? '');
$trangthai = $_GET['trangthai'] ?? '';
$loaikm    = $_GET['loaikm'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 10;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(TENKM LIKE ? OR MA_CODE LIKE ? OR MAKM LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'sss';
}
if ($trangthai) { $where[] = 'TRANGTHAI=?'; $params[] = $trangthai; $types .= 's'; }
if ($loaikm)    { $where[] = 'LOAI_KM=?';   $params[] = $loaikm;   $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM KHUYENMAI WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$khuyenmai = dbFetch("SELECT km.*,
    (SELECT COUNT(*) FROM CT_KHUYENMAI WHERE MAKM=km.MAKM) AS so_sp_ap_dung,
    (SELECT COUNT(*) FROM HOADON_KHUYENMAI WHERE MAKM=km.MAKM) AS so_hd_da_dung
    FROM KHUYENMAI km
    WHERE $whereSQL
    ORDER BY km.TRANGTHAI='HoatDong' DESC, km.NGAYKT DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

// Stats
$statsKM = dbFetch("SELECT TRANGTHAI, COUNT(*) AS cnt FROM KHUYENMAI GROUP BY TRANGTHAI");
$statKMMap = [];
foreach($statsKM as $s) $statKMMap[$s['TRANGTHAI']] = $s['cnt'];

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>🎁 Voucher &amp; Khuyến mãi</h1>
          <p>Quản lý mã giảm giá và chương trình khuyến mãi</p>
        </div>
        <div class="page-header-right">
          <a href="khuyenmai_them.php" class="btn btn-primary">＋ Tạo khuyến mãi</a>
        </div>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msgType==='success'?'success':'danger' ?>" data-dismiss>
        <?= $msgType==='success'?'✅':'❌' ?> <?= e($msg) ?>
      </div>
      <?php endif; ?>

      <!-- Quick stats -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <?php foreach([
          ['HoatDong','🟢','Hoạt động'],
          ['TamDung', '🟡','Tạm dừng'],
          ['HetHan',  '⚫','Hết hạn'],
        ] as [$key,$icon,$label]): ?>
        <a href="?trangthai=<?= $key ?>" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;<?= $trangthai===$key?'border-color:var(--blue);background:var(--blue-glow)':'' ?>">
          <span><?= $icon ?></span>
          <span style="font-size:13px;color:var(--text-secondary)"><?= $label ?></span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px"><?= $statKMMap[$key] ?? 0 ?></strong>
        </a>
        <?php endforeach; ?>
        <?php if($trangthai): ?><a href="khuyenmai.php" class="btn btn-sm btn-outline">✕ Xóa lọc</a><?php endif; ?>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:250px">
            <span class="si">🔍</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm tên, mã voucher..." />
          </div>
          <select name="loaikm" class="form-control" style="width:180px">
            <option value="">Tất cả loại</option>
            <option value="PhanTram"     <?= $loaikm==='PhanTram'?'selected':'' ?>>Phần trăm (%)</option>
            <option value="SoTienCoDinh" <?= $loaikm==='SoTienCoDinh'?'selected':'' ?>>Số tiền cố định</option>
          </select>
          <input type="hidden" name="trangthai" value="<?= e($trangthai) ?>" />
          <button type="submit" class="btn btn-primary">🔍 Lọc</button>
          <a href="khuyenmai.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Voucher Grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
        <?php foreach($khuyenmai as $km):
          $isActive = $km['TRANGTHAI'] === 'HoatDong';
          $isExpired = $km['TRANGTHAI'] === 'HetHan';
          $pct = $km['SOLUONG_MA'] ? min(100, round($km['DA_SUDUNG'] / $km['SOLUONG_MA'] * 100)) : null;
          $accentColor = $isActive ? '#22c55e' : ($isExpired ? '#4a5568' : '#f59e0b');
        ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:var(--transition);<?= $isExpired?'opacity:0.6':'' ?>"
             onmouseenter="this.style.borderColor='<?= $accentColor ?>44'"
             onmouseleave="this.style.borderColor='var(--border)'">

          <!-- Top stripe -->
          <div style="height:4px;background:linear-gradient(90deg,<?= $accentColor ?>,transparent)"></div>

          <div style="padding:16px">
            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px">
              <!-- Icon -->
              <div style="width:44px;height:44px;border-radius:10px;background:<?= $accentColor ?>22;border:1px solid <?= $accentColor ?>44;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                <?= $km['LOAI_KM'] === 'PhanTram' ? '🏷️' : '💵' ?>
              </div>

              <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:4px"><?= e($km['TENKM']) ?></div>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                  <code style="background:var(--bg-main);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-size:12px;color:var(--blue-light);letter-spacing:1px">
                    <?= e($km['MA_CODE'] ?? '—') ?>
                  </code>
                  <?= statusBadge($km['TRANGTHAI'], 'khuyenmai') ?>
                </div>
              </div>

              <!-- Value -->
              <div style="text-align:right;flex-shrink:0">
                <div style="font-size:20px;font-weight:800;color:<?= $accentColor ?>">
                  <?php if($km['LOAI_KM']==='PhanTram'): ?>
                    -<?= $km['GIATRI_KM'] ?>%
                  <?php else: ?>
                    -<?= formatVND($km['GIATRI_KM']) ?>
                  <?php endif; ?>
                </div>
                <?php if($km['SOTIENTOIDA_KM'] && $km['LOAI_KM']==='PhanTram'): ?>
                <div style="font-size:11px;color:var(--text-muted)">tối đa <?= formatVND($km['SOTIENTOIDA_KM']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Info grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;color:var(--text-muted);margin-bottom:12px">
              <div>
                <div>📅 Bắt đầu</div>
                <div style="color:var(--text-secondary);font-weight:500"><?= $km['NGAYBD'] ? date('d/m/Y',strtotime($km['NGAYBD'])) : '—' ?></div>
              </div>
              <div>
                <div>📅 Kết thúc</div>
                <div style="color:<?= (!$isExpired && $km['NGAYKT'] && strtotime($km['NGAYKT']) < strtotime('+3 days')) ? 'var(--red)' : 'var(--text-secondary)' ?>;font-weight:500">
                  <?= $km['NGAYKT'] ? date('d/m/Y',strtotime($km['NGAYKT'])) : '∞' ?>
                </div>
              </div>
              <div>
                <div>🛒 Đơn tối thiểu</div>
                <div style="color:var(--text-secondary);font-weight:500"><?= $km['SOTIENTOITHIEU_NHANKM'] > 0 ? formatVND($km['SOTIENTOITHIEU_NHANKM']) : 'Không' ?></div>
              </div>
              <div>
                <div>🏷️ Sản phẩm áp dụng</div>
                <div style="color:var(--text-secondary);font-weight:500"><?= $km['so_sp_ap_dung'] > 0 ? $km['so_sp_ap_dung'].' SP' : 'Tất cả' ?></div>
              </div>
            </div>

            <!-- Progress bar (nếu có giới hạn lượt) -->
            <?php if($km['SOLUONG_MA']): ?>
            <div style="margin-bottom:12px">
              <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <span>Đã dùng: <?= $km['DA_SUDUNG'] ?>/<?= $km['SOLUONG_MA'] ?> lượt</span>
                <span><?= $pct ?>%</span>
              </div>
              <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct > 80 ? 'var(--red)' : ($pct > 50 ? 'var(--orange)' : 'var(--blue)') ?>;border-radius:3px;transition:width 0.5s ease"></div>
              </div>
            </div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--green);margin-bottom:12px">♾️ Không giới hạn lượt dùng &bull; Đã dùng: <?= $km['DA_SUDUNG'] ?> lần</div>
            <?php endif; ?>

            <!-- Actions -->
            <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--border)">
              <a href="khuyenmai.php?action=toggle&makm=<?= e($km['MAKM']) ?>"
                 class="btn btn-sm <?= $isActive?'btn-outline':'btn-success' ?>"
                 onclick="return confirm('Thay đổi trạng thái khuyến mãi?')">
                <?= $isActive ? '⏸️ Tạm dừng' : '▶️ Kích hoạt' ?>
              </a>
              <a href="khuyenmai_sua.php?makm=<?= e($km['MAKM']) ?>" class="btn btn-sm btn-outline">✏️ Sửa</a>
              <a href="khuyenmai.php?action=delete&makm=<?= e($km['MAKM']) ?>"
                 class="btn btn-sm btn-outline" style="margin-left:auto;border-color:rgba(239,68,68,0.3);color:var(--red)"
                 onclick="return confirm('Xóa khuyến mãi <?= e(addslashes($km['TENKM'])) ?>?')">🗑️</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($khuyenmai)): ?>
        <div style="grid-column:1/-1">
          <div class="empty-state"><div class="empty-icon">🎁</div><p>Không tìm thấy khuyến mãi nào</p></div>
        </div>
        <?php endif; ?>
      </div>

      <?php if($pages > 1): ?>
      <div class="pagination" style="margin-top:16px">
        <?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="page-link">‹</a><?php endif; ?>
        <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="page-link">›</a><?php endif; ?>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
