<?php
// ================================================================
// Quản lý Sản phẩm - admin/sanpham.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/storage.php';
requireRole(['Admin']);

$pageTitle = 'Quản lý Sản phẩm';
$breadcrumb = ['Quản lý', 'Sản phẩm'];

// Xử lý action
$action  = $_GET['action']  ?? '';
$msg     = '';
$msgType = 'success';

if ($action === 'delete' && !empty($_GET['masp'])) {
    $masp = $_GET['masp'];
    // Kiểm tra ràng buộc FK trước khi xóa
    $used = dbFetchOne("SELECT COUNT(*) AS cnt FROM CHITIETHOADON WHERE MASP=?", [$masp], 's');
    if ($used && $used['cnt'] > 0) {
        $msg = "Không thể xóa sản phẩm đã có trong hóa đơn!";
        $msgType = 'error';
    } else {
        dbExecute("DELETE FROM DANHSACHANH WHERE MASP=?", [$masp], 's');
        dbExecute("DELETE FROM MOTA WHERE MASP=?", [$masp], 's');
        dbExecute("DELETE FROM LICHSUGIA WHERE MASP=?", [$masp], 's');
        dbExecute("DELETE FROM CT_KHUYENMAI WHERE MASP=?", [$masp], 's');
        dbExecute("DELETE FROM CT_GIOHANG WHERE MASP=?", [$masp], 's');
        dbExecute("DELETE FROM SANPHAM WHERE MASP=?", [$masp], 's');
        $msg = "Đã xóa sản phẩm thành công!";
    }
}

if ($action === 'toggle' && !empty($_GET['masp'])) {
    $masp = $_GET['masp'];
    $sp = dbFetchOne("SELECT TRANGTHAI FROM SANPHAM WHERE MASP=?", [$masp], 's');
    if ($sp) {
        $newStatus = $sp['TRANGTHAI'] === 'DangBan' ? 'NgungBan' : 'DangBan';
        dbExecute("UPDATE SANPHAM SET TRANGTHAI=? WHERE MASP=?", [$newStatus, $masp], 'ss');
        $msg = "Đã cập nhật trạng thái sản phẩm!";
    }
}

// Bộ lọc
$search    = trim($_GET['q'] ?? '');
$maloai    = $_GET['maloai'] ?? '';
$mansx     = $_GET['mansx'] ?? '';
$trangthai = $_GET['trangthai'] ?? '';
$filter    = $_GET['filter'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 10;

// Build WHERE
$where  = ['1=1'];
$params = [];
$types  = '';

if ($search) {
    $where[] = '(sp.TENSP LIKE ? OR sp.MASP LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($maloai) { $where[] = 'sp.MALOAI=?'; $params[] = $maloai; $types .= 's'; }
if ($mansx)  { $where[] = 'sp.MANSX=?';  $params[] = $mansx;  $types .= 's'; }
if ($trangthai) { $where[] = 'sp.TRANGTHAI=?'; $params[] = $trangthai; $types .= 's'; }
if ($filter === 'low_stock') { $where[] = 'sp.SOLUONGTON <= 20'; }

$whereSQL = implode(' AND ', $where);

// Count
$countRow = dbFetchOne("SELECT COUNT(*) AS cnt FROM SANPHAM sp WHERE $whereSQL",
    $params, $types);
$total   = $countRow['cnt'] ?? 0;
$pages   = max(1, ceil($total / $perPage));
$offset  = ($page - 1) * $perPage;

// Fetch paginated (dùng LIMIT ... OFFSET vì prepared statement)
$conn = getDB();
$limitSQL = "SELECT sp.*, lsp.TENLOAI, nsx.TENNSX, ncc.TENNCC,
             (SELECT URL_ANH FROM DANHSACHANH WHERE MASP=sp.MASP AND LA_ANH_CHINH=1 LIMIT 1) AS ANH_CHINH
             FROM SANPHAM sp
             LEFT JOIN LOAISANPHAM lsp ON sp.MALOAI=lsp.MALOAI
             LEFT JOIN NHASANXUA   nsx ON sp.MANSX=nsx.MANSX
             LEFT JOIN NHACUNGCAP  ncc ON sp.MANCC=ncc.MANCC
             WHERE $whereSQL
             ORDER BY sp.NGAYTHEM DESC
             LIMIT $perPage OFFSET $offset";
$sanpham = dbFetch($limitSQL, $params, $types);

// Dropdown filter data
$loaiList = dbFetch("SELECT * FROM LOAISANPHAM ORDER BY TENLOAI");
$nsxList  = dbFetch("SELECT * FROM NHASANXUA ORDER BY TENNSX");

include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="page-content">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Sản phẩm</h1>
          <p>Tổng cộng <strong style="color:var(--blue-light)"><?= formatNum($total) ?></strong> sản phẩm<?= $search ? ' khớp "'.e($search).'"' : '' ?></p>
        </div>
        <div class="page-header-right">
          <a href="sanpham_them.php" class="btn btn-primary">＋ Thêm sản phẩm</a>
          <button class="btn btn-outline" onclick="exportTableCSV('spTable','sanpham')">Xuất CSV</button>
        </div>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'danger' ?>" data-dismiss>
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= e($msg) ?>
      </div>
      <?php endif; ?>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET" action="" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:280px">
            <span class="si"><?= icon('search') ?></span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm tên hoặc mã sản phẩm..." />
          </div>
          <select name="maloai" class="form-control" style="width:180px">
            <option value="">Tất cả loại SP</option>
            <?php foreach($loaiList as $l): ?>
            <option value="<?= e($l['MALOAI']) ?>" <?= $maloai === $l['MALOAI'] ? 'selected' : '' ?>>
              <?= e($l['TENLOAI']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <select name="mansx" class="form-control" style="width:160px">
            <option value="">Tất cả NSX</option>
            <?php foreach($nsxList as $n): ?>
            <option value="<?= e($n['MANSX']) ?>" <?= $mansx === $n['MANSX'] ? 'selected' : '' ?>>
              <?= e($n['TENNSX']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <select name="trangthai" class="form-control" style="width:150px">
            <option value="">Tất cả trạng thái</option>
            <option value="DangBan" <?= $trangthai==='DangBan' ? 'selected' : '' ?>>Đang Bán</option>
            <option value="HetHang" <?= $trangthai==='HetHang' ? 'selected' : '' ?>>Hết Hàng</option>
            <option value="NgungBan" <?= $trangthai==='NgungBan' ? 'selected' : '' ?>>Ngừng Bán</option>
          </select>
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="sanpham.php" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="spTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Sản phẩm</th>
                <th>Loại</th>
                <th>NSX</th>
                <th>Giá bán</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Ngày thêm</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($sanpham as $i => $sp): ?>
              <tr>
                <td style="color:var(--text-muted);font-size:12px"><?= $offset + $i + 1 ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <label class="sp-thumb" title="Bấm để tải ảnh lên">
                      <?php if (!empty($sp['ANH_CHINH'])): ?>
                        <img src="<?= e(storageUrl($sp['ANH_CHINH'])) ?>" alt="" loading="lazy" onerror="this.remove()">
                      <?php endif; ?>
                      <?= icon('laptop', 18) ?>
                      <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-masp="<?= e($sp['MASP']) ?>" onchange="uploadAnhSP(this)">
                    </label>
                    <div>
                      <div style="font-weight:600;font-size:13px;color:var(--text-primary)"><?= e($sp['TENSP']) ?></div>
                      <div style="font-size:11px;color:var(--text-muted)">
                        <code style="background:var(--bg-main);padding:1px 5px;border-radius:3px"><?= e($sp['MASP']) ?></code>
                        &bull; <?= e($sp['DONVT'] ?? 'Cái') ?>
                        <?php if($sp['MANCC']): ?> &bull; <?= e($sp['TENNCC']) ?><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td style="font-size:13px">
                  <span style="background:rgba(58,86,228,0.1);color:var(--blue-light);padding:2px 8px;border-radius:6px;font-size:12px">
                    <?= e($sp['TENLOAI'] ?? '—') ?>
                  </span>
                </td>
                <td style="font-size:13px;color:var(--text-secondary)"><?= e($sp['TENNSX'] ?? '—') ?></td>
                <td>
                  <div style="font-weight:700;font-size:13px;color:var(--text-primary)"><?= formatVND($sp['DONGIA_SP']) ?></div>
                </td>
                <td>
                  <?php
                    $stock = intval($sp['SOLUONGTON']);
                    $sc = $stock <= 5 ? 'stock-danger' : ($stock <= 20 ? 'stock-warning' : 'stock-ok');
                  ?>
                  <span class="stock-badge <?= $sc ?>"><?= formatNum($stock) ?></span>
                </td>
                <td><?= statusBadge($sp['TRANGTHAI'], 'sanpham') ?></td>
                <td style="font-size:12px;color:var(--text-secondary)"><?= date('d/m/Y', strtotime($sp['NGAYTHEM'])) ?></td>
                <td>
                  <div style="display:flex;gap:6px;align-items:center">
                    <a href="sanpham_sua.php?masp=<?= e($sp['MASP']) ?>" class="btn-icon" title="Sửa"><?= icon('pencil', 15) ?></a>
                    <a href="sanpham.php?action=toggle&masp=<?= e($sp['MASP']) ?>"
                       class="btn-icon" title="<?= $sp['TRANGTHAI']==='DangBan' ? 'Ngừng bán' : 'Bật bán' ?>" aria-label="<?= $sp['TRANGTHAI']==='DangBan' ? 'Ngừng bán' : 'Bật bán' ?>" aria-label="<?= $sp['TRANGTHAI']==='DangBan' ? 'Ngừng bán' : 'Bật bán' ?>"
                       onclick="return confirm('Thay đổi trạng thái sản phẩm?')">
                       <?= $sp['TRANGTHAI']==='DangBan' ? icon('pause', 15) : icon('play', 15) ?>
                    </a>
                    <a href="sanpham.php?action=delete&masp=<?= e($sp['MASP']) ?>"
                       class="btn-icon" title="Xóa" style="border-color:rgba(200,30,30,0.3)"
                       onclick="return confirm('Xóa sản phẩm <?= e(addslashes($sp['TENSP'])) ?>?')"><?= icon('trash', 15) ?></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($sanpham)): ?>
              <tr><td colspan="9">
                <div class="empty-state">
                  <div class="empty-icon"><?= icon('laptop') ?></div>
                  <p>Không tìm thấy sản phẩm nào</p>
                </div>
              </td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <?php if($pages > 1): ?>
        <div class="pagination">
          <?php if($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" class="page-link">‹</a>
          <?php endif; ?>
          <?php for($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
               class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if($page < $pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" class="page-link">›</a>
          <?php endif; ?>
          <span style="font-size:12px;color:var(--text-muted);margin-left:8px">
            Trang <?= $page ?>/<?= $pages ?> — <?= formatNum($total) ?> sản phẩm
          </span>
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
// Tải ảnh sản phẩm lên MinIO, xong thì thay ảnh trong ô ngay tại chỗ
function uploadAnhSP(input) {
  const file = input.files[0];
  if (!file) return;
  const box = input.closest('.sp-thumb');
  const fd = new FormData();
  fd.append('masp', input.dataset.masp);
  fd.append('anh', file);
  box.classList.add('is-loading');
  fetch('<?= ADMIN_URL ?>/api/upload_anh.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (!d.success) { showToast(d.error || 'Tải ảnh thất bại', 'error'); return; }
      if (d.la_chinh) {
        let img = box.querySelector('img');
        if (!img) { img = document.createElement('img'); img.alt = ''; box.prepend(img); }
        img.src = d.url;
      }
      showToast('Đã tải ảnh lên', 'success');
    })
    .catch(() => showToast('Không kết nối được máy chủ', 'error'))
    .finally(() => { box.classList.remove('is-loading'); input.value = ''; });
}
</script>
