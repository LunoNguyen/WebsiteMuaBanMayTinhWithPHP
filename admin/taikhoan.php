<?php
// ================================================================
// Quản lý Tài khoản - admin/taikhoan.php
// ================================================================
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Quản lý Tài khoản';
$breadcrumb = ['Quản lý', 'Tài khoản'];

$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? '';

if ($action === 'toggle' && !empty($_GET['matk'])) {
    $matk = $_GET['matk'];
    $tk = dbFetchOne("SELECT TRANGTHAI FROM TAIKHOAN WHERE MATK=?", [$matk], 's');
    if ($tk) {
        $newTT = $tk['TRANGTHAI'] === 'HoatDong' ? 'KhoaTamThoi' : 'HoatDong';
        dbExecute("UPDATE TAIKHOAN SET TRANGTHAI=? WHERE MATK=?", [$newTT, $matk], 'ss');
        $msg = "Đã cập nhật trạng thái tài khoản!";
    }
}

// Filters
$search   = trim($_GET['q'] ?? '');
$loaifil  = $_GET['loai'] ?? '';
$ttfil    = $_GET['trangthai'] ?? '';
$page     = max(1, intval($_GET['page'] ?? 1));
$perPage  = 12;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(tk.MATK LIKE ? OR tk.EMAIL_TK LIKE ? OR kh.TENKH LIKE ? OR nv.TENNV LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ssss';
}
if ($loaifil) { $where[] = 'tk.LOAI_TAIKHOAN=?'; $params[] = $loaifil; $types .= 's'; }
if ($ttfil)   { $where[] = 'tk.TRANGTHAI=?';      $params[] = $ttfil;  $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM TAIKHOAN tk
    LEFT JOIN KHACHHANG kh ON tk.MAKH=kh.MAKH
    LEFT JOIN NHANVIEN  nv ON tk.MANV=nv.MANV
    WHERE $whereSQL", $params, $types);
$total = $countRow['cnt'] ?? 0;
$pages = max(1, ceil($total/$perPage));
$offset = ($page-1)*$perPage;

$taikhoan = dbFetch("SELECT tk.*,
    kh.TENKH, kh.SDT_KH,
    nv.TENNV, cv.TENCV
    FROM TAIKHOAN tk
    LEFT JOIN KHACHHANG kh ON tk.MAKH=kh.MAKH
    LEFT JOIN NHANVIEN  nv ON tk.MANV=nv.MANV
    LEFT JOIN CHUCVU    cv ON nv.MACV=cv.MACV
    WHERE $whereSQL
    ORDER BY tk.NGAYTAO DESC
    LIMIT $perPage OFFSET $offset", $params, $types);

// Count by type
$typeCounts = dbFetch("SELECT LOAI_TAIKHOAN, COUNT(*) AS cnt FROM TAIKHOAN GROUP BY LOAI_TAIKHOAN");
$typeMap = [];
foreach($typeCounts as $t) $typeMap[$t['LOAI_TAIKHOAN']] = $t['cnt'];

$loaiIcon  = ['Admin'=>'👑','NhanVien'=>'👔','KhachHang'=>'👤'];
$loaiColor = ['Admin'=>'var(--orange)','NhanVien'=>'var(--blue)','KhachHang'=>'var(--green)'];
$ttColor   = ['HoatDong'=>'var(--green)','KhoaTamThoi'=>'var(--orange)','KhoaVinhVien'=>'var(--red)'];
$ttLabel   = ['HoatDong'=>'Hoạt động','KhoaTamThoi'=>'Khoá tạm','KhoaVinhVien'=>'Khoá vĩnh viễn'];

include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">

      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Tài khoản</h1>
          <p>Tổng <strong style="color:var(--blue-light)"><?= formatNum($total) ?></strong> tài khoản trong hệ thống</p>
        </div>
        <div class="page-header-right">
          <a href="taikhoan_them.php" class="btn btn-primary">＋ Tạo tài khoản</a>
        </div>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-success" data-dismiss><?= icon('check') ?> <?= e($msg) ?></div>
      <?php endif; ?>

      <!-- Type stats chips -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <?php foreach(['Admin','NhanVien','KhachHang'] as $lt): ?>
        <a href="?loai=<?= $lt ?>" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;<?= $loaifil===$lt?'border-color:var(--blue);background:var(--blue-glow)':'' ?>">
          <span><?= $loaiIcon[$lt] ?></span>
          <span style="font-size:13px;color:var(--text-secondary)"><?= $lt ?></span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px"><?= $typeMap[$lt] ?? 0 ?></strong>
        </a>
        <?php endforeach; ?>
        <?php if($loaifil || $ttfil): ?><a href="taikhoan.php" class="btn btn-sm btn-outline">Xóa lọc</a><?php endif; ?>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:260px">
            <span class="si"><?= icon('search') ?></span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm mã TK, email, tên..." />
          </div>
          <select name="trangthai" class="form-control" style="width:160px">
            <option value="">Tất cả trạng thái</option>
            <option value="HoatDong"     <?= $ttfil==='HoatDong'?'selected':'' ?>>Hoạt động</option>
            <option value="KhoaTamThoi"  <?= $ttfil==='KhoaTamThoi'?'selected':'' ?>>Khoá tạm</option>
            <option value="KhoaVinhVien" <?= $ttfil==='KhoaVinhVien'?'selected':'' ?>>Khoá vĩnh viễn</option>
          </select>
          <input type="hidden" name="loai" value="<?= e($loaifil) ?>" />
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="taikhoan.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Tài khoản</th>
                <th>Loại</th>
                <th>Người dùng</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Cập nhật</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($taikhoan as $tk):
                $loai = $tk['LOAI_TAIKHOAN'];
                $icon = $loaiIcon[$loai] ?? '👤';
                $color = $loaiColor[$loai] ?? 'var(--text-muted)';
                $name = $tk['TENKH'] ?? $tk['TENNV'] ?? 'Không xác định';
              ?>
              <tr>
                <td>
                  <div style="font-family:monospace;font-weight:700;font-size:13px;color:var(--blue-light)"><?= e($tk['MATK']) ?></div>
                  <div style="font-size:12px;color:var(--text-muted)"><?= icon('mail') ?> <?= e($tk['EMAIL_TK']) ?></div>
                </td>
                <td>
                  <span style="background:color-mix(in srgb,<?= $color ?> 12%,transparent);color:<?= $color ?>;border:1px solid color-mix(in srgb,<?= $color ?> 30%,transparent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    <?= $icon ?> <?= e($loai) ?>
                  </span>
                </td>
                <td>
                  <div style="font-size:13px;font-weight:600"><?= e($name) ?></div>
                  <?php if($tk['TENCV']): ?>
                  <div style="font-size:11px;color:var(--text-muted)"><?= e($tk['TENCV']) ?></div>
                  <?php elseif($tk['SDT_KH']): ?>
                  <div style="font-size:11px;color:var(--text-muted)"><?= icon('phone') ?> <?= e($tk['SDT_KH']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php $ttC = $ttColor[$tk['TRANGTHAI']] ?? 'var(--text-secondary)'; ?>
                  <span style="background:color-mix(in srgb,<?= $ttC ?> 12%,transparent);color:<?= $ttC ?>;border:1px solid color-mix(in srgb,<?= $ttC ?> 30%,transparent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    <?= $ttLabel[$tk['TRANGTHAI']] ?? $tk['TRANGTHAI'] ?>
                  </span>
                </td>
                <td style="font-size:12px;color:var(--text-secondary)"><?= date('d/m/Y H:i', strtotime($tk['NGAYTAO'])) ?></td>
                <td style="font-size:12px;color:var(--text-muted)"><?= $tk['NGAY_CAPNHAT'] ? date('d/m/Y H:i', strtotime($tk['NGAY_CAPNHAT'])) : '—' ?></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="taikhoan_sua.php?matk=<?= e($tk['MATK']) ?>" class="btn-icon" title="Sửa"><?= icon('pencil', 15) ?></a>
                    <?php if($tk['LOAI_TAIKHOAN'] !== 'Admin'): ?>
                    <a href="taikhoan.php?action=toggle&matk=<?= urlencode($tk['MATK']) ?>"
                       class="btn-icon" title="<?= $tk['TRANGTHAI']==='HoatDong'?'Khoá':'Mở khoá' ?>"
                       onclick="return confirm('Thay đổi trạng thái tài khoản?')"
                       style="border-color:<?= $tk['TRANGTHAI']==='HoatDong'?'rgba(200,30,30,0.3)':'rgba(21,128,61,0.3)' ?>">
                      <?= $tk['TRANGTHAI']==='HoatDong'?'🔒':'🔓' ?>
                    </a>
                    <?php endif; ?>
                    <button class="btn-icon" title="Reset mật khẩu" onclick="showToast('Tính năng đang phát triển','info')"><?= icon('refresh', 15) ?></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($taikhoan)): ?>
              <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('key', 15) ?></div><p>Không tìm thấy tài khoản nào</p></div></td></tr>
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
