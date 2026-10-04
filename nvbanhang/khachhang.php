<?php
// ================================================================
// NVBANHANG - Quản lý Khách hàng
// nvbanhang/khachhang.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['NhanVienBan','Admin']);

$pageTitle = 'Quản lý Khách hàng';
define('NVB_URL', BASE_URL . '/nvbanhang');

$search  = trim($_GET['q'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;

$where = ['1=1']; $params = []; $types = '';
if ($search) {
    $where[] = '(kh.TENKH LIKE ? OR kh.MAKH LIKE ? OR kh.SDT_KH LIKE ? OR kh.EMAIL_KH LIKE ?)';
    $params  = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
    $types  .= 'ssss';
}
$whereSQL = implode(' AND ', $where);

$total = (int)(dbFetchOne("SELECT COUNT(*) AS cnt FROM KHACHHANG kh WHERE $whereSQL", $params, $types)['cnt'] ?? 0);
$pages  = max(1, ceil($total / $perPage));
$offset = ($page-1)*$perPage;

$khachhang = dbFetch("
    SELECT kh.*,
        (SELECT COUNT(*) FROM HOADON WHERE MAKH=kh.MAKH) AS so_hd,
        (SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH=kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')) AS tong_chi,
        (SELECT MAX(NGAYLAP)    FROM HOADON WHERE MAKH=kh.MAKH) AS lan_mua_cuoi
    FROM KHACHHANG kh WHERE $whereSQL
    ORDER BY tong_chi DESC, so_hd DESC
    LIMIT $perPage OFFSET $offset
", $params, $types);

// KPI
$kpiTotal   = dbFetchOne("SELECT COUNT(*) AS c FROM KHACHHANG");
$kpiMoi     = dbFetchOne("SELECT COUNT(*) AS c FROM TAIKHOAN WHERE LOAI_TAIKHOAN='KhachHang' AND MONTH(NGAYTAO)=MONTH(CURDATE()) AND YEAR(NGAYTAO)=YEAR(CURDATE())");
$kpiVip     = dbFetchOne("SELECT COUNT(*) AS c FROM KHACHHANG kh WHERE (SELECT SUM(TONGTIEN_HD) FROM HOADON WHERE MAKH=kh.MAKH AND TRANGTHAI IN ('DaGiao','HoanThanh')) >= 50000000");
$colors = ['#22c55e','#4f6ef7','#8b5cf6','#f59e0b','#06b6d4','#ec4899','#ef4444'];

include __DIR__ . '/includes/header.php';
?>
<style>
:root{--bg:#0d1117;--card:#161b22;--card2:#1c2333;--bd:#30363d;--tx:#e6edf3;--mt:#8b949e;--gr:#22c55e;--bl:#4f6ef7;}
*{box-sizing:border-box;}body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--tx);}
.shell{display:flex;min-height:100vh;}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.topbar{height:54px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.topbar h2{font-size:15px;font-weight:700;color:var(--tx);margin:0;}
.tb-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.tb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#16a34a);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;}
.ct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:16px;}
.ph h1{font-size:20px;font-weight:800;color:var(--tx);margin:0;}
.ph p{font-size:12px;color:var(--mt);margin:4px 0 0;}
.kgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.kcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;}
.kico{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.kval{font-size:24px;font-weight:900;line-height:1;}.klbl{font-size:10px;color:var(--mt);}
/* Search bar */
.fbar{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:10px 14px;display:flex;gap:8px;align-items:center;}
.fbar input{flex:1;background:var(--card2);border:1px solid var(--bd);border-radius:7px;padding:7px 10px;color:var(--tx);font-size:12px;outline:none;}
.fbar input::placeholder{color:var(--mt);}
.btn{padding:7px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;text-decoration:none;transition:all .15s;}
.btn-pri{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;}
.btn-out{background:transparent;border:1px solid var(--bd)!important;color:var(--mt);}
/* Grid cards */
.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;}
.ccard{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:16px;
  transition:border-color .2s,transform .15s;cursor:pointer;}
.ccard:hover{border-color:#444;transform:translateY(-2px);}
.cc-head{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.cc-av{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:16px;color:#fff;flex-shrink:0;}
.cc-name{font-size:14px;font-weight:700;color:var(--tx);}
.cc-id{font-family:monospace;font-size:11px;color:var(--mt);}
.cc-vip{padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.3);margin-left:auto;}
.cc-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;font-size:11px;}
.ccl{color:var(--mt);margin-bottom:1px;}.ccv{font-weight:600;color:var(--tx);}
.cc-bar{margin-top:10px;height:3px;background:rgba(255,255,255,.08);border-radius:2px;overflow:hidden;}
.cc-bar-fill{height:100%;border-radius:2px;background:linear-gradient(90deg,#22c55e,#4f6ef7);}
.empty{padding:40px;text-align:center;color:var(--mt);}
/* Pagination */
.pg{display:flex;align-items:center;justify-content:space-between;margin-top:4px;}
.pga{display:flex;gap:3px;}
.pga a{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--mt);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.pga a:hover{background:rgba(255,255,255,.05);color:var(--tx);}
.pga a.active{background:rgba(34,197,94,.2);border-color:rgba(34,197,94,.4);color:var(--gr);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px;}
</style>

<div class="shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main">

    <!-- Topbar -->
    <div class="topbar">
      <span style="font-size:18px">👤</span>
      <h2>Quản lý Khách hàng</h2>
      <div class="tb-right">
        <div style="text-align:right">
          <div style="font-size:12px;font-weight:700;color:var(--tx)"><?= htmlspecialchars($_SESSION['tennv']??'NV Bán hàng') ?></div>
          <div style="font-size:10px;color:var(--mt)">NV Bán hàng</div>
        </div>
        <div class="tb-av"><?= mb_strtoupper(mb_substr($_SESSION['tennv']??'NV',0,2)) ?></div>
      </div>
    </div>

    <div class="ct">
      <!-- Page header -->
      <div class="ph">
        <h1>👤 Danh sách Khách hàng</h1>
        <p>Thông tin khách hàng &amp; lịch sử mua hàng</p>
      </div>

      <!-- KPI -->
      <div class="kgrid">
        <div class="kcard">
          <div class="kico" style="background:rgba(79,110,247,.12)">👥</div>
          <div>
            <div class="klbl">TỔNG KHÁCH HÀNG</div>
            <div class="kval" style="color:var(--bl)"><?= $kpiTotal['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">tài khoản đã đăng ký</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(34,197,94,.1)">🆕</div>
          <div>
            <div class="klbl">KHÁCH HÀNG MỚI THÁNG NÀY</div>
            <div class="kval" style="color:var(--gr)"><?= $kpiMoi['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đăng ký trong tháng</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(245,158,11,.12)">⭐</div>
          <div>
            <div class="klbl">KHÁCH VIP (&gt;50 triệu)</div>
            <div class="kval" style="color:#f59e0b"><?= $kpiVip['c']??0 ?></div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">khách hàng VIP</div>
          </div>
        </div>
      </div>

      <!-- Search -->
      <form method="GET" class="fbar">
        <span style="color:var(--mt)">🔍</span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Tìm theo tên, mã KH, SĐT, email..."/>
        <button type="submit" class="btn btn-pri">Tìm kiếm</button>
        <a href="khachhang.php" class="btn btn-out">↩ Reset</a>
      </form>

      <!-- Customer Grid -->
      <?php if(empty($khachhang)): ?>
      <div class="empty">
        <div style="font-size:40px;opacity:.3">👤</div>
        <p style="font-size:14px;margin:10px 0 0">Không tìm thấy khách hàng nào</p>
      </div>
      <?php else: ?>
      <div class="cgrid">
        <?php foreach($khachhang as $idx => $kh):
          $color  = $colors[$idx % count($colors)];
          $initials = mb_strtoupper(mb_substr($kh['TENKH']??'KH', 0, 2));
          $chiTieu  = floatval($kh['tong_chi'] ?? 0);
          $isVip    = $chiTieu >= 50000000;
          $maxChi   = 100000000;
          $pct      = min(100, round($chiTieu / $maxChi * 100));
        ?>
        <div class="ccard" onclick="window.location='xem_khachhang.php?makh=<?= htmlspecialchars($kh['MAKH']) ?>'">
          <div class="cc-head">
            <div class="cc-av" style="background:<?= $color ?>"><?= $initials ?></div>
            <div style="flex:1;min-width:0">
              <div class="cc-name"><?= htmlspecialchars($kh['TENKH']??'—') ?></div>
              <div class="cc-id"><?= htmlspecialchars($kh['MAKH']) ?></div>
            </div>
            <?php if($isVip): ?>
            <span class="cc-vip">⭐ VIP</span>
            <?php endif; ?>
          </div>

          <div class="cc-grid">
            <div>
              <div class="ccl">SĐT</div>
              <div class="ccv"><?= htmlspecialchars($kh['SDT_KH']??'—') ?></div>
            </div>
            <div>
              <div class="ccl">Số đơn hàng</div>
              <div class="ccv"><?= $kh['so_hd']??0 ?> đơn</div>
            </div>
            <div>
              <div class="ccl">Tổng chi tiêu</div>
              <div class="ccv" style="color:var(--gr)"><?= formatVND($chiTieu) ?></div>
            </div>
            <div>
              <div class="ccl">Mua gần nhất</div>
              <div class="ccv"><?= $kh['lan_mua_cuoi']?date('d/m/Y',strtotime($kh['lan_mua_cuoi'])):'Chưa mua' ?></div>
            </div>
          </div>

          <?php if($pct > 0): ?>
          <div class="cc-bar">
            <div class="cc-bar-fill" style="width:<?= $pct ?>%"></div>
          </div>
          <div style="font-size:10px;color:var(--mt);margin-top:4px;text-align:right"><?= $pct ?>% mục tiêu 100tr</div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if($pages>1): ?>
      <div class="pg">
        <span style="font-size:11px;color:var(--mt)">
          Trang <?= $page ?> / <?= $pages ?> &bull; <?= $total ?> khách hàng
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
      <?php endif; ?>
    </div><!-- /ct -->
  </div><!-- /main -->
</div><!-- /shell -->
<?php include __DIR__ . '/includes/footer.php'; ?>
