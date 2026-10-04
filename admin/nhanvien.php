<?php
// ================================================================
// Quản lý Nhân viên - admin/nhanvien.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Quản lý Nhân viên';
$breadcrumb = ['Quản lý', 'Nhân viên'];

$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? '';

if ($action === 'toggle' && !empty($_GET['manv'])) {
    $manv = $_GET['manv'];
    $nv = dbFetchOne("SELECT TRANGTHAI FROM NHANVIEN WHERE MANV=?", [$manv], 's');
    if ($nv) {
        $newTT = $nv['TRANGTHAI'] ? 0 : 1;
        dbExecute("UPDATE NHANVIEN SET TRANGTHAI=? WHERE MANV=?", [$newTT, $manv], 'is');
        $msg = "Đã cập nhật trạng thái nhân viên!";
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$macv   = $_GET['macv'] ?? '';
$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(nv.TENNV LIKE ? OR nv.MANV LIKE ? OR nv.EMAIL_NV LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'sss';
}
if ($macv) { $where[] = 'nv.MACV=?'; $params[] = $macv; $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM NHANVIEN nv WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$nhanvien = dbFetch("SELECT nv.*, cv.TENCV,
    (SELECT COUNT(*) FROM HOADON WHERE MANV=nv.MANV) AS so_hd
    FROM NHANVIEN nv
    LEFT JOIN CHUCVU cv ON nv.MACV=cv.MACV
    WHERE $whereSQL
    ORDER BY nv.NGAYVAOLAM DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

$chucvuList = dbFetch("SELECT * FROM CHUCVU ORDER BY MACV");

// Avatar colors
$avatarColors = ['#4f6ef7','#22c55e','#8b5cf6','#f59e0b','#06b6d4','#ec4899','#ef4444'];

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>👥 Quản lý Nhân viên</h1>
          <p>Tổng <strong style="color:var(--blue-light)"><?= formatNum($total) ?></strong> nhân viên</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('nvTable','nhanvien')">📥 Xuất CSV</button>
          <a href="nhanvien_them.php" class="btn btn-primary">＋ Thêm nhân viên</a>
        </div>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-success" data-dismiss>✅ <?= e($msg) ?></div>
      <?php endif; ?>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:260px">
            <span class="si">🔍</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm tên, mã, email nhân viên..." />
          </div>
          <select name="macv" class="form-control" style="width:200px">
            <option value="">Tất cả chức vụ</option>
            <?php foreach($chucvuList as $cv): ?>
            <option value="<?= e($cv['MACV']) ?>" <?= $macv===$cv['MACV']?'selected':'' ?>>
              <?= e($cv['TENCV']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary">🔍 Lọc</button>
          <a href="nhanvien.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Grid cards view -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px">
        <?php foreach($nhanvien as $i => $nv):
          $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), explode(' ', $nv['TENNV'])));
          $initials = mb_substr($initials, 0, 2);
          $color = $avatarColors[$i % count($avatarColors)];
          $isActive = $nv['TRANGTHAI'];
        ?>
        <div class="card" style="overflow:visible">
          <div class="card-body" style="padding:20px">
            <div style="display:flex;align-items:flex-start;gap:12px">
              <!-- Avatar -->
              <div style="width:52px;height:52px;border-radius:50%;background:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white;flex-shrink:0;box-shadow:0 4px 12px <?= $color ?>44">
                <?= e($initials) ?>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary)"><?= e($nv['TENNV']) ?></div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                  <code style="background:var(--bg-main);padding:1px 5px;border-radius:3px;font-size:11px"><?= e($nv['MANV']) ?></code>
                </div>
                <span style="background:rgba(79,110,247,0.1);color:var(--blue-light);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;margin-top:4px;display:inline-block">
                  <?= e($nv['TENCV'] ?? '—') ?>
                </span>
              </div>
              <!-- Status toggle -->
              <label style="cursor:pointer;position:relative;width:36px;height:20px;flex-shrink:0" title="<?= $isActive ? 'Đang làm việc' : 'Nghỉ việc' ?>">
                <a href="nhanvien.php?action=toggle&manv=<?= e($nv['MANV']) ?>" onclick="return confirm('Thay đổi trạng thái nhân viên?')">
                  <div style="width:36px;height:20px;background:<?= $isActive ? 'var(--green)' : 'var(--border)' ?>;border-radius:10px;display:flex;align-items:center;padding:2px;transition:var(--transition)">
                    <div style="width:16px;height:16px;background:white;border-radius:50%;margin-left:<?= $isActive ? '16px' : '0' ?>;transition:var(--transition)"></div>
                  </div>
                </a>
              </label>
            </div>

            <div style="margin-top:16px;display:flex;flex-direction:column;gap:6px">
              <?php if($nv['SDT_NV']): ?>
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>📞</span> <span><?= e($nv['SDT_NV']) ?></span>
              </div>
              <?php endif; ?>
              <?php if($nv['EMAIL_NV']): ?>
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>📧</span> <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($nv['EMAIL_NV']) ?></span>
              </div>
              <?php endif; ?>
              <?php if($nv['NGAYVAOLAM']): ?>
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>📅</span> <span>Vào làm: <?= date('d/m/Y', strtotime($nv['NGAYVAOLAM'])) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:12px;color:var(--text-muted)">
                <?= $nv['so_hd'] ?? 0 ?> đơn hàng đã xử lý
              </span>
              <div style="display:flex;gap:6px">
                <a href="nhanvien_sua.php?manv=<?= e($nv['MANV']) ?>" class="btn-icon" title="Sửa">✏️</a>
                <a href="taikhoan.php?manv=<?= e($nv['MANV']) ?>" class="btn-icon" title="Tài khoản">🔑</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($nhanvien)): ?>
        <div style="grid-column:1/-1">
          <div class="empty-state"><div class="empty-icon">👥</div><p>Không tìm thấy nhân viên nào</p></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Table view (hidden, for export) -->
      <table id="nvTable" style="display:none">
        <thead><tr><th>Mã NV</th><th>Tên NV</th><th>Chức vụ</th><th>SĐT</th><th>Email</th><th>Ngày vào làm</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach($nhanvien as $nv): ?>
        <tr>
          <td><?= e($nv['MANV']) ?></td>
          <td><?= e($nv['TENNV']) ?></td>
          <td><?= e($nv['TENCV']) ?></td>
          <td><?= e($nv['SDT_NV']) ?></td>
          <td><?= e($nv['EMAIL_NV']) ?></td>
          <td><?= $nv['NGAYVAOLAM'] ? date('d/m/Y',strtotime($nv['NGAYVAOLAM'])) : '' ?></td>
          <td><?= $nv['TRANGTHAI'] ? 'Đang làm việc' : 'Nghỉ việc' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <?php if($pages > 1): ?>
      <div class="pagination">
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
