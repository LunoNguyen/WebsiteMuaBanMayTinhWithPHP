<?php
// ================================================================
// KHO - Đơn hàng cần xuất kho
// kho/donhang.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['NhanVienKho','Admin']);

$pageTitle = 'Đơn hàng cần xuất kho';
define('KHO_URL', BASE_URL . '/kho');

// Action: xác nhận đã chuẩn bị / đã giao
$action = $_GET['action'] ?? '';
$msg = ''; $msgType = 'success';

if ($action === 'xuatkho' && !empty($_GET['mahd'])) {
    $mahd = $_GET['mahd'];
    $hd = dbFetchOne("SELECT TRANGTHAI FROM HOADON WHERE MAHD=?", [$mahd], 's');
    if ($hd && $hd['TRANGTHAI'] === 'DaXacNhan') {
        dbExecute("UPDATE HOADON SET TRANGTHAI='DangGiao' WHERE MAHD=?", [$mahd], 's');
        $msg = "Đã xuất kho &amp; bàn giao vận chuyển đơn $mahd!";
    }
}

// Filters — NV kho chỉ thấy đơn DaXacNhan + DangGiao
$search    = trim($_GET['q'] ?? '');
$trangthai = $_GET['trangthai'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 12;

// Mặc định chỉ hiện đơn liên quan đến kho
$baseWhere = "hd.TRANGTHAI IN ('DaXacNhan','DangGiao','DaGiao')";
$where = [$baseWhere]; $params = []; $types = '';
if ($search) {
    $where[] = '(hd.MAHD LIKE ? OR kh.TENKH LIKE ? OR hd.TEN_NGUOINHAN LIKE ?)';
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]); $types .= 'sss';
}
if ($trangthai) { $where[] = 'hd.TRANGTHAI=?'; $params[] = $trangthai; $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$joinSQL  = "FROM HOADON hd
             LEFT JOIN KHACHHANG kh ON hd.MAKH=kh.MAKH
             LEFT JOIN NHANVIEN  nv ON hd.MANV=nv.MANV";

$total  = (int)(dbFetchOne("SELECT COUNT(*) AS cnt $joinSQL WHERE $whereSQL", $params, $types)['cnt'] ?? 0);
$pages  = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$donhang = dbFetch("
    SELECT hd.*, kh.TENKH, kh.SDT_KH, nv.TENNV,
        (SELECT COUNT(*) FROM CHITIETHOADON WHERE MAHD=hd.MAHD) AS so_sp,
        (SELECT SUM(SOLUONG) FROM CHITIETHOADON WHERE MAHD=hd.MAHD) AS tong_sl
    $joinSQL WHERE $whereSQL
    ORDER BY FIELD(hd.TRANGTHAI,'DaXacNhan','DangGiao','DaGiao'), hd.NGAYLAP DESC
    LIMIT $perPage OFFSET $offset
", $params, $types);

// KPI cho kho
$kpiCho    = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='DaXacNhan'");
$kpiDangGiao = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='DangGiao'");
$kpiHomNay = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='DaGiao' AND DATE(NGAYLAP)=CURDATE()");

$stMap = [
    'DaXacNhan' => ['#f59e0b','Chờ xuất kho','cwarn'],
    'DangGiao'  => ['#4f6ef7','Đang giao',   'csc'],
    'DaGiao'    => ['#22c55e','Đã giao',      'cok'],
];

include __DIR__ . '/includes/header.php';
?>
<style>
/* Reuse WMS styles */
:root{--wc:#161b22;--wc2:#1c2333;--wb:#30363d;--wt:#e6edf3;--wm:#8b949e;--wr:10px;}
*{box-sizing:border-box;}
body{background:#0d1117;font-family:'Inter',sans-serif;}
.wsh{display:flex;min-height:100vh;}
.wsb{width:218px;min-width:218px;background:var(--wc);border-right:1px solid var(--wb);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.wlogo{padding:15px 13px 11px;border-bottom:1px solid var(--wb);}
.wlogo-r{display:flex;align-items:center;gap:9px;}
.wli{width:33px;height:33px;border-radius:8px;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;}
.wln{font-size:14px;font-weight:800;color:var(--wt);}.wls{font-size:10px;color:var(--wm);letter-spacing:1px;text-transform:uppercase;}
.wng{padding:11px 9px 4px;}.wnl{font-size:10px;color:var(--wm);letter-spacing:1.2px;text-transform:uppercase;padding:0 7px;margin-bottom:4px;}
.wni{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;color:var(--wm);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .15s;}
.wni:hover{background:rgba(255,255,255,.05);color:var(--wt);}
.wni.active{background:rgba(79,110,247,.15);color:#7b93f7;font-weight:600;}
.wni .ni{font-size:14px;flex-shrink:0;}
.nbg{margin-left:auto;background:#ef4444;color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;}
.wrb{margin:9px;padding:9px 11px;border-radius:8px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);}
.rdot{display:inline-block;width:7px;height:7px;border-radius:50%;background:#22c55e;margin-right:4px;animation:pl 2s infinite;}
@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}
.wmn{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#0d1117;}
.wct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:14px;}
.wph{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.wph h1{font-size:20px;font-weight:800;color:var(--wt);margin:0;}
.wph p{font-size:12px;color:var(--wm);margin:4px 0 0;}
.wkg{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.wk{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:14px 16px;display:flex;align-items:center;gap:12px;}
.wki{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.wkv{font-size:26px;font-weight:900;line-height:1;}.wkl{font-size:10px;color:var(--wm);}
.wfl{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:10px 14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.wfi{flex:1;min-width:180px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 9px;}
.wfi input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wfi input::placeholder{color:var(--wm);}
.wse{background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 8px;color:var(--wt);font-size:12px;outline:none;cursor:pointer;}
.wtw{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);overflow:hidden;}
.wth{padding:10px 14px;border-bottom:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wth h3{font-size:13px;font-weight:700;color:var(--wt);margin:0;}
.wcnt{background:rgba(79,110,247,.15);color:#7b93f7;border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.wt{width:100%;border-collapse:collapse;}
table.wt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--wm);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--wb);text-align:left;}
table.wt td{padding:10px 12px;font-size:13px;color:var(--wt);border-bottom:1px solid rgba(48,54,61,.5);vertical-align:middle;}
table.wt tr:last-child td{border-bottom:none;}
table.wt tr:hover td{background:rgba(255,255,255,.02);}
.wcode{font-family:monospace;font-weight:700;color:#7b93f7;}
.wpil{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.wbn{padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;transition:all .15s;text-decoration:none;white-space:nowrap;}
.wb-pri{background:linear-gradient(135deg,#4f6ef7,#7c3aed);color:#fff;}
.wb-suc{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;}
.wb-out{background:transparent;border:1px solid var(--wb)!important;color:var(--wm);}
.wb-out:hover{border-color:#4f6ef7!important;color:var(--wt);}
.btn-ic{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;text-decoration:none;transition:all .15s;}
.btn-ic:hover{background:rgba(255,255,255,.08);}
.alert-msg{padding:11px 16px;border-radius:8px;font-size:13px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#22c55e;}
.wni.active{background:rgba(79,110,247,.15);color:#7b93f7;font-weight:600;}
/* Topbar minimal */
.kho-topbar{height:52px;background:var(--wc);border-bottom:1px solid var(--wb);display:flex;align-items:center;padding:0 18px;gap:12px;flex-shrink:0;}
.kho-topbar h2{font-size:14px;font-weight:700;color:var(--wt);margin:0;}
.kto-user{display:flex;align-items:center;gap:8px;margin-left:auto;}
.kto-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;}
.wpg{padding:10px 14px;border-top:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wpa{display:flex;gap:3px;}
.wpl2{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--wm);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.wpl2:hover{background:rgba(255,255,255,.05);color:var(--wt);}
.wpl2.active{background:rgba(79,110,247,.2);border-color:rgba(79,110,247,.4);color:#7b93f7;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--wb);border-radius:2px;}
.cok{background:rgba(34,197,94,.14);color:#22c55e;border:1px solid rgba(34,197,94,.3);}
.csc{background:rgba(79,110,247,.14);color:#7b93f7;border:1px solid rgba(79,110,247,.3);}
.cwarn{background:rgba(245,158,11,.14);color:#f59e0b;border:1px solid rgba(245,158,11,.3);}
</style>

<div class="wsh">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="wmn">

  <!-- Topbar -->
  <div class="kho-topbar">
    <span style="font-size:18px">🚚</span>
    <h2>Đơn hàng cần xuất kho</h2>
    <div class="kto-user">
      <div style="text-align:right">
        <div style="font-size:12px;font-weight:700;color:var(--wt)"><?= htmlspecialchars($_SESSION['tennv']??'NV Kho') ?></div>
        <div style="font-size:10px;color:var(--wm)">Nhân viên Kho &bull; <?= htmlspecialchars($_SESSION['manv']??'') ?></div>
      </div>
      <div class="kto-av"><?= mb_strtoupper(mb_substr($_SESSION['tennv']??'NK',0,2)) ?></div>
    </div>
  </div>

  <div class="wct">
    <?php if($msg): ?>
    <div class="alert-msg">✅ <?= $msg ?></div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="wph">
      <div>
        <h1>🚚 Đơn hàng cần Xuất kho &amp; Bàn giao</h1>
        <p>Soạn hàng theo đơn đã xác nhận · Kiểm tra trước khi giao vận chuyển</p>
      </div>
    </div>

    <!-- KPI -->
    <div class="wkg">
      <div class="wk">
        <div class="wki" style="background:rgba(245,158,11,.12)">📋</div>
        <div>
          <div class="wkl">CHỜ XUẤT KHO</div>
          <div class="wkv" style="color:#f59e0b"><?= $kpiCho['c']??0 ?></div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn hàng chờ soạn</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(79,110,247,.12)">🚚</div>
        <div>
          <div class="wkl">ĐANG VẬN CHUYỂN</div>
          <div class="wkv" style="color:#7b93f7"><?= $kpiDangGiao['c']??0 ?></div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn đang trên đường</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(34,197,94,.1)">✅</div>
        <div>
          <div class="wkl">GIAO THÀNH CÔNG HÔM NAY</div>
          <div class="wkv" style="color:#22c55e"><?= $kpiHomNay['c']??0 ?></div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn đã hoàn tất</div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="wfl">
      <div class="wfi">
        <span style="color:var(--wm)">🔍</span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm mã đơn, tên khách hàng..."/>
      </div>
      <select name="trangthai" class="wse" onchange="this.form.submit()">
        <option value="">Tất cả trạng thái kho</option>
        <option value="DaXacNhan"  <?= $trangthai==='DaXacNhan' ?'selected':'' ?>>⏳ Chờ xuất kho</option>
        <option value="DangGiao"   <?= $trangthai==='DangGiao'  ?'selected':'' ?>>🚚 Đang giao</option>
        <option value="DaGiao"     <?= $trangthai==='DaGiao'    ?'selected':'' ?>>✅ Đã giao</option>
      </select>
      <button type="submit" class="wbn wb-out" style="padding:6px 12px">🔍 Lọc</button>
      <a href="donhang.php" class="wbn wb-out" style="padding:6px 12px">↩ Reset</a>
    </form>

    <!-- Table -->
    <div class="wtw">
      <div class="wth">
        <h3>📦 Danh sách Đơn hàng cần Xử lý (Kho)</h3>
        <span class="wcnt"><?= $total ?> đơn</span>
      </div>
      <table class="wt">
        <thead>
          <tr>
            <th>MÃ ĐƠN</th>
            <th>KHÁCH HÀNG</th>
            <th>SẢN PHẨM</th>
            <th>TỔNG TIỀN</th>
            <th>NGÀY ĐẶT</th>
            <th>ĐỊA CHỈ GIAO</th>
            <th>TRẠNG THÁI</th>
            <th>THAO TÁC KHO</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($donhang as $dh):
            $st = $stMap[$dh['TRANGTHAI']] ?? ['#6b7280','—',''];
          ?>
          <tr>
            <td><span class="wcode"><?= htmlspecialchars($dh['MAHD']) ?></span></td>
            <td>
              <div style="font-weight:600;font-size:12px"><?= htmlspecialchars($dh['TENKH']??$dh['TEN_NGUOINHAN']??'—') ?></div>
              <div style="font-size:10px;color:var(--wm)"><?= htmlspecialchars($dh['SDT_KH']??$dh['SDT_NGUOINHAN']??'') ?></div>
            </td>
            <td style="text-align:center">
              <div style="font-weight:700"><?= $dh['so_sp']??0 ?> SP</div>
              <div style="font-size:10px;color:var(--wm)"><?= $dh['tong_sl']??0 ?> chiếc</div>
            </td>
            <td style="font-weight:700"><?= formatVND($dh['TONGTIEN_HD']??0) ?></td>
            <td style="font-size:11px"><?= $dh['NGAYLAP']?date('d/m/Y',strtotime($dh['NGAYLAP'])):'—' ?></td>
            <td style="font-size:11px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= htmlspecialchars($dh['DIACHI_GIAO']??$dh['DIACHI_KH']??'—') ?>
            </td>
            <td>
              <span class="wpil <?= $st[2] ?>"
                style="background:<?= $st[0] ?>22;color:<?= $st[0] ?>;border:1px solid <?= $st[0] ?>44">
                <?= $st[1] ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:5px;align-items:center">
                <?php if($dh['TRANGTHAI']==='DaXacNhan'): ?>
                <a href="?action=xuatkho&mahd=<?= htmlspecialchars($dh['MAHD']) ?>"
                   class="wbn wb-suc" style="padding:5px 10px;font-size:11px"
                   onclick="return confirm('Xác nhận xuất kho đơn <?= htmlspecialchars($dh['MAHD']) ?>?')">
                  📤 Xuất kho
                </a>
                <?php elseif($dh['TRANGTHAI']==='DangGiao'): ?>
                <span style="font-size:11px;color:var(--wm)">Đang vận chuyển</span>
                <?php else: ?>
                <span style="font-size:11px;color:#22c55e">✅ Hoàn tất</span>
                <?php endif; ?>
                <a href="xem_chitiet.php?mahd=<?= htmlspecialchars($dh['MAHD']) ?>" class="btn-ic" title="Xem chi tiết">👁️</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($donhang)): ?>
          <tr><td colspan="8">
            <div style="padding:32px;text-align:center;color:var(--wm)">
              <div style="font-size:36px;opacity:.3">🚚</div>
              <p style="font-size:13px;margin:8px 0 0">Không có đơn hàng cần xử lý</p>
            </div>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <?php if($pages>1): ?>
      <div class="wpg">
        <span style="font-size:11px;color:var(--wm)">
          Hiển thị <?= ($page-1)*$perPage+1 ?>–<?= min($page*$perPage,$total) ?> trên <?= $total ?> đơn
        </span>
        <div class="wpa">
          <?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="wpl2">‹</a><?php endif; ?>
          <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="wpl2 <?= $p===$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="wpl2">›</a><?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div><!-- /wct -->
</div><!-- /wmn -->
</div><!-- /wsh -->
<?php include __DIR__ . '/includes/footer.php'; ?>
