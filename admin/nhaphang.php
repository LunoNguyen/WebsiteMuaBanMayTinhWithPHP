<?php
// ================================================================
// Quản lý Nhập hàng - admin/nhaphang.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Quản lý Nhập hàng';
$breadcrumb = ['Quản lý', 'Nhập hàng'];

$search    = trim($_GET['q'] ?? '');
$trangthai = $_GET['trangthai'] ?? '';
$mancc     = $_GET['mancc'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 10;

$where = ['1=1']; $params = []; $types = '';
if ($search)    { $where[] = '(pnh.MAPNH LIKE ? OR ncc.TENNCC LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }
if ($trangthai) { $where[] = 'pnh.TRANGTHAI=?';  $params[] = $trangthai; $types .= 's'; }
if ($mancc)     { $where[] = 'pnh.MANCC=?';      $params[] = $mancc;     $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$joinSQL  = "FROM PHIEUNHAPHANG pnh LEFT JOIN NHACUNGCAP ncc ON pnh.MANCC=ncc.MANCC LEFT JOIN NHANVIEN nv ON pnh.MANV=nv.MANV";
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt $joinSQL WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total/$perPage));
$offset = ($page-1)*$perPage;

$phieunhap = dbFetch("SELECT pnh.*, ncc.TENNCC, nv.TENNV,
    (SELECT SUM(SOLUONG) FROM CT_PHIEUNHAPHANG WHERE MAPNH=pnh.MAPNH) AS tong_sl,
    (SELECT COUNT(*) FROM CT_PHIEUNHAPHANG WHERE MAPNH=pnh.MAPNH) AS so_san_pham
    $joinSQL WHERE $whereSQL
    ORDER BY pnh.NGAYTAO DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

$nccList = dbFetch("SELECT * FROM NHACUNGCAP ORDER BY TENNCC");
$statsTT = ['ChuaThanhToan'=>0,'DaThanhToan'=>0,'HoanTien'=>0];
$statsRows = dbFetch("SELECT TRANGTHAI_THANHTOAN, COUNT(*) AS cnt FROM PHIEUNHAPHANG GROUP BY TRANGTHAI_THANHTOAN");
foreach($statsRows as $r) $statsTT[$r['TRANGTHAI_THANHTOAN']] = $r['cnt'];

// Tổng tiền nhập tháng này
$tongNhapThang = dbFetchOne("SELECT SUM(TONGCONG_PNH) AS tong FROM PHIEUNHAPHANG WHERE MONTH(NGAYTAO)=MONTH(CURDATE()) AND YEAR(NGAYTAO)=YEAR(CURDATE())");

$statusMap = [
  'ChoDuyet'   => ['#f59e0b','Chờ duyệt'],
  'DaDuyet'    => ['#4f6ef7','Đã duyệt'],
  'DaNhan'     => ['#8b5cf6','Đã nhận'],
  'HoanThanh'  => ['#22c55e','Hoàn thành'],
  'DaHuy'      => ['#ef4444','Đã hủy'],
];
$ttMap = [
  'ChuaThanhToan' => ['#f59e0b','Chưa TT'],
  'DaThanhToan'   => ['#22c55e','Đã TT'],
  'HoanTien'      => ['#6b7280','Hoàn tiền'],
];

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>📦 Quản lý Nhập hàng</h1>
          <p>Tổng tháng này: <strong style="color:var(--blue-light)"><?= formatVND($tongNhapThang['tong'] ?? 0) ?></strong></p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('pnhTable','phieunhap')">📥 Xuất CSV</button>
          <a href="nhaphang_them.php" class="btn btn-primary">＋ Tạo phiếu nhập</a>
        </div>
      </div>

      <!-- Quick stats -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
        <?php foreach([
          ['ChuaThanhToan','💳','Chưa thanh toán','orange'],
          ['DaThanhToan',  '✅','Đã thanh toán',  'green'],
          ['HoanTien',     '↩️','Hoàn tiền',       'gray'],
        ] as [$key,$icon,$label,$c]): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:16px;display:flex;align-items:center;gap:12px">
          <span style="font-size:24px"><?= $icon ?></span>
          <div>
            <div style="font-size:12px;color:var(--text-muted)"><?= $label ?></div>
            <div style="font-size:20px;font-weight:700;color:var(--text-primary)"><?= $statsTT[$key] ?> phiếu</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:220px">
            <span class="si">🔍</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Mã phiếu, tên NCC..." />
          </div>
          <select name="trangthai" class="form-control" style="width:160px">
            <option value="">Tất cả trạng thái</option>
            <?php foreach(['ChoDuyet','DaDuyet','DaNhan','HoanThanh','DaHuy'] as $tt): ?>
            <option value="<?= $tt ?>" <?= $trangthai===$tt?'selected':'' ?>><?= e($statusMap[$tt][1] ?? $tt) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="mancc" class="form-control" style="width:180px">
            <option value="">Tất cả NCC</option>
            <?php foreach($nccList as $ncc): ?>
            <option value="<?= e($ncc['MANCC']) ?>" <?= $mancc===$ncc['MANCC']?'selected':'' ?>><?= e($ncc['TENNCC']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary">🔍 Lọc</button>
          <a href="nhaphang.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="pnhTable">
            <thead>
              <tr>
                <th>Mã phiếu</th>
                <th>Nhà cung cấp</th>
                <th>Nhân viên kho</th>
                <th>Ngày đặt / Nhận</th>
                <th>SP / SL</th>
                <th>Tổng tiền</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($phieunhap as $pnh):
                $st = $statusMap[$pnh['TRANGTHAI']] ?? ['#6b7280','—'];
                $tt = $ttMap[$pnh['TRANGTHAI_THANHTOAN']] ?? ['#6b7280','—'];
              ?>
              <tr>
                <td><span style="font-family:monospace;font-weight:700;color:var(--blue-light)"><?= e($pnh['MAPNH']) ?></span></td>
                <td>
                  <div style="font-weight:600;font-size:13px"><?= e($pnh['TENNCC']) ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= e($pnh['MANCC']) ?></div>
                </td>
                <td style="font-size:13px"><?= e($pnh['TENNV'] ?? '—') ?></td>
                <td>
                  <div style="font-size:12px"><?= $pnh['NGAY_DATMUA'] ? date('d/m/Y',strtotime($pnh['NGAY_DATMUA'])) : '—' ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= $pnh['NGAYNHAN'] ? '📥 '.date('d/m/Y',strtotime($pnh['NGAYNHAN'])) : '—' ?></div>
                </td>
                <td style="text-align:center">
                  <div style="font-weight:600"><?= $pnh['so_san_pham'] ?> SP</div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= formatNum($pnh['tong_sl'] ?? 0) ?> cái</div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--text-primary)"><?= formatVND($pnh['TONGCONG_PNH'] ?? 0) ?></div>
                  <div style="font-size:11px;color:var(--text-muted)">VAT <?= $pnh['THUE_VAT'] ?>% / CK <?= $pnh['CHIETKHAU'] ?>%</div>
                </td>
                <td>
                  <span style="background:<?= $tt[0] ?>22;color:<?= $tt[0] ?>;border:1px solid <?= $tt[0] ?>44;padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600"><?= $tt[1] ?></span>
                </td>
                <td>
                  <span style="background:<?= $st[0] ?>22;color:<?= $st[0] ?>;border:1px solid <?= $st[0] ?>44;padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600"><?= $st[1] ?></span>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="nhaphang_chitiet.php?mapnh=<?= e($pnh['MAPNH']) ?>" class="btn-icon" title="Chi tiết">👁️</a>
                    <?php if($pnh['TRANGTHAI'] === 'ChoDuyet'): ?>
                    <a href="nhaphang_sua.php?mapnh=<?= e($pnh['MAPNH']) ?>" class="btn-icon" title="Sửa">✏️</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($phieunhap)): ?>
              <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📦</div><p>Không có phiếu nhập hàng nào</p></div></td></tr>
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
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
