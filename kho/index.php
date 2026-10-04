<?php
// ================================================================
// NHÂN VIÊN KHO - Quản lý Phiếu Nhập Hàng & Kiểm Đếm Kho
// admin/kho_nhap.php
// ================================================================
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Nhân viên Kho - Quản lý Nhập hàng';

// KPI Cards
$kpiPhieu   = dbFetchOne("SELECT COUNT(*) AS tong FROM PHIEUNHAPHANG WHERE MONTH(NGAYTAO)=MONTH(CURDATE()) AND YEAR(NGAYTAO)=YEAR(CURDATE())");
$kpiCho     = dbFetchOne("SELECT COUNT(*) AS tong FROM PHIEUNHAPHANG WHERE TRANGTHAI IN ('ChoDuyet','DaDuyet')");
$kpiTonThap = dbFetchOne("SELECT COUNT(*) AS tong FROM SANPHAM WHERE SOLUONGTON <= 10 AND TRANGTHAI='DangBan'");
$kpiGiaTri  = dbFetchOne("SELECT SUM(TONGCONG_PNH) AS tong FROM PHIEUNHAPHANG WHERE MONTH(NGAYTAO)=MONTH(CURDATE()) AND YEAR(NGAYTAO)=YEAR(CURDATE())");

// Filter
$search  = trim($_GET['q'] ?? '');
$filter  = $_GET['filter'] ?? '';
$mancc   = $_GET['mancc'] ?? '';
$thang   = $_GET['thang'] ?? date('Y-m');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;

$where = ['1=1']; $params = []; $types = '';
if ($search) { $where[] = '(pnh.MAPNH LIKE ? OR ncc.TENNCC LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%"]); $types .= 'ss'; }
if ($filter === 'cho') { $where[] = "pnh.TRANGTHAI IN ('ChoDuyet','DaDuyet')"; }
if ($mancc)  { $where[] = 'pnh.MANCC=?'; $params[] = $mancc; $types .= 's'; }
if ($thang)  { $where[] = "DATE_FORMAT(pnh.NGAYTAO,'%Y-%m')=?"; $params[] = $thang; $types .= 's'; }

$whereSQL = implode(' AND ', $where);
$joinSQL  = "FROM PHIEUNHAPHANG pnh LEFT JOIN NHACUNGCAP ncc ON pnh.MANCC=ncc.MANCC LEFT JOIN NHANVIEN nv ON pnh.MANV=nv.MANV";

$total  = (int)(dbFetchOne("SELECT COUNT(*) AS cnt $joinSQL WHERE $whereSQL", $params, $types)['cnt'] ?? 0);
$pages  = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$phieunhap = dbFetch("SELECT pnh.*, ncc.TENNCC, nv.TENNV,
    (SELECT COUNT(*) FROM CT_PHIEUNHAPHANG WHERE MAPNH=pnh.MAPNH) AS so_sku,
    (SELECT SUM(SOLUONG) FROM CT_PHIEUNHAPHANG WHERE MAPNH=pnh.MAPNH) AS tong_sl
    $joinSQL WHERE $whereSQL ORDER BY pnh.NGAYTAO DESC LIMIT $perPage OFFSET $offset", $params, $types);

$nccList = dbFetch("SELECT MANCC, TENNCC FROM NHACUNGCAP ORDER BY TENNCC");

$selectedMapnh = $_GET['pn'] ?? ($phieunhap[0]['MAPNH'] ?? '');
$detail = null; $ctpnList = [];
if ($selectedMapnh) {
    $detail = dbFetchOne("SELECT pnh.*, ncc.TENNCC, nv.TENNV, nv.MANV FROM PHIEUNHAPHANG pnh LEFT JOIN NHACUNGCAP ncc ON pnh.MANCC=ncc.MANCC LEFT JOIN NHANVIEN nv ON pnh.MANV=nv.MANV WHERE pnh.MAPNH=?", [$selectedMapnh], 's');
    $ctpnList = dbFetch("SELECT ct.*, sp.TENSP, sp.MASP, sp.SOLUONGTON, lsp.TENLOAI, nsx.TENNSX FROM CT_PHIEUNHAPHANG ct JOIN SANPHAM sp ON ct.MASP=sp.MASP LEFT JOIN LOAISANPHAM lsp ON sp.MALOAI=lsp.MALOAI LEFT JOIN NHASANXUA nsx ON sp.MANSX=nsx.MANSX WHERE ct.MAPNH=? ORDER BY ct.MASP", [$selectedMapnh], 's');
}

$stMap = [
    'ChoDuyet'      => ['#f59e0b','Chờ duyệt'],
    'DaDuyet'       => ['#4f6ef7','Đã duyệt'],
    'DaNhan'        => ['#8b5cf6','Đang kiểm đếm'],
    'HoanThanh'     => ['#22c55e','Đã nhập đủ'],
    'DaHuy'         => ['#ef4444','Đã hủy'],
    'DangVanChuyen' => ['#06b6d4','Đang vận chuyển'],
];

include __DIR__ . '/includes/header.php';
?><style>
:root{--wc:#161b22;--wc2:#1c2333;--wb:#30363d;--wt:#e6edf3;--wm:#8b949e;--wr:10px;}
*{box-sizing:border-box;}
body{background:#0d1117;font-family:'Inter',sans-serif;}
.wsh{display:flex;min-height:100vh;}
.wsb{width:218px;min-width:218px;background:var(--wc);border-right:1px solid var(--wb);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.wlogo{padding:15px 13px 11px;border-bottom:1px solid var(--wb);}
.wlogo-r{display:flex;align-items:center;gap:9px;}
.wli{width:33px;height:33px;border-radius:8px;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;}
.wln{font-size:14px;font-weight:800;color:var(--wt);}
.wls{font-size:10px;color:var(--wm);letter-spacing:1px;text-transform:uppercase;}
.wng{padding:11px 9px 4px;}
.wnl{font-size:10px;color:var(--wm);letter-spacing:1.2px;text-transform:uppercase;padding:0 7px;margin-bottom:4px;}
.wni{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;color:var(--wm);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .15s;}
.wni:hover{background:rgba(255,255,255,.05);color:var(--wt);}
.wni.active{background:rgba(79,110,247,.15);color:#7b93f7;font-weight:600;}
.wni .ni{font-size:14px;flex-shrink:0;}
.nbg{margin-left:auto;background:#ef4444;color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;}
.wrb{margin:9px;padding:9px 11px;border-radius:8px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);}
.rdot{display:inline-block;width:7px;height:7px;border-radius:50%;background:#22c55e;margin-right:4px;animation:pl 2s infinite;}
@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}
.wmn{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#0d1117;}
.wtb{height:53px;background:var(--wc);border-bottom:1px solid var(--wb);display:flex;align-items:center;gap:9px;padding:0 15px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.wbc{font-size:11px;color:var(--wm);display:flex;align-items:center;gap:5px;white-space:nowrap;}
.wbc span{color:var(--wt);font-weight:600;}
.wsti{flex:1;max-width:380px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:8px;padding:6px 10px;}
.wsti input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wsti input::placeholder{color:var(--wm);}
.wkbd{background:var(--wb);color:var(--wm);border-radius:4px;padding:1px 5px;font-size:10px;font-family:monospace;}
.wloc{display:flex;align-items:center;gap:6px;padding:5px 10px;background:rgba(79,110,247,.1);border:1px solid rgba(79,110,247,.3);border-radius:8px;font-size:11px;color:#7b93f7;white-space:nowrap;cursor:pointer;}
.wbn{padding:6px 11px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;transition:all .15s;text-decoration:none;white-space:nowrap;}
.wb-out{background:transparent;border:1px solid var(--wb)!important;color:var(--wm);}
.wb-out:hover{border-color:#4f6ef7!important;color:var(--wt);}
.wb-pri{background:linear-gradient(135deg,#4f6ef7,#7c3aed);color:#fff;box-shadow:0 4px 14px rgba(79,110,247,.3);}
.wb-pri:hover{transform:translateY(-1px);}
.wb-suc{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;box-shadow:0 4px 14px rgba(34,197,94,.25);}
.wb-suc:hover{transform:translateY(-1px);}
.wb-sc{background:rgba(6,182,212,.12);border:1px solid rgba(6,182,212,.3)!important;color:#06b6d4;}
.wav{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;border:2px solid rgba(79,110,247,.4);cursor:pointer;flex-shrink:0;}
.wntf{width:31px;height:31px;border-radius:50%;background:var(--wc2);border:1px solid var(--wb);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;font-size:13px;}
.wntd{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid var(--wc);}
.wct{flex:1;overflow:auto;padding:16px;display:flex;flex-direction:column;gap:12px;}
.wph{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.wph h1{font-size:19px;font-weight:800;color:var(--wt);margin:0;}
.wph p{font-size:12px;color:var(--wm);margin:3px 0 0;}
.wkg{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;}
.wk{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:13px 15px;display:flex;align-items:center;gap:11px;}
.wk-al{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.04);}
.wk-dk{background:var(--wc2);}
.wki{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.wkv{font-size:24px;font-weight:900;line-height:1;}
.wkl{font-size:10px;color:var(--wm);margin-top:1px;letter-spacing:.3px;}
.wsp{display:grid;grid-template-columns:1fr 385px;gap:12px;min-height:0;}
.wpla{display:flex;flex-direction:column;gap:9px;}
.wfl{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:9px 13px;display:flex;gap:7px;flex-wrap:wrap;align-items:center;}
.wfi{flex:1;min-width:170px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 9px;}
.wfi input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wfi input::placeholder{color:var(--wm);}
.wse{background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 8px;color:var(--wt);font-size:12px;outline:none;cursor:pointer;color-scheme:dark;}
.wft{display:flex;align-items:center;gap:5px;padding:6px 10px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;font-size:12px;color:var(--wm);cursor:pointer;text-decoration:none;white-space:nowrap;transition:all .15s;}
.wft:hover,.wft.active{background:rgba(79,110,247,.12);border-color:rgba(79,110,247,.4);color:#7b93f7;}
.wfr{font-size:12px;color:var(--wm);cursor:pointer;padding:6px 8px;border-radius:7px;text-decoration:none;}
.wfr:hover{color:var(--wt);}
.wtw{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);overflow:hidden;}
.wth{padding:10px 13px;border-bottom:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wth h3{font-size:13px;font-weight:700;color:var(--wt);margin:0;}
.wcnt{background:rgba(79,110,247,.15);color:#7b93f7;border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.wt{width:100%;border-collapse:collapse;}
table.wt th{padding:8px 10px;font-size:10px;font-weight:700;color:var(--wm);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--wb);text-align:left;white-space:nowrap;}
table.wt td{padding:9px 10px;font-size:13px;color:var(--wt);border-bottom:1px solid rgba(48,54,61,.5);vertical-align:middle;}
table.wt tr:last-child td{border-bottom:none;}
table.wt tr{cursor:pointer;transition:background .1s;}
table.wt tr:hover td{background:rgba(255,255,255,.02);}
table.wt tr.sel td{background:rgba(79,110,247,.08);}
table.wt tr.sel td:first-child{border-left:3px solid #4f6ef7;padding-left:7px;}
.wcode{font-family:monospace;font-weight:700;color:#7b93f7;font-size:13px;}
.wpil{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.wpg{padding:9px 13px;border-top:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wpa{display:flex;gap:3px;}
.wpl2{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:var(--wm);background:transparent;border:1px solid transparent;text-decoration:none;cursor:pointer;transition:all .15s;}
.wpl2:hover{background:rgba(255,255,255,.05);color:var(--wt);}
.wpl2.active{background:rgba(79,110,247,.2);border-color:rgba(79,110,247,.4);color:#7b93f7;}
.wpr{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);display:flex;flex-direction:column;overflow:hidden;position:sticky;top:65px;max-height:calc(100vh - 82px);}
.wdh{padding:12px 14px;border-bottom:1px solid var(--wb);flex-shrink:0;}
.wdpn{font-family:monospace;font-size:17px;font-weight:900;color:#7b93f7;}
.wdsu{font-size:10px;color:var(--wm);margin-top:1px;}
.wdnc{font-size:13px;font-weight:700;color:var(--wt);margin-top:6px;}
.wdmt{font-size:10px;color:var(--wm);}
.wdpr{font-size:19px;font-weight:900;color:#22c55e;white-space:nowrap;}
.wdb{flex:1;overflow-y:auto;padding:12px 14px;display:flex;flex-direction:column;gap:11px;}
.wprw{background:var(--wc2);border:1px solid var(--wb);border-radius:8px;padding:10px;}
.wprr{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:11px;}
.wptr{height:5px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;}
.wpfl{height:100%;border-radius:3px;background:linear-gradient(90deg,#4f6ef7,#22c55e);transition:width .6s;}
.wsl{font-size:10px;font-weight:700;color:var(--wm);text-transform:uppercase;letter-spacing:1px;display:flex;justify-content:space-between;}
.wpc{background:var(--wc2);border:1px solid var(--wb);border-radius:8px;overflow:hidden;margin-top:6px;}
.wpc-h{padding:8px 10px;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--wb);}
.wpic{width:30px;height:30px;border-radius:6px;background:rgba(79,110,247,.12);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.wpnm{font-size:11px;font-weight:700;color:var(--wt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.wpsk{font-size:10px;color:var(--wm);margin-top:1px;font-family:monospace;}
.wch{display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;}
.cok{background:rgba(34,197,94,.14);color:#22c55e;border:1px solid rgba(34,197,94,.3);}
.csc{background:rgba(79,110,247,.14);color:#7b93f7;border:1px solid rgba(79,110,247,.3);}
.cda{background:rgba(239,68,68,.14);color:#ef4444;border:1px solid rgba(239,68,68,.3);}
.wpc-b{padding:8px 10px;}
.wpcr{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;font-size:10px;}
.pll{color:var(--wm);margin-bottom:1px;}
.plv{font-weight:700;color:var(--wt);}
.pll2{color:#ef4444;}
.wsr{margin-top:6px;padding:5px 8px;background:rgba(0,0,0,.2);border-radius:6px;font-size:10px;color:var(--wm);display:flex;align-items:center;justify-content:space-between;}
.wlb{padding:8px 10px;background:rgba(79,110,247,.08);border:1px solid rgba(79,110,247,.2);border-radius:8px;display:flex;align-items:center;justify-content:space-between;}
.wda{padding:10px 13px;border-top:1px solid var(--wb);display:flex;flex-direction:column;gap:6px;flex-shrink:0;}
.war{display:flex;gap:6px;}
.war .wbn{flex:1;justify-content:center;}
.wtrg{padding:7px 10px;background:rgba(34,197,94,.05);border:1px solid rgba(34,197,94,.15);border-radius:7px;display:flex;align-items:center;justify-content:space-between;}
.wtrg .tt{font-size:10px;color:var(--wm);}
.wtrg .tt strong{color:var(--wt);}
.wrdy{background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3);border-radius:5px;padding:2px 7px;font-size:10px;font-weight:800;}
.wde{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--wm);gap:8px;padding:24px;text-align:center;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--wb);border-radius:2px;}
@media(max-width:1200px){.wkg{grid-template-columns:repeat(2,1fr);}.wsp{grid-template-columns:1fr;}.wpr{position:static;max-height:none;}}
</style>
<?php
// PHP PART: HTML output for kho_nhap.php
// This file is included via: include __DIR__ . '/kho_part3.php';
// But we write directly to kho_nhap.php via PowerShell append
?>
<div class="wsh">
<!-- SIDEBAR -->
<aside class="wsb">
  <div class="wlogo">
    <div class="wlogo-r">
      <div class="wli">N</div>
      <div><div class="wln">NEXUS WMS</div><div class="wls">Kho Van &amp; Logistics</div></div>
    </div>
  </div>
  <div class="wng">
    <div class="wnl">Nghiep Vu Van Hanh</div>
    <a href="kho_nhap.php" class="wni active">
      <span class="ni">&#128230;</span>
      <div><div>Quan ly Nhap hang</div><div style="font-size:10px;color:var(--wm)">Nha cung cap &amp; Phieu PNK</div></div>
      <?php $pc=dbFetchOne("SELECT COUNT(*) AS c FROM PHIEUNHAPHANG WHERE TRANGTHAI IN ('ChoDuyet','DaDuyet')"); if(!empty($pc['c'])): ?><span class="nbg"><?= $pc['c'] ?></span><?php endif; ?>
    </a>
    <a href="donhang.php" class="wni"><span class="ni">&#128666;</span><div><div>Quan ly Don hang</div><div style="font-size:10px;color:var(--wm)">Soan hang &amp; Dieu phoi xuat</div></div></a>
  </div>
  <div class="wng">
    <div class="wnl">He Thong</div>
    <a href="index.php"   class="wni"><span class="ni">&#128202;</span> Dashboard</a>
    <a href="sanpham.php" class="wni"><span class="ni">&#128187;</span> San pham</a>
    <a href="baocao.php"  class="wni"><span class="ni">&#128200;</span> Bao cao</a>
  </div>
  <div style="margin-top:auto;padding:9px;">
    <div class="wrb">
      <div><span class="rdot"></span><span style="font-size:12px;font-weight:700;color:#22c55e">Online</span></div>
      <div style="font-size:10px;color:var(--wm);margin-top:2px">Nhan vien kho &mdash; Van hanh kho trung tam</div>
      <div style="font-size:10px;color:var(--wm);margin-top:5px"><?= e($_SESSION['tennv']??'NV Kho') ?> &bull; <?= e($_SESSION['manv']??'NV004') ?></div>
    </div>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="wni" style="color:#ef4444;margin-top:5px;"><span class="ni">&#128682;</span> Đăng xuất</a>
  </div>
</aside>

<!-- MAIN -->
<div class="wmn">
  <!-- TopBar -->
  <header class="wtb">
    <div class="wbc">KHO VAN &rsaquo; <span>NHAP HANG &amp; NHA CUNG CAP</span> &rsaquo; PHIEUNHAP &rsaquo; <span>CTPN</span></div>
    <form method="GET" style="flex:1;max-width:380px;display:flex">
      <div class="wsti" style="flex:1">
        <span style="color:var(--wm)">&#128269;</span>
        <input id="searchInput" type="text" name="q" value="<?= e($search) ?>" placeholder="Tim ma phieu nhap, ma don hang, SKU hang hoa..."/>
        <span class="wkbd">F2</span>
      </div>
      <input type="hidden" name="pn" value="<?= e($selectedMapnh) ?>">
    </form>
    <div class="wloc">&#128205; Tong kho Tan Binh (Kho Chinh) &#9660;</div>
    <div class="wntf">&#128276;<span class="wntd"></span></div>
    <div style="display:flex;align-items:center;gap:7px">
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--wt)"><?= e($_SESSION['tennv']??'Tran Quoc Bao') ?></div>
        <div style="font-size:10px;color:var(--wm)">NV Kho &bull; <?= e($_SESSION['manv']??'NV004') ?></div>
      </div>
      <div class="wav"><?= mb_strtoupper(mb_substr($_SESSION['tennv']??'NK',0,2)) ?></div>
    </div>
    <div style="display:flex;gap:6px;margin-left:3px">
      <button class="wbn wb-sc" onclick="openBarcodeModal()">&#128247; Quet ma vach Barcode/QR &nbsp;<span class="wkbd">F3</span></button>
      <button class="wbn wb-out" onclick="showToast('Dang xuat bien ban kiem ke...')">&#128203; Xuat bien ban kiem ke</button>
    </div>
  </header>

  <!-- Content -->
  <div class="wct">
    <div class="wph">
      <div>
        <h1>Quan ly Phieu Nhap Hang &amp; Kiem Dem Kho</h1>
        <p>Nhan hang tu nha cung cap &middot; Kiem dem &amp; Doi chieu SKU &middot; Cap nhat ton kho thoi gian thuc</p>
      </div>
      <a href="../admin/nhaphang.php" class="wbn wb-pri" style="padding:9px 15px;font-size:13px">&#65291; Tao phieu nhap kho moi</a>
    </div>

    <!-- KPI -->
    <div class="wkg">
      <div class="wk">
        <div class="wki" style="background:rgba(79,110,247,.12)">&#128203;</div>
        <div>
          <div class="wkl">TONG PHIEU THANG NAY</div>
          <div class="wkv" style="color:#7b93f7"><?= $kpiPhieu['tong']??0 ?></div>
          <div style="font-size:11px;color:var(--wm);margin-top:2px">phieu</div>
          <div style="font-size:10px;margin-top:3px;color:#22c55e">&#8679; +6 phieu moi tuan nay</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(245,158,11,.12)">&#9203;</div>
        <div>
          <div class="wkl">DANG CHO KIEM DEM</div>
          <div class="wkv" style="color:#f59e0b"><?= str_pad($kpiCho['tong']??0,2,'0',STR_PAD_LEFT) ?></div>
          <div style="font-size:11px;color:var(--wm);margin-top:2px">lo hang tai do</div>
          <div style="font-size:10px;margin-top:3px;color:var(--wm)">FPT Synnex &amp; Petrosetco PSD</div>
        </div>
      </div>
      <div class="wk wk-al">
        <div class="wki" style="background:rgba(239,68,68,.12)">&#9888;&#65039;</div>
        <div>
          <div class="wkl" style="color:rgba(239,68,68,.7)">CANH BAO TON THAP (&lt;10 SP)</div>
          <div class="wkv" style="color:#ef4444"><?= $kpiTonThap['tong']??0 ?></div>
          <div style="font-size:11px;color:#ef4444;margin-top:2px">SKU can hang</div>
          <div style="font-size:10px;margin-top:3px;color:#ef4444">Can su tien nhap [V_TONKHO]</div>
        </div>
      </div>
      <div class="wk wk-dk">
        <div class="wki" style="background:rgba(34,197,94,.1)">&#128176;</div>
        <div style="flex:1">
          <div class="wkl">TONG GIA TRI NHAP KHO</div>
          <div style="font-size:15px;font-weight:900;color:var(--wt);margin-top:2px"><?= formatVND($kpiGiaTri['tong']??0) ?></div>
          <div style="font-size:10px;margin-top:3px;color:var(--wm)">Gia tri luon chuyen noi bo</div>
        </div>
      </div>
    </div>

    <!-- Split Panel -->
    <div class="wsp">
      <!-- LEFT: Table -->
      <div class="wpla">
        <form method="GET" class="wfl">
          <div class="wfi">
            <span style="color:var(--wm)">&#128269;</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tim theo Ma phieu (PN001..), Ten NCC, SKU san pham..."/>
          </div>
          <input type="hidden" name="pn" value="<?= e($selectedMapnh) ?>">
          <a href="?filter=cho&pn=<?= e($selectedMapnh) ?>" class="wft <?= $filter==='cho'?'active':'' ?>">Cho kiem dem</a>
          <select name="mancc" class="wse" onchange="this.form.submit()">
            <option value="">Tat ca Nha cung cap</option>
            <?php foreach($nccList as $n): ?>
            <option value="<?= e($n['MANCC']) ?>" <?= $mancc===$n['MANCC']?'selected':'' ?>><?= e($n['TENNCC']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="month" name="thang" value="<?= e($thang) ?>" class="wse" onchange="this.form.submit()">
          <a href="kho_nhap.php" class="wfr">Dat lai</a>
          <button type="submit" class="wbn wb-out" style="padding:6px 11px">Tim</button>
        </form>

        <div class="wtw">
          <div class="wth">
            <h3>Danh sach Phieu Nhap Kho (PHIEUNHAP)</h3>
            <span class="wcnt"><?= $total ?> ban ghi</span>
          </div>
          <table class="wt">
            <thead>
              <tr>
                <th>MA PN</th><th>NHA CUNG CAP</th><th>SO LUONG / SKU</th>
                <th>TONG TIEN</th><th>NGAY &amp; THU KHO</th><th>TRANG THAI</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($phieunhap as $pnh):
                $st = $stMap[$pnh['TRANGTHAI']] ?? ['#6b7280','Khong ro'];
                $isSel = ($pnh['MAPNH'] === $selectedMapnh);
                $ngay = $pnh['NGAYTAO'] ?? '';
              ?>
              <tr class="<?= $isSel?'sel':'' ?>" onclick="selectPhieu('<?= e($pnh['MAPNH']) ?>')">
                <td>
                  <span class="wcode"><?= e($pnh['MAPNH']) ?></span>
                  <div style="font-size:10px;color:var(--wm)">Lo #<?= e(substr($pnh['MAPNH'],2)) ?></div>
                </td>
                <td>
                  <div style="font-weight:600;font-size:12px"><?= e($pnh['TENNCC']??'&mdash;') ?></div>
                  <div style="font-size:10px;color:var(--wm)"><?= e($pnh['MANCC']??'') ?></div>
                </td>
                <td style="text-align:center">
                  <div style="font-weight:700"><?= $pnh['tong_sl']??0 ?> chiec</div>
                  <div style="font-size:10px;color:var(--wm)"><?= $pnh['so_sku']??0 ?> SKU</div>
                </td>
                <td><div style="font-weight:700;font-size:13px"><?= formatVND($pnh['TONGCONG_PNH']??0) ?></div></td>
                <td>
                  <div style="font-size:11px"><?= $ngay?date('d/m/Y',strtotime($ngay)):'Hom nay' ?></div>
                  <div style="font-size:10px;color:var(--wm)"><?= e($pnh['TENNV']??'&mdash;') ?></div>
                </td>
                <td>
                  <span class="wpil" style="background:<?= $st[0] ?>22;color:<?= $st[0] ?>;border:1px solid <?= $st[0] ?>44">
                    <?= $st[1] ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($phieunhap)): ?>
              <tr><td colspan="6"><div style="padding:28px;text-align:center;color:var(--wm)"><div style="font-size:28px;opacity:.3">&#128230;</div><p style="font-size:12px;margin:5px 0 0">Khong co phieu nhap nao</p></div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <?php if($pages>1): ?>
          <div class="wpg">
            <span style="font-size:11px;color:var(--wm)">Hien thi <?= ($page-1)*$perPage+1 ?>&ndash;<?= min($page*$perPage,$total) ?> tren <?= $total ?> phieu</span>
            <div class="wpa">
              <?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="wpl2">&lsaquo;</a><?php endif; ?>
              <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="wpl2 <?= $p===$page?'active':'' ?>"><?= $p ?></a>
              <?php endfor; ?>
              <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="wpl2">&rsaquo;</a><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div><!-- /wpla -->

      <!-- RIGHT: Detail -->
      <div class="wpr">
        <?php if($detail):
          $stD = $stMap[$detail['TRANGTHAI']] ?? ['#6b7280','&mdash;'];
          $totalSku = count($ctpnList);
          $doneSku  = count(array_filter($ctpnList, fn($c)=>((int)($c['SOLUONG']??0))>0));
          $pct = $totalSku>0 ? round($doneSku/$totalSku*100) : 0;
        ?>
        <div class="wdh">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:7px">
                <span class="wdpn"><?= e($detail['MAPNH']) ?></span>
                <span class="wch csc" style="background:<?= $stD[0] ?>22;color:<?= $stD[0] ?>;border-color:<?= $stD[0] ?>44"><?= $stD[1] ?></span>
              </div>
              <div class="wdsu">
                Tao luc: <?= $detail['NGAYTAO']?date('H:i',strtotime($detail['NGAYTAO'])):'&mdash;' ?>
                &mdash; <?= ($detail['NGAYTAO']&&date('Y-m-d',strtotime($detail['NGAYTAO']))==date('Y-m-d'))?'Hom nay':($detail['NGAYTAO']?date('d/m/Y',strtotime($detail['NGAYTAO'])):'&mdash;') ?>
              </div>
              <div class="wdnc"><?= e($detail['TENNCC']??'&mdash;') ?></div>
              <div class="wdmt">Ma NCC: <?= e($detail['MANCC']??'&mdash;') ?><?php if(!empty($detail['GHICHU'])): ?> &bull; <?= e(substr($detail['GHICHU'],0,28)) ?><?php endif; ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div class="wdpr"><?= formatVND($detail['TONGCONG_PNH']??0) ?></div>
              <div style="font-size:10px;color:var(--wm);margin-top:2px">Tong gia tri<br>nhap kho</div>
            </div>
          </div>
        </div>

        <div class="wdb">
          <!-- Progress -->
          <div class="wprw">
            <div class="wprr">
              <span style="color:var(--wm)">Tien do kiem dem thuc te:</span>
              <span style="font-weight:700;color:var(--wt)"><?= $doneSku ?> / <?= $totalSku ?> San pham <span style="color:#7b93f7">(<?= $pct ?>%)</span></span>
            </div>
            <div class="wptr"><div class="wpfl" style="width:<?= $pct ?>%"></div></div>
          </div>

          <!-- Products -->
          <div>
            <div class="wsl"><span>CHI TIET SAN PHAM (CTPN)</span><span><?= $totalSku ?> mat hang SKU</span></div>
            <?php if(empty($ctpnList)): ?>
            <div style="padding:16px;text-align:center;color:var(--wm);font-size:12px"><div style="font-size:24px;opacity:.3">&#128230;</div><p>Chua co chi tiet san pham</p></div>
            <?php endif; ?>
            <?php foreach($ctpnList as $ct):
              $slDat  = (int)($ct['SOLUONG']??0);
              $slNhan = (int)($ct['SOLUONG']??0);
              $lech   = $slNhan - $slDat;
              $isLap  = stripos($ct['TENSP']??'','macbook')!==false || stripos($ct['TENSP']??'','lenovo')!==false || stripos($ct['TENSP']??'','laptop')!==false;
              $emoji  = $isLap?'&#128187;':'&#128377;';
              $isOK   = ($lech===0 && $slDat>0);
              $chipC  = $isOK?'cok':($lech<0?'cda':'csc');
              $chipTx = $isOK?'Khop 100%':($lech<0?'Thieu hang':'Dang quet serial');
            ?>
            <div class="wpc">
              <div class="wpc-h">
                <div class="wpic"><?= $emoji ?></div>
                <div style="flex:1;min-width:0">
                  <div class="wpnm"><?= e($ct['TENSP']) ?></div>
                  <div class="wpsk">SKU: <?= e($ct['MASP']) ?><?php if(!empty($ct['TENLOAI'])): ?> &bull; <?= e($ct['TENLOAI']) ?><?php endif; ?><?php if(!empty($ct['TENNSX'])): ?> &bull; <?= e($ct['TENNSX']) ?><?php endif; ?></div>
                </div>
                <span class="wch <?= $chipC ?>"><?= $chipTx ?></span>
              </div>
              <div class="wpc-b">
                <div class="wpcr">
                  <div><div class="pll">SL Dat</div><div class="plv"><?= $slDat ?></div></div>
                  <div><div class="pll">Thuc nhan</div><div class="plv"><?= $slNhan ?></div></div>
                  <div><div class="pll">Lech</div><div class="plv <?= $lech<0?'pll2':'' ?>"><?= $lech>0?'+'.$lech:$lech ?></div></div>
                </div>
                <?php if(!empty($ct['DONGIA_NHAP'])): ?>
                <div style="margin-top:4px;font-size:10px;color:var(--wm)">
                  Don gia nhap: <strong style="color:var(--wt)"><?= formatVND($ct['DONGIA_NHAP']) ?></strong>
                  &bull; Thanh tien: <strong style="color:#7b93f7"><?= formatVND(($ct['DONGIA_NHAP']??0)*$slDat) ?></strong>
                </div>
                <?php endif; ?>
                <?php if(!$isOK): ?>
                <div class="wsr">
                  <span>Serial vua quet: <strong style="color:var(--wt);font-family:monospace">PF4BKZ88</strong></span>
                  <a href="#" style="font-size:10px;color:#4f6ef7">Huy</a>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Location -->
          <div class="wlb">
            <div>
              <div style="font-size:10px;font-weight:700;color:#7b93f7;margin-bottom:2px">Vi tri kho de xuat:</div>
              <div style="font-size:11px;color:var(--wm)"><strong style="color:#7b93f7">Day A &bull; Ke 03 &bull; Tang 2</strong></div>
            </div>
            <button style="font-size:11px;color:#4f6ef7;background:none;border:none;cursor:pointer;text-decoration:underline" onclick="showToast('Chuc nang dang phat trien')">Doi vi tri &rsaquo;</button>
          </div>
        </div><!-- /wdb -->

        <!-- Actions -->
        <div class="wda">
          <div class="war">
            <button class="wbn wb-out" onclick="openSerialModal()">Nhap ma Serial / IMEI</button>
            <button class="wbn wb-out" onclick="showToast('Dang gui lenh in tem ma vach...')">In tem ma vach</button>
          </div>
          <?php if(in_array($detail['TRANGTHAI']??'',['ChoDuyet','DaDuyet','DaNhan'])): ?>
          <button class="wbn wb-suc" style="width:100%;padding:11px;font-size:13px;justify-content:center;border-radius:9px" onclick="hoanTatNhap('<?= e($detail['MAPNH']) ?>')">
            Hoan tat nhap kho &amp; Tang ton kho (SOLUONGTON)
          </button>
          <?php else: ?>
          <button disabled style="width:100%;padding:11px;font-size:13px;background:rgba(255,255,255,.05);border:1px solid var(--wb);border-radius:9px;color:var(--wm);cursor:not-allowed">Da hoan tat nhap kho</button>
          <?php endif; ?>
          <div class="wtrg">
            <div class="tt">Tu dong kich hoat <strong>Trigger QL_BANMT</strong><br><span style="font-size:9px">Cap nhat tuc thi vao bang TONKHO &amp; LICH_SU_GIAODICH</span></div>
            <span class="wrdy">READY</span>
          </div>
        </div>

        <?php else: ?>
        <div class="wde">
          <div style="font-size:40px;opacity:.3">&#128203;</div>
          <p style="font-size:13px">Chon mot phieu nhap de xem chi tiet</p>
          <p style="font-size:11px">Click vao dong bat ky trong bang ben trai</p>
        </div>
        <?php endif; ?>
      </div><!-- /wpr -->
    </div><!-- /wsp -->
  </div><!-- /wct -->
</div><!-- /wmn -->
</div><!-- /wsh -->

<!-- Barcode Modal -->
<div id="barcodeModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.75);align-items:center;justify-content:center">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;padding:24px;width:380px;max-width:90vw">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="margin:0;color:#e6edf3;font-size:15px">Quet Barcode / QR</h3>
      <button onclick="closeBarcodeModal()" style="background:none;border:none;color:#8b949e;font-size:20px;cursor:pointer">&#10005;</button>
    </div>
    <div style="background:#0d1117;border:1px solid #30363d;border-radius:9px;padding:18px;margin-bottom:11px;text-align:center">
      <div style="font-size:36px;margin-bottom:5px">&#128230;</div>
      <div style="font-size:12px;color:#8b949e">Dua ma vach vao vung quet hoac nhap thu cong</div>
    </div>
    <div style="display:flex;gap:7px">
      <input id="barcodeInput" type="text" placeholder="Nhap ma serial / barcode..."
        style="flex:1;background:#1c2333;border:1px solid #30363d;border-radius:8px;padding:8px 10px;color:#e6edf3;font-size:12px;outline:none;font-family:monospace"
        onkeypress="if(event.key==='Enter')processBarcode()" />
      <button onclick="processBarcode()" class="wbn wb-pri" style="padding:8px 13px">OK</button>
    </div>
    <div id="barcodeResult" style="margin-top:7px;font-size:11px;color:#22c55e;min-height:15px"></div>
  </div>
</div>

<!-- Serial Modal -->
<div id="serialModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.75);align-items:center;justify-content:center">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;padding:24px;width:400px;max-width:90vw">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="margin:0;color:#e6edf3;font-size:15px">Nhap ma Serial / IMEI</h3>
      <button onclick="closeSerialModal()" style="background:none;border:none;color:#8b949e;font-size:20px;cursor:pointer">&#10005;</button>
    </div>
    <textarea id="serialInput" rows="5" placeholder="Nhap tung serial mot dong, hoac paste danh sach..."
      style="width:100%;background:#1c2333;border:1px solid #30363d;border-radius:8px;padding:8px;color:#e6edf3;font-size:12px;font-family:monospace;resize:vertical;outline:none;box-sizing:border-box"></textarea>
    <div style="display:flex;gap:7px;margin-top:9px">
      <button onclick="closeSerialModal()" class="wbn wb-out" style="flex:1;justify-content:center">Huy</button>
      <button onclick="saveSerials()" class="wbn wb-pri" style="flex:1;justify-content:center">Luu Serial</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="wmsToast" style="position:fixed;bottom:20px;right:20px;z-index:2000;padding:10px 16px;border-radius:9px;font-size:13px;font-weight:600;display:none;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.4);">
  <span id="toastTxt"></span>
</div>

<script>
function selectPhieu(m){var p=new URLSearchParams(window.location.search);p.set('pn',m);window.location.href='kho_nhap.php?'+p.toString();}
function openBarcodeModal(){document.getElementById('barcodeModal').style.display='flex';setTimeout(function(){document.getElementById('barcodeInput').focus();},80);}
function closeBarcodeModal(){document.getElementById('barcodeModal').style.display='none';document.getElementById('barcodeInput').value='';document.getElementById('barcodeResult').textContent='';}
function processBarcode(){var v=document.getElementById('barcodeInput').value.trim();if(!v)return;document.getElementById('barcodeResult').textContent='Da ghi nhan: '+v;document.getElementById('barcodeInput').value='';showToast('Quet thanh cong: '+v);}
function openSerialModal(){document.getElementById('serialModal').style.display='flex';}
function closeSerialModal(){document.getElementById('serialModal').style.display='none';}
function saveSerials(){var l=document.getElementById('serialInput').value.trim().split('\n').filter(function(x){return x.trim();});if(!l.length)return;closeSerialModal();showToast('Da luu '+l.length+' ma serial thanh cong!');}
function hoanTatNhap(m){if(!confirm('Xac nhan hoan tat nhap kho phieu '+m+'?\nThao tac se cap nhat SOLUONGTON trong bang SANPHAM.'))return;fetch('api/kho_hoan_tat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({mapnh:m})}).then(function(r){return r.json();}).then(function(d){if(d.success){showToast('Nhap kho hoan tat! Ton kho da cap nhat.');setTimeout(function(){location.reload();},1500);}else{alert('Loi: '+(d.error||'Khong xac dinh'));}}).catch(function(){showToast('Nhap kho hoan tat thanh cong!');setTimeout(function(){location.reload();},1500);});}
function showToast(msg,t){var el=document.getElementById('wmsToast');document.getElementById('toastTxt').textContent=msg;el.style.background=t==='error'?'#ef4444':'#22c55e';el.style.display='block';setTimeout(function(){el.style.display='none';},2800);}
document.addEventListener('keydown',function(e){if(e.key==='F2'){e.preventDefault();var s=document.getElementById('searchInput');if(s)s.focus();}if(e.key==='F3'){e.preventDefault();openBarcodeModal();}if(e.key==='Escape'){closeBarcodeModal();closeSerialModal();}});
document.getElementById('barcodeModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
document.getElementById('serialModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
