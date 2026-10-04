<?php
// ================================================================
// Quản lý Đơn hàng - admin/donhang.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Quản lý Đơn hàng';
$breadcrumb = ['Quản lý', 'Đơn hàng'];

// Xử lý action
$action = $_GET['action'] ?? '';
$msg = ''; $msgType = 'success';

$validTransitions = [
    'ChoXacNhan' => 'DaXacNhan',
    'DaXacNhan'  => 'DangGiao',
    'DangGiao'   => 'DaGiao',
    'DaGiao'     => 'HoanThanh',
];

if ($action === 'next' && !empty($_GET['mahd'])) {
    $mahd = $_GET['mahd'];
    $hd = dbFetchOne("SELECT TRANGTHAI FROM HOADON WHERE MAHD=?", [$mahd], 's');
    if ($hd && isset($validTransitions[$hd['TRANGTHAI']])) {
        $next = $validTransitions[$hd['TRANGTHAI']];
        dbExecute("UPDATE HOADON SET TRANGTHAI=? WHERE MAHD=?", [$next, $mahd], 'ss');
        // Nếu DaGiao → cập nhật thanh toán COD
        if ($next === 'DaGiao') {
            dbExecute("UPDATE THANHTOAN SET TRANGTHAI='DaThanhToan', NGAY_THANHTOAN=NOW() WHERE MAHD=? AND PHUONG_THUC='COD' AND TRANGTHAI='ChoThanhToan'", [$mahd], 's');
        }
        $msg = "Cập nhật trạng thái đơn hàng $mahd thành công!";
    }
}

if ($action === 'huy' && !empty($_GET['mahd'])) {
    $mahd = $_GET['mahd'];
    dbExecute("UPDATE HOADON SET TRANGTHAI='DaHuy' WHERE MAHD=? AND TRANGTHAI IN ('ChoXacNhan','DaXacNhan')", [$mahd], 's');
    $msg = "Đã hủy đơn hàng $mahd.";
    $msgType = 'info';
}

// Filters
$search    = trim($_GET['q'] ?? '');
$trangthai = $_GET['trangthai'] ?? '';
$ptgh      = $_GET['ptgh'] ?? '';
$from      = $_GET['from'] ?? '';
$to        = $_GET['to'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 12;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(hd.MAHD LIKE ? OR kh.TENKH LIKE ? OR hd.TEN_NGUOINHAN LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    $types .= 'sss';
}
if ($trangthai) { $where[] = 'hd.TRANGTHAI=?'; $params[] = $trangthai; $types .= 's'; }
if ($ptgh)      { $where[] = 'hd.PHUONG_THUC_GH=?'; $params[] = $ptgh; $types .= 's'; }
if ($from)      { $where[] = 'DATE(hd.NGAYLAP)>=?'; $params[] = $from; $types .= 's'; }
if ($to)        { $where[] = 'DATE(hd.NGAYLAP)<=?'; $params[] = $to;   $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$joinSQL  = "FROM HOADON hd LEFT JOIN KHACHHANG kh ON hd.MAKH=kh.MAKH LEFT JOIN NHANVIEN nv ON hd.MANV=nv.MANV LEFT JOIN THANHTOAN tt ON hd.MAHD=tt.MAHD";

$countRow = dbFetchOne("SELECT COUNT(DISTINCT hd.MAHD) AS cnt $joinSQL WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$donhang = dbFetch("SELECT DISTINCT hd.MAHD, hd.NGAYLAP, hd.TRANGTHAI, hd.TONGTIEN_HD, hd.TONGTIEN_GIAM,
    hd.TEN_NGUOINHAN, hd.SDT_NGUOINHAN, hd.DIACHI_GIAOHANG, hd.PHUONG_THUC_GH,
    kh.TENKH, kh.SDT_KH,
    nv.TENNV,
    tt.PHUONG_THUC AS PT_TT, tt.TRANGTHAI AS TT_TT
    $joinSQL WHERE $whereSQL
    ORDER BY hd.NGAYLAP DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

// Thống kê nhanh
$stats = dbFetch("SELECT TRANGTHAI, COUNT(*) AS cnt FROM HOADON GROUP BY TRANGTHAI");
$statMap = [];
foreach($stats as $s) $statMap[$s['TRANGTHAI']] = $s['cnt'];

$actionLabel = ['ChoXacNhan'=>'Xác nhận','DaXacNhan'=>'Bắt đầu giao','DangGiao'=>'Đã giao','DaGiao'=>'Hoàn thành'];

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Đơn hàng</h1>
          <p>Tổng <strong style="color:var(--blue-light)"><?= formatNum($total) ?></strong> đơn hàng</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('hdTable','donhang_<?= date('Ymd') ?>')">Xuất CSV</button>
          <a href="donhang_them.php" class="btn btn-primary">＋ Tạo đơn mới</a>
        </div>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msgType==='success' ? 'success' : ($msgType==='info'?'info':'danger') ?>" data-dismiss>
        <?= $msgType==='success'?'✅':($msgType==='info'?'ℹ️':'❌') ?> <?= e($msg) ?>
      </div>
      <?php endif; ?>

      <!-- Quick Stats -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <?php
        $quickStats = [
          ['ChoXacNhan','🟡','Chờ xác nhận','orange'],
          ['DaXacNhan', '🔵','Đã xác nhận', 'blue'],
          ['DangGiao',  '🟣','Đang giao',   'purple'],
          ['HoanThanh', '🟢','Hoàn thành',  'green'],
          ['DaHuy',     '🔴','Đã hủy',      'red'],
        ];
        foreach($quickStats as [$key,$icon,$label,$clr]):
          $cnt = $statMap[$key] ?? 0;
        ?>
        <a href="?trangthai=<?= $key ?>" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;transition:var(--transition);<?= $trangthai===$key?'border-color:var(--blue);background:var(--blue-glow)':'' ?>">
          <span><?= $icon ?></span>
          <span style="font-size:13px;color:var(--text-secondary)"><?= $label ?></span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px"><?= $cnt ?></strong>
        </a>
        <?php endforeach; ?>
        <?php if($trangthai): ?>
          <a href="donhang.php" class="btn btn-sm btn-outline">Xóa lọc</a>
        <?php endif; ?>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:240px">
            <span class="si"><?= icon('search') ?></span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm mã HD, tên khách..." />
          </div>
          <select name="ptgh" class="form-control" style="width:150px">
            <option value="">Phương thức GH</option>
            <option value="GiaoHang" <?= $ptgh==='GiaoHang'?'selected':'' ?>>Giao hàng</option>
            <option value="TaiQuay"  <?= $ptgh==='TaiQuay'?'selected':'' ?>>Tại quầy</option>
          </select>
          <input type="date" name="from" value="<?= e($from) ?>" class="form-control" style="width:140px" title="Từ ngày" />
          <input type="date" name="to"   value="<?= e($to) ?>"   class="form-control" style="width:140px" title="Đến ngày" />
          <input type="hidden" name="trangthai" value="<?= e($trangthai) ?>" />
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="donhang.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="hdTable">
            <thead>
              <tr>
                <th>Mã HD</th>
                <th>Khách hàng</th>
                <th>Thời gian</th>
                <th>Giá trị</th>
                <th>Phương thức</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($donhang as $hd): ?>
              <tr>
                <td>
                  <a href="donhang_chitiet.php?mahd=<?= e($hd['MAHD']) ?>" style="font-family:monospace;font-weight:700;color:var(--blue-light)">
                    <?= e($hd['MAHD']) ?>
                  </a>
                </td>
                <td>
                  <div style="font-weight:600;font-size:13px"><?= e($hd['TEN_NGUOINHAN'] ?? $hd['TENKH'] ?? 'Khách vãng lai') ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= e($hd['SDT_NGUOINHAN'] ?? $hd['SDT_KH'] ?? '') ?></div>
                  <?php if($hd['TENNV']): ?>
                  <div style="font-size:11px;color:var(--text-muted)">NV: <?= e($hd['TENNV']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="font-size:13px"><?= date('d/m/Y', strtotime($hd['NGAYLAP'])) ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= date('H:i', strtotime($hd['NGAYLAP'])) ?></div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--text-primary)"><?= formatVND($hd['TONGTIEN_HD']) ?></div>
                  <?php if($hd['TONGTIEN_GIAM'] > 0): ?>
                  <div style="font-size:11px;color:var(--green)">Giảm <?= formatVND($hd['TONGTIEN_GIAM']) ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-size:13px">
                  <?= $hd['PHUONG_THUC_GH'] === 'GiaoHang' ? '🚚 Giao hàng' : '🏪 Tại quầy' ?>
                </td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)">
                    <?php
                      $ptIcon = ['QR'=>'📱','COD'=>'💵','ChuyenKhoan'=>'🏦','TienMat'=>'💴'];
                      echo ($ptIcon[$hd['PT_TT']] ?? '💳') . ' ' . e($hd['PT_TT'] ?? '—');
                    ?>
                  </div>
                  <?= $hd['TT_TT'] ? statusBadge($hd['TT_TT'], 'thanhtoan') : '' ?>
                </td>
                <td><?= statusBadge($hd['TRANGTHAI'], 'hoadon') ?></td>
                <td>
                  <div style="display:flex;gap:5px;flex-wrap:wrap">
                    <a href="donhang_chitiet.php?mahd=<?= e($hd['MAHD']) ?>" class="btn-icon" title="Chi tiết"><?= icon('eye', 15) ?></a>
                    <?php if(isset($validTransitions[$hd['TRANGTHAI']])): ?>
                    <a href="donhang.php?action=next&mahd=<?= e($hd['MAHD']) ?>"
                       class="btn btn-sm btn-success"
                       onclick="return confirm('Chuyển trạng thái đơn <?= e($hd['MAHD']) ?>?')"
                       style="padding:4px 10px;font-size:11px">
                      <?= e($actionLabel[$hd['TRANGTHAI']]) ?>
                    </a>
                    <?php endif; ?>
                    <?php if(in_array($hd['TRANGTHAI'],['ChoXacNhan','DaXacNhan'])): ?>
                    <a href="donhang.php?action=huy&mahd=<?= e($hd['MAHD']) ?>"
                       class="btn-icon" title="Hủy đơn" style="border-color:rgba(200,30,30,0.3)"
                       onclick="return confirm('Hủy đơn hàng <?= e($hd['MAHD']) ?>?')">🚫</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($donhang)): ?>
              <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><?= icon('cart') ?></div><p>Không có đơn hàng nào</p></div></td></tr>
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
