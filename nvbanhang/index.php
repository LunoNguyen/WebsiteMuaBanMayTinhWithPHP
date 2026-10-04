<?php
// ================================================================
// NVBANHANG - Dashboard: Quản lý Đơn hàng
// nvbanhang/index.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['NhanVienBan','Admin']);

$pageTitle = 'Quản lý Đơn hàng';
define('NVB_URL', BASE_URL . '/nvbanhang');

// Actions
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
        if ($next === 'DaGiao') {
            dbExecute("UPDATE THANHTOAN SET TRANGTHAI='DaThanhToan',NGAY_THANHTOAN=NOW() WHERE MAHD=? AND PHUONG_THUC='COD' AND TRANGTHAI='ChoThanhToan'", [$mahd], 's');
        }
        $msg = "Cập nhật trạng thái đơn $mahd thành công!";
    }
}
if ($action === 'huy' && !empty($_GET['mahd'])) {
    $mahd = $_GET['mahd'];
    dbExecute("UPDATE HOADON SET TRANGTHAI='DaHuy' WHERE MAHD=? AND TRANGTHAI IN ('ChoXacNhan','DaXacNhan')", [$mahd], 's');
    $msg = "Đã hủy đơn hàng $mahd."; $msgType = 'info';
}

// Filters
$search    = trim($_GET['q'] ?? '');
$trangthai = $_GET['trangthai'] ?? '';
$from      = $_GET['from'] ?? '';
$to        = $_GET['to'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 12;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(hd.MAHD LIKE ? OR kh.TENKH LIKE ? OR hd.TEN_NGUOINHAN LIKE ?)';
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]); $types .= 'sss';
}
if ($trangthai) { $where[] = 'hd.TRANGTHAI=?'; $params[] = $trangthai; $types .= 's'; }
if ($from)      { $where[] = 'DATE(hd.NGAYLAP)>=?'; $params[] = $from; $types .= 's'; }
if ($to)        { $where[] = 'DATE(hd.NGAYLAP)<=?'; $params[] = $to;   $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$joinSQL  = "FROM HOADON hd LEFT JOIN KHACHHANG kh ON hd.MAKH=kh.MAKH LEFT JOIN NHANVIEN nv ON hd.MANV=nv.MANV";

$total  = (int)(dbFetchOne("SELECT COUNT(*) AS cnt $joinSQL WHERE $whereSQL", $params, $types)['cnt'] ?? 0);
$pages  = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$donhang = dbFetch("
    SELECT hd.*, kh.TENKH, kh.SDT_KH, nv.TENNV,
        (SELECT COUNT(*) FROM CHITIETHOADON WHERE MAHD=hd.MAHD) AS so_sp
    $joinSQL WHERE $whereSQL
    ORDER BY hd.NGAYLAP DESC
    LIMIT $perPage OFFSET $offset
", $params, $types);

// KPI
$kpiCho   = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='ChoXacNhan'");
$kpiGiao  = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='DangGiao'");
$kpiHT    = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='HoanThanh' AND MONTH(NGAYLAP)=MONTH(CURDATE())");
$kpiDT    = dbFetchOne("SELECT SUM(TONGTIEN_HD) AS t FROM HOADON WHERE TRANGTHAI IN ('DaGiao','HoanThanh') AND MONTH(NGAYLAP)=MONTH(CURDATE())");

$stMap = [
    'ChoXacNhan' => ['#f59e0b','Chờ Xác Nhận'],
    'DaXacNhan'  => ['#4f6ef7','Đã Xác Nhận'],
    'DangGiao'   => ['#8b5cf6','Đang Giao'],
    'DaGiao'     => ['#06b6d4','Đã Giao'],
    'HoanThanh'  => ['#22c55e','Hoàn Thành'],
    'DaHuy'      => ['#ef4444','Đã Hủy'],
];

include __DIR__ . '/includes/header.php';
?>
<style>
:root{--bg:#0d1117;--card:#161b22;--card2:#1c2333;--bd:#30363d;--tx:#e6edf3;--mt:#8b949e;--gr:#22c55e;--bl:#4f6ef7;}
*{box-sizing:border-box;}
body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--tx);}
.shell{display:flex;min-height:100vh;}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
/* Topbar */
.topbar{height:54px;background:var(--card);border-bottom:1px solid var(--bd);
  display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.topbar h2{font-size:15px;font-weight:700;color:var(--tx);margin:0;}
.tb-search{flex:1;max-width:350px;}
.tb-search form{display:flex;gap:6px;}
.tb-search input{flex:1;background:var(--card2);border:1px solid var(--bd);border-radius:8px;
  padding:7px 12px;color:var(--tx);font-size:12px;outline:none;}
.tb-search input::placeholder{color:var(--mt);}
.tb-search button{background:var(--bl);color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer;}
.tb-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.tb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#16a34a);
  display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;}
/* Content */
.ct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:16px;}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
.ph h1{font-size:20px;font-weight:800;color:var(--tx);margin:0;}
.ph p{font-size:12px;color:var(--mt);margin:4px 0 0;}
/* KPI */
.kgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.kcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:14px 16px;
  display:flex;align-items:center;gap:12px;}
.kico{width:42px;height:42px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.kval{font-size:24px;font-weight:900;line-height:1;}
.klbl{font-size:10px;color:var(--mt);margin-top:1px;}
/* Filter */
.fbar{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:10px 14px;
  display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.fbar input,.fbar select{background:var(--card2);border:1px solid var(--bd);border-radius:7px;
  padding:7px 10px;color:var(--tx);font-size:12px;outline:none;color-scheme:dark;}
.fbar input::placeholder{color:var(--mt);}
.btn{padding:7px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;
  display:flex;align-items:center;gap:5px;border:none;text-decoration:none;transition:all .15s;white-space:nowrap;}
.btn-pri{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;}
.btn-out{background:transparent;border:1px solid var(--bd)!important;color:var(--mt);}
.btn-out:hover{border-color:var(--bl)!important;color:var(--tx);}
.btn-sm-blue{background:rgba(79,110,247,.15);color:#7b93f7;border:1px solid rgba(79,110,247,.3)!important;padding:5px 10px;font-size:11px;}
.btn-sm-red{background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.3)!important;padding:5px 10px;font-size:11px;}
/* Table */
.tcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;overflow:hidden;}
.thead-row{padding:11px 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.thead-row h3{font-size:13px;font-weight:700;color:var(--tx);margin:0;}
.cnt{background:rgba(34,197,94,.15);color:#22c55e;border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--mt);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--bd);text-align:left;}
table.dt td{padding:9px 12px;font-size:13px;color:var(--tx);border-bottom:1px solid rgba(48,54,61,.5);vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:rgba(255,255,255,.02);}
.mono{font-family:monospace;font-weight:700;color:var(--gr);}
.pil{display:inline-flex;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
/* Alert */
.amsg{padding:11px 16px;border-radius:8px;font-size:13px;margin-bottom:2px;}
.amsg.ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#22c55e;}
.amsg.info{background:rgba(79,110,247,.1);border:1px solid rgba(79,110,247,.2);color:#7b93f7;}
/* Pagination */
.pg{padding:10px 14px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.pga{display:flex;gap:3px;}
.pga a{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--mt);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.pga a:hover{background:rgba(255,255,255,.05);color:var(--tx);}
.pga a.active{background:rgba(34,197,94,.2);border-color:rgba(34,197,94,.4);color:var(--gr);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px;}
@media(max-width:1100px){.kgrid{grid-template-columns:repeat(2,1fr);}}
</style>

<div class="shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main">

    <!-- Topbar -->
    <div class="topbar">
      <span style="font-size:18px">🛒</span>
      <h2>Quản lý Đơn hàng</h2>
      <div class="tb-search">
        <form method="GET">
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm mã đơn, khách hàng..."/>
          <button type="submit">🔍</button>
        </form>
      </div>
      <div class="tb-right">
        <div style="text-align:right">
          <div style="font-size:12px;font-weight:700;color:var(--tx)"><?= htmlspecialchars($_SESSION['tennv']??'NV Bán hàng') ?></div>
          <div style="font-size:10px;color:var(--mt)">NV Bán hàng &bull; <?= htmlspecialchars($_SESSION['manv']??'') ?></div>
        </div>
        <div class="tb-av"><?= mb_strtoupper(mb_substr($_SESSION['tennv']??'NV',0,2)) ?></div>
      </div>
    </div>

    <div class="ct">
      <?php if($msg): ?>
      <div class="amsg <?= $msgType==='info'?'info':'ok' ?>">
        <?= $msgType==='info'?'ℹ️':'✅' ?> <?= $msg ?>
      </div>
      <?php endif; ?>

      <!-- Page header -->
      <div class="ph">
        <div>
          <h1>🛒 Quản lý Đơn hàng</h1>
          <p>Xác nhận &amp; Cập nhật trạng thái đơn hàng của khách</p>
        </div>
      </div>

      <!-- KPI -->
      <div class="kgrid">
        <div class="kcard">
          <div class="kico" style="background:rgba(245,158,11,.12)">⏳</div>
          <div>
            <div class="klbl">CHỜ XÁC NHẬN</div>
            <div class="kval" style="color:#f59e0b"><?= $kpiCho['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">cần xử lý ngay</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(139,92,246,.12)">🚚</div>
          <div>
            <div class="klbl">ĐANG VẬN CHUYỂN</div>
            <div class="kval" style="color:#8b5cf6"><?= $kpiGiao['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn trên đường</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(34,197,94,.1)">✅</div>
          <div>
            <div class="klbl">HOÀN THÀNH THÁNG NÀY</div>
            <div class="kval" style="color:var(--gr)"><?= $kpiHT['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn thành công</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(79,110,247,.1)">💰</div>
          <div>
            <div class="klbl">DOANH THU THÁNG</div>
            <div style="font-size:14px;font-weight:900;color:var(--bl);margin-top:2px"><?= formatVND($kpiDT['t']??0) ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn đã hoàn thành</div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" class="fbar">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm mã đơn, tên khách..." style="min-width:200px;flex:1"/>
        <select name="trangthai" onchange="this.form.submit()">
          <option value="">Tất cả trạng thái</option>
          <?php foreach(['ChoXacNhan','DaXacNhan','DangGiao','DaGiao','HoanThanh','DaHuy'] as $tt): ?>
          <option value="<?= $tt ?>" <?= $trangthai===$tt?'selected':'' ?>><?= $stMap[$tt][1]??$tt ?></option>
          <?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" title="Từ ngày">
        <input type="date" name="to"   value="<?= htmlspecialchars($to) ?>"   title="Đến ngày">
        <button type="submit" class="btn btn-pri">🔍 Lọc</button>
        <a href="index.php" class="btn btn-out">↩ Reset</a>
      </form>

      <!-- Table -->
      <div class="tcard">
        <div class="thead-row">
          <h3>📋 Danh sách Đơn hàng</h3>
          <span class="cnt"><?= $total ?> đơn hàng</span>
        </div>
        <table class="dt">
          <thead>
            <tr>
              <th>MÃ ĐƠN</th><th>KHÁCH HÀNG</th><th>SP</th>
              <th>TỔNG TIỀN</th><th>NGÀY ĐẶT</th>
              <th>NV XỬ LÝ</th><th>TRẠNG THÁI</th><th>THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($donhang as $dh):
              $st = $stMap[$dh['TRANGTHAI']] ?? ['#6b7280','—'];
              $canNext = isset($validTransitions[$dh['TRANGTHAI']]);
              $canHuy  = in_array($dh['TRANGTHAI'],['ChoXacNhan','DaXacNhan']);
            ?>
            <tr>
              <td><span class="mono"><?= htmlspecialchars($dh['MAHD']) ?></span></td>
              <td>
                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($dh['TENKH']??$dh['TEN_NGUOINHAN']??'—') ?></div>
                <div style="font-size:11px;color:var(--mt)"><?= htmlspecialchars($dh['SDT_KH']??'') ?></div>
              </td>
              <td style="text-align:center;font-weight:700"><?= $dh['so_sp']??0 ?> SP</td>
              <td style="font-weight:700"><?= formatVND($dh['TONGTIEN_HD']??0) ?></td>
              <td style="font-size:12px"><?= $dh['NGAYLAP']?date('d/m/Y H:i',strtotime($dh['NGAYLAP'])):'—' ?></td>
              <td style="font-size:12px"><?= htmlspecialchars($dh['TENNV']??'Hệ thống') ?></td>
              <td>
                <span class="pil" style="background:<?= $st[0] ?>22;color:<?= $st[0] ?>;border:1px solid <?= $st[0] ?>44">
                  <?= $st[1] ?>
                </span>
              </td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                  <?php if($canNext): ?>
                  <a href="?action=next&mahd=<?= htmlspecialchars($dh['MAHD']) ?>&<?= http_build_query(array_diff_key($_GET,['action'=>'','mahd'=>''])) ?>"
                     class="btn btn-sm-blue"
                     onclick="return confirm('Chuyển trạng thái đơn <?= htmlspecialchars($dh['MAHD']) ?>?')">
                    ▶ Tiếp theo
                  </a>
                  <?php endif; ?>
                  <?php if($canHuy): ?>
                  <a href="?action=huy&mahd=<?= htmlspecialchars($dh['MAHD']) ?>&<?= http_build_query(array_diff_key($_GET,['action'=>'','mahd'=>''])) ?>"
                     class="btn btn-sm-red"
                     onclick="return confirm('Hủy đơn <?= htmlspecialchars($dh['MAHD']) ?>?')">
                    ✕ Hủy
                  </a>
                  <?php endif; ?>
                  <a href="xem_don.php?mahd=<?= htmlspecialchars($dh['MAHD']) ?>"
                     style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none;background:rgba(255,255,255,.05);"
                     title="Xem">👁️</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($donhang)): ?>
            <tr><td colspan="8">
              <div style="padding:32px;text-align:center;color:var(--mt)">
                <div style="font-size:36px;opacity:.3">🛒</div>
                <p style="font-size:13px;margin:8px 0 0">Không có đơn hàng nào</p>
              </div>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if($pages>1): ?>
        <div class="pg">
          <span style="font-size:11px;color:var(--mt)">
            Hiển thị <?= ($page-1)*$perPage+1 ?>–<?= min($page*$perPage,$total) ?> trên <?= $total ?> đơn
          </span>
          <div class="pga">
            <?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">‹</a><?php endif; ?>
            <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="<?= $p===$page?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">›</a><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div><!-- /ct -->
  </div><!-- /main -->
</div><!-- /shell -->
<?php include __DIR__ . '/includes/footer.php'; ?>
