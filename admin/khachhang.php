<?php
// ================================================================
// Quản lý Khách hàng - admin/khachhang.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Quản lý Khách hàng';
$breadcrumb = ['Quản lý', 'Khách hàng'];

$search = trim($_GET['q'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(kh.TENKH LIKE ? OR kh.MAKH LIKE ? OR kh.SDT_KH LIKE ? OR kh.EMAIL_KH LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    $types .= 'ssss';
}
$whereSQL = implode(' AND ', $where);
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM KHACHHANG kh WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$khachhang = dbFetch("SELECT kh.*,
    (SELECT COUNT(*) FROM HOADON WHERE MAKH=kh.MAKH) AS so_hd,
    (SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH=kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')) AS tong_chi_tieu
    FROM KHACHHANG kh WHERE $whereSQL
    ORDER BY tong_chi_tieu DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

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
          <h1>👤 Quản lý Khách hàng</h1>
          <p>Tổng <strong style="color:var(--blue-light)"><?= formatNum($total) ?></strong> khách hàng</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('khTable','khachhang')">📥 Xuất CSV</button>
          <a href="khachhang_them.php" class="btn btn-primary">＋ Thêm khách hàng</a>
        </div>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;width:100%">
          <div class="search-box" style="flex:1">
            <span class="si">🔍</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm tên, SĐT, email khách hàng..." />
          </div>
          <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
          <a href="khachhang.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="khTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Khách hàng</th>
                <th>Liên hệ</th>
                <th>Địa chỉ</th>
                <th>Đơn hàng</th>
                <th>Chi tiêu</th>
                <th>Hạng</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($khachhang as $i => $kh):
                $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), array_slice(explode(' ',$kh['TENKH']),-2)));
                $color = $avatarColors[$i % count($avatarColors)];
                $chiTieu = floatval($kh['tong_chi_tieu'] ?? 0);
                $hang = $chiTieu >= 50000000 ? ['💎 VIP','#f59e0b'] : ($chiTieu >= 20000000 ? ['🥇 Gold','#22c55e'] : ($chiTieu >= 5000000 ? ['🥈 Silver','#8892b0'] : ['🥉 Bronze','#cd7f32']));
              ?>
              <tr>
                <td style="color:var(--text-muted);font-size:12px"><?= $offset+$i+1 ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:38px;height:38px;border-radius:50%;background:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white;flex-shrink:0">
                      <?= mb_substr($initials,0,2) ?>
                    </div>
                    <div>
                      <div style="font-weight:600;font-size:13px"><?= e($kh['TENKH']) ?></div>
                      <div style="font-size:11px;color:var(--text-muted)">
                        <code style="background:var(--bg-main);padding:1px 4px;border-radius:3px"><?= e($kh['MAKH']) ?></code>
                        <?php if($kh['GIOITINH'] !== null): ?>
                          &bull; <?= $kh['GIOITINH'] ? '👨 Nam' : '👩 Nữ' ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="font-size:13px">📞 <?= e($kh['SDT_KH'] ?? '—') ?></div>
                  <div style="font-size:12px;color:var(--text-muted)">📧 <?= e($kh['EMAIL_KH'] ?? '—') ?></div>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);max-width:200px">
                  <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($kh['DIACHI_KH'] ?? '—') ?></div>
                </td>
                <td style="text-align:center">
                  <div style="font-size:18px;font-weight:700;color:var(--text-primary)"><?= $kh['so_hd'] ?? 0 ?></div>
                  <div style="font-size:11px;color:var(--text-muted)">đơn hàng</div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--blue-light)"><?= formatVND($chiTieu) ?></div>
                </td>
                <td>
                  <span style="background:<?= $hang[1] ?>22;color:<?= $hang[1] ?>;border:1px solid <?= $hang[1] ?>44;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    <?= $hang[0] ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="khachhang_chitiet.php?makh=<?= e($kh['MAKH']) ?>" class="btn-icon" title="Chi tiết">👁️</a>
                    <a href="khachhang_sua.php?makh=<?= e($kh['MAKH']) ?>" class="btn-icon" title="Sửa">✏️</a>
                    <a href="donhang.php?makh=<?= e($kh['MAKH']) ?>" class="btn-icon" title="Xem đơn hàng">🛒</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($khachhang)): ?>
              <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👤</div><p>Không tìm thấy khách hàng nào</p></div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if($pages > 1): ?>
        <div class="pagination">
          <?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="page-link">‹</a><?php endif; ?>
          <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="page-link">›</a><?php endif; ?>
          <span style="font-size:12px;color:var(--text-muted);margin-left:8px">Trang <?= $page ?>/<?= $pages ?></span>
        </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
