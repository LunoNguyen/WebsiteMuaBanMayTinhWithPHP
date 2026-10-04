<?php
// ================================================================
// ADMIN DASHBOARD - Tổng quan hệ thống
// admin/index.php
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['Admin']);

$pageTitle = 'Tổng quan';
$breadcrumb = ['Tổng quan'];

// ================================================================
// QUERIES LẤY DỮ LIỆU DASHBOARD
// ================================================================

// 1. KPI - Tổng doanh thu (đơn hoàn thành)
$kpiDT = dbFetchOne("
    SELECT 
        SUM(TONGTIEN_HD) AS tong_dt,
        COUNT(*) AS so_don
    FROM HOADON 
    WHERE TRANGTHAI IN ('DaGiao','HoanThanh')
");

// 2. KPI - Sản phẩm đang bán
$kpiSP = dbFetchOne("SELECT COUNT(*) AS cnt FROM SANPHAM WHERE TRANGTHAI='DangBan'");

// 3. KPI - Khách hàng
$kpiKH = dbFetchOne("SELECT COUNT(*) AS cnt FROM KHACHHANG");

// 4. KPI - Nhân viên đang làm
$kpiNV = dbFetchOne("SELECT COUNT(*) AS cnt FROM NHANVIEN WHERE TRANGTHAI=1");

// 5. KPI - Tổng tài khoản hoạt động
$kpiTK = dbFetchOne("SELECT COUNT(*) AS cnt FROM TAIKHOAN WHERE TRANGTHAI='HoatDong'");

// 6. Doanh thu 6 tháng gần nhất
$dtThang = dbFetch("
    SELECT 
        DATE_FORMAT(NGAYLAP,'%m/%Y') AS thang_label,
        YEAR(NGAYLAP) AS nam,
        MONTH(NGAYLAP) AS thang,
        SUM(TONGTIEN_HD) AS doanhthu,
        COUNT(*) AS so_don
    FROM HOADON
    WHERE TRANGTHAI IN ('DaGiao','HoanThanh')
      AND NGAYLAP >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(NGAYLAP), MONTH(NGAYLAP)
    ORDER BY YEAR(NGAYLAP), MONTH(NGAYLAP)
");

// 7. Cơ cấu doanh số theo loại SP
$coSua = dbFetch("
    SELECT 
        lsp.TENLOAI,
        SUM(cthd.THANHTIEN) AS tong_tien,
        COUNT(*) AS so_san_pham_ban
    FROM CHITIETHOADON cthd
    JOIN SANPHAM sp ON cthd.MASP = sp.MASP
    JOIN LOAISANPHAM lsp ON sp.MALOAI = lsp.MALOAI
    JOIN HOADON hd ON cthd.MAHD = hd.MAHD
    WHERE hd.TRANGTHAI IN ('DaGiao','HoanThanh')
    GROUP BY lsp.MALOAI, lsp.TENLOAI
    ORDER BY tong_tien DESC
");

// 8. Sản phẩm tồn kho thấp (cảnh báo)
$tonKhoThap = dbFetch("
    SELECT sp.MASP, sp.TENSP, sp.SOLUONGTON, sp.DONGIA_SP,
           lsp.TENLOAI, nsx.TENNSX
    FROM SANPHAM sp
    LEFT JOIN LOAISANPHAM lsp ON sp.MALOAI = lsp.MALOAI
    LEFT JOIN NHASANXUA nsx ON sp.MANSX = nsx.MANSX
    WHERE sp.SOLUONGTON <= 20 AND sp.TRANGTHAI = 'DangBan'
    ORDER BY sp.SOLUONGTON ASC
    LIMIT 5
");

// 9. Khuyến mãi đang chạy
$kmHoatDong = dbFetch("
    SELECT MAKM, TENKM, MA_CODE, LOAI_KM, GIATRI_KM, SOTIENTOIDA_KM, NGAYKT, TRANGTHAI, DA_SUDUNG, SOLUONG_MA
    FROM KHUYENMAI
    WHERE TRANGTHAI IN ('HoatDong','TamDung')
    ORDER BY TRANGTHAI='HoatDong' DESC, NGAYKT DESC
    LIMIT 4
");

// 10. Hoạt động gần đây (5 hóa đơn + 5 cập nhật giá)
$activityHD = dbFetch("
    SELECT 
        hd.MAHD, hd.NGAYLAP, hd.TRANGTHAI, hd.TONGTIEN_HD,
        kh.TENKH,
        nv.TENNV,
        'hoadon' AS loai
    FROM HOADON hd
    LEFT JOIN KHACHHANG kh ON hd.MAKH = kh.MAKH
    LEFT JOIN NHANVIEN nv  ON hd.MANV = nv.MANV
    ORDER BY hd.NGAYLAP DESC
    LIMIT 6
");

$activityGia = dbFetch("
    SELECT 
        lsg.MASP, lsg.DONGIA_MOI, lsg.DONGIA_CU, lsg.NGAY_CAPNHAT, lsg.GHI_CHU,
        sp.TENSP,
        nv.TENNV,
        'gia' AS loai
    FROM LICHSUGIA lsg
    JOIN SANPHAM sp ON lsg.MASP = sp.MASP
    LEFT JOIN NHANVIEN nv ON lsg.MANV_CAPNHAT = nv.MANV
    ORDER BY lsg.NGAY_CAPNHAT DESC
    LIMIT 4
");

// 11. Tổng hàng nhập trong tháng
$nhapThang = dbFetchOne("
    SELECT SUM(TONGCONG_PNH) AS tong, COUNT(*) AS so_phieu
    FROM PHIEUNHAPHANG
    WHERE MONTH(NGAYTAO)=MONTH(CURDATE()) AND YEAR(NGAYTAO)=YEAR(CURDATE())
");

// 12. Đơn hàng chờ xử lý
$donCho = dbFetchOne("SELECT COUNT(*) AS cnt FROM HOADON WHERE TRANGTHAI IN ('ChoXacNhan','DaXacNhan')");
$donGiao = dbFetchOne("SELECT COUNT(*) AS cnt FROM HOADON WHERE TRANGTHAI = 'DangGiao'");

// Chuẩn bị data cho chart
$chartLabels = array_column($dtThang, 'thang_label');
$chartDT = array_column($dtThang, 'doanhthu');
$chartDon = array_column($dtThang, 'so_don');

// Tính tổng doanh thu cho %
$tongDoanhThu = array_sum(array_column($coSua, 'tong_tien')) ?: 1;

// Màu cho từng loại trong donut chart
$donutColors = ['#4f6ef7','#22c55e','#8b5cf6','#f59e0b','#06b6d4','#ec4899','#ef4444'];

include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="page-content">
      <!-- ======================================================
           PAGE HEADER
           ====================================================== -->
      <div class="page-header">
        <div class="page-header-left">
          <h1>Tổng quan Hệ thống &amp; Báo cáo Hoạt động</h1>
          <p>Giám sát tức thời chỉ số kinh doanh, kho vận và sản phẩm từ nguồn dữ liệu thời gian thực</p>
        </div>
        <div class="page-header-right">
          <div class="date-chip">
            <span>📅</span>
            Tháng <?= date('m/Y') ?>
          </div>
          <a href="baocao.php" class="btn btn-outline">
            <span>📊</span> Báo cáo đầy đủ
          </a>
          <button class="btn btn-primary" onclick="exportTableCSV('activityTable','hoatdong_<?= date('Ymd') ?>')">
            <span>📥</span> Xuất báo cáo PDF
          </button>
        </div>
      </div>

      <!-- ======================================================
           KPI STATS CARDS
           ====================================================== -->
      <div class="stats-grid">
        <!-- Doanh thu -->
        <div class="stat-card" style="--accent-color:#4f6ef7">
          <div class="stat-card-top">
            <span class="stat-card-label">Tổng doanh thu (HĐ)</span>
            <div class="stat-card-icon" style="color:#4f6ef7">💰</div>
          </div>
          <div class="stat-card-value" style="font-size:20px;color:#4f6ef7">
            <?= formatVND($kpiDT['tong_dt'] ?? 0) ?>
          </div>
          <div class="stat-card-sub">
            <span class="stat-card-trend trend-up">▲ +8.2%</span>
            so với T<?= date('m', strtotime('-1 month')) ?>/<?= date('Y') ?>
          </div>
        </div>

        <!-- Sản phẩm đang bán -->
        <div class="stat-card" style="--accent-color:#22c55e">
          <div class="stat-card-top">
            <span class="stat-card-label">Sản phẩm đang bán</span>
            <div class="stat-card-icon" style="color:#22c55e">💻</div>
          </div>
          <div class="stat-card-value" style="color:#22c55e">
            <span data-count="<?= $kpiSP['cnt'] ?? 0 ?>" data-suffix=" SP">0 SP</span>
          </div>
          <div class="stat-card-sub">
            10 danh mục sản phẩm &bull;
            <strong style="color:#4f6ef7"><?= $donCho['cnt'] ?? 0 ?></strong> đơn chờ duyệt
          </div>
        </div>

        <!-- Khách hàng -->
        <div class="stat-card" style="--accent-color:#8b5cf6">
          <div class="stat-card-top">
            <span class="stat-card-label">Khách hàng</span>
            <div class="stat-card-icon" style="color:#8b5cf6">👤</div>
          </div>
          <div class="stat-card-value" style="color:#8b5cf6">
            <span data-count="<?= $kpiKH['cnt'] ?? 0 ?>">0</span>
          </div>
          <div class="stat-card-sub">
            <span class="stat-card-trend trend-up">▲ +3 KH</span>
            tháng này &bull;
            <strong style="color:#22c55e"><?= $kpiTK['cnt'] ?? 0 ?></strong> tài khoản
          </div>
        </div>

        <!-- Nhân viên -->
        <div class="stat-card" style="--accent-color:#f59e0b">
          <div class="stat-card-top">
            <span class="stat-card-label">Nhân viên hoạt động</span>
            <div class="stat-card-icon" style="color:#f59e0b">👨‍💼</div>
          </div>
          <div class="stat-card-value" style="color:#f59e0b">
            <span data-count="<?= $kpiNV['cnt'] ?? 0 ?>">0</span>
          </div>
          <div class="stat-card-sub">
            <span data-count="<?= $donGiao['cnt'] ?? 0 ?>">0</span> đơn đang giao &bull;
            <strong style="color:#22c55e">100%</strong> hoạt động
          </div>
        </div>
      </div>

      <!-- ======================================================
           ROW 2: CHART + DONUT
           ====================================================== -->
      <div class="grid-7-5" style="margin-bottom:20px">
        <!-- Doanh thu 6 tháng -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>📈 Doanh thu &amp; Tăng trưởng 6 Tháng</h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:3px">Giai đoạn từ tháng <?= count($dtThang) ? $chartLabels[0] : '—' ?> đến <?= count($dtThang) ? end($chartLabels) : '—' ?></p>
            </div>
            <div style="display:flex;gap:12px;font-size:12px;color:var(--text-muted)">
              <span style="color:#4f6ef7">■</span> Doanh thu
              <span style="color:#22c55e">■</span> Số đơn/người
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="chartDoanhThu"></canvas>
            </div>
          </div>
        </div>

        <!-- Cơ cấu doanh số - Donut -->
        <div class="card">
          <div class="card-header">
            <h3>🍩 Cơ cấu Doanh số</h3>
            <a href="baocao.php" style="font-size:12px;color:var(--blue-light)">Xem chi tiết ›</a>
          </div>
          <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:16px">
              <div style="display:flex;justify-content:center">
                <canvas id="chartDonut" style="max-height:160px;max-width:160px"></canvas>
              </div>
              <div class="donut-legend">
                <?php foreach($coSua as $i => $cs): ?>
                <div class="legend-item">
                  <div class="legend-dot" style="background:<?= $donutColors[$i % count($donutColors)] ?>"></div>
                  <span><?= e($cs['TENLOAI']) ?></span>
                  <span class="legend-value"><?= number_format($cs['tong_tien'] / $tongDoanhThu * 100, 0) ?>%</span>
                  <span style="font-size:11px;color:var(--text-muted);margin-left:4px">(<?= formatVND($cs['tong_tien']) ?>)</span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($coSua)): ?>
                <p style="text-align:center;color:var(--text-muted);font-size:13px">Chưa có dữ liệu</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================
           ROW 3: TỒN KHO + KHUYẾN MÃI
           ====================================================== -->
      <div class="grid-2" style="margin-bottom:20px">
        <!-- Cảnh báo tồn kho -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>⚠️ Cảnh báo Tồn kho &amp; Đề xuất Nhập</h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Thấp hơn mức tối thiểu 20 cái</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <span style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                🚨 <?= count($tonKhoThap) ?> mặt hàng
              </span>
              <a href="sanpham.php?filter=low_stock" class="btn btn-sm btn-outline">Xem tất cả kho</a>
            </div>
          </div>
          <div class="card-body" style="padding:16px">
            <?php foreach($tonKhoThap as $sp): 
              $stockClass = $sp['SOLUONGTON'] <= 5 ? 'stock-danger' : ($sp['SOLUONGTON'] <= 10 ? 'stock-warning' : 'stock-ok');
            ?>
            <div class="product-item">
              <div class="product-thumb">
                <?= (strpos($sp['TENLOAI'],'Laptop') !== false) ? '💻' : ((strpos($sp['TENLOAI'],'PC') !== false) ? '🖥️' : '🖱️') ?>
              </div>
              <div class="product-info">
                <div class="product-name"><?= e($sp['TENSP']) ?></div>
                <div class="product-meta">
                  <?= e($sp['TENLOAI']) ?> &bull; <?= e($sp['TENNSX']) ?> &bull; 
                  <strong style="color:var(--blue-light)"><?= formatVND($sp['DONGIA_SP']) ?></strong>
                </div>
              </div>
              <span class="stock-badge <?= $stockClass ?>">
                Còn <?= $sp['SOLUONGTON'] ?> cái
              </span>
              <a href="nhaphang.php?masp=<?= e($sp['MASP']) ?>" class="btn btn-sm btn-primary" style="margin-left:8px">
                Nhập
              </a>
            </div>
            <?php endforeach; ?>
            <?php if(empty($tonKhoThap)): ?>
            <div class="empty-state" style="padding:24px">
              <div class="empty-icon">✅</div>
              <p>Tất cả sản phẩm còn đủ hàng</p>
            </div>
            <?php endif; ?>

            <?php if(!empty($tonKhoThap)): ?>
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:13px;color:var(--text-secondary)">
                🔔 Tổng giá trị nhập dự kiến:
                <strong style="color:var(--text-primary)">
                  <?= formatVND(array_sum(array_map(fn($s) => $s['DONGIA_SP'] * max(1, 20 - $s['SOLUONGTON']), $tonKhoThap))) ?>
                </strong>
              </span>
              <a href="nhaphang.php" class="btn btn-sm btn-primary">Đặt mua ngay →</a>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Khuyến mãi đang chạy -->
        <div class="card">
          <div class="card-header">
            <h3>🎁 Khuyến Mãi Đang Chạy</h3>
            <a href="khuyenmai.php" style="font-size:12px;color:var(--blue-light)">Thêm mới ＋</a>
          </div>
          <div class="card-body" style="padding:16px">
            <?php foreach($kmHoatDong as $km): 
              $kmIsActive = $km['TRANGTHAI'] === 'HoatDong';
              $pct = $km['SOLUONG_MA'] ? round($km['DA_SUDUNG'] / $km['SOLUONG_MA'] * 100) : 0;
            ?>
            <div class="km-card">
              <div class="km-card-top">
                <div class="km-icon" style="background:<?= $kmIsActive ? 'rgba(34,197,94,0.15)' : 'rgba(245,158,11,0.15)' ?>">
                  <?= $km['LOAI_KM'] === 'PhanTram' ? '🏷️' : '💵' ?>
                </div>
                <div class="km-info">
                  <div class="km-name"><?= e($km['TENKM']) ?></div>
                  <div class="km-code">
                    <code style="background:var(--bg-main);padding:1px 6px;border-radius:4px;font-size:11px"><?= e($km['MA_CODE']) ?></code>
                    &nbsp;
                    <?php if($kmIsActive): ?>
                      <span style="color:#22c55e;font-size:11px">● Hoạt động</span>
                    <?php else: ?>
                      <span style="color:#f59e0b;font-size:11px">● Tạm dừng</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="km-value" style="color:<?= $kmIsActive ? '#22c55e' : '#f59e0b' ?>">
                  <?php if($km['LOAI_KM'] === 'PhanTram'): ?>
                    Giảm <?= $km['GIATRI_KM'] ?>%
                    <?php if($km['SOTIENTOIDA_KM']): ?>
                      <div style="font-size:11px;font-weight:400;color:var(--text-muted)">(Tối đa <?= formatVND($km['SOTIENTOIDA_KM']) ?>)</div>
                    <?php endif; ?>
                  <?php else: ?>
                    Giảm <?= formatVND($km['GIATRI_KM']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="km-card-bottom">
                <span>
                  HSD: <strong><?= $km['NGAYKT'] ? date('d/m/Y', strtotime($km['NGAYKT'])) : '∞' ?></strong>
                </span>
                <?php if($km['SOLUONG_MA']): ?>
                <span>
                  Đã dùng: <?= $km['DA_SUDUNG'] ?>/<?= $km['SOLUONG_MA'] ?>
                  <span style="display:inline-block;width:40px;height:4px;background:var(--border);border-radius:2px;margin-left:4px;vertical-align:middle">
                    <span style="display:block;width:<?= $pct ?>%;height:100%;background:#4f6ef7;border-radius:2px"></span>
                  </span>
                </span>
                <?php else: ?>
                <span style="color:var(--green)">Không giới hạn lượt dùng</span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($kmHoatDong)): ?>
            <div class="empty-state" style="padding:24px">
              <div class="empty-icon">🎁</div>
              <p>Chưa có khuyến mãi nào đang chạy</p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ======================================================
           ROW 4: NHẬT KÝ HOẠT ĐỘNG & KIỂM SOÁT
           ====================================================== -->
      <div class="card">
        <div class="card-header">
          <div>
            <h3>📋 Nhật ký Hoạt động &amp; Đối Soát Kiểm Toán</h3>
            <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Các hoạt động bán hàng và cập nhật hệ thống gần đây nhất</p>
          </div>
          <div style="display:flex;gap:8px">
            <a href="donhang.php" class="btn btn-sm btn-outline">Tất cả đơn hàng</a>
            <button class="btn btn-sm btn-outline" onclick="exportTableCSV('activityTable','nhatky')">
              📥 Xuất CSV
            </button>
            <div style="display:flex;gap:4px">
              <button class="btn btn-sm btn-outline" onclick="filterActivity('all')" id="fAll" style="border-color:var(--blue);color:var(--blue-light)">Tất cả</button>
              <button class="btn btn-sm btn-outline" onclick="filterActivity('hoadon')" id="fHD">Hóa đơn</button>
              <button class="btn btn-sm btn-outline" onclick="filterActivity('gia')" id="fGia">Giá</button>
            </div>
          </div>
        </div>
        <div class="table-wrapper">
          <table id="activityTable">
            <thead>
              <tr>
                <th>Mã</th>
                <th>Nội dung hoạt động</th>
                <th>Người thực hiện</th>
                <th>Thời gian</th>
                <th>Giá trị</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($activityHD as $act): ?>
              <tr class="activity-row" data-type="hoadon">
                <td>
                  <a href="donhang.php?mahd=<?= e($act['MAHD']) ?>" style="font-family:monospace;font-size:13px;color:var(--blue-light);font-weight:700">
                    <?= e($act['MAHD']) ?>
                  </a>
                </td>
                <td>
                  <div style="font-size:13px;color:var(--text-primary)">
                    🛒 <strong><?= e($act['TENKH'] ?? 'Khách vãng lai') ?></strong> — Đặt hàng online
                  </div>
                  <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                    NV xử lý: <?= e($act['TENNV'] ?? '—') ?>
                  </div>
                </td>
                <td style="font-size:13px"><?= e($act['TENNV'] ?? 'Hệ thống') ?></td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)"><?= date('d/m/Y', strtotime($act['NGAYLAP'])) ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= date('H:i', strtotime($act['NGAYLAP'])) ?></div>
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--blue-light)"><?= formatVND($act['TONGTIEN_HD']) ?></td>
                <td><?= statusBadge($act['TRANGTHAI'], 'hoadon') ?></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="donhang.php?mahd=<?= e($act['MAHD']) ?>" class="btn-icon" title="Xem chi tiết">👁️</a>
                    <?php if($act['TRANGTHAI'] === 'ChoXacNhan'): ?>
                    <a href="donhang.php?action=xacnhan&mahd=<?= e($act['MAHD']) ?>" class="btn-icon" title="Xác nhận" style="border-color:var(--green)">✅</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>

              <?php foreach($activityGia as $act): ?>
              <tr class="activity-row" data-type="gia">
                <td>
                  <span style="font-family:monospace;font-size:12px;color:var(--purple)"><?= e($act['MASP']) ?></span>
                </td>
                <td>
                  <div style="font-size:13px;color:var(--text-primary)">
                    🏷️ Cập nhật giá sản phẩm: <strong><?= e(mb_substr($act['TENSP'], 0, 35)) ?>...</strong>
                  </div>
                  <div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= e($act['GHI_CHU']) ?></div>
                </td>
                <td style="font-size:13px"><?= e($act['TENNV'] ?? 'Hệ thống') ?></td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)"><?= date('d/m/Y', strtotime($act['NGAY_CAPNHAT'])) ?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?= date('H:i', strtotime($act['NGAY_CAPNHAT'])) ?></div>
                </td>
                <td>
                  <div style="font-size:12px;color:var(--text-muted);text-decoration:line-through"><?= formatVND($act['DONGIA_CU']) ?></div>
                  <div style="font-size:13px;font-weight:700;color:var(--green)"><?= formatVND($act['DONGIA_MOI']) ?></div>
                </td>
                <td>
                  <?php $diff = $act['DONGIA_MOI'] - $act['DONGIA_CU']; ?>
                  <span style="color:<?= $diff < 0 ? 'var(--green)' : ($diff > 0 ? 'var(--red)' : 'var(--text-muted)') ?>;font-size:12px;font-weight:600">
                    <?= $diff != 0 ? ($diff > 0 ? '▲' : '▼') . ' ' . formatVND(abs($diff)) : 'Không đổi' ?>
                  </span>
                </td>
                <td>
                  <a href="sanpham.php?masp=<?= e($act['MASP']) ?>" class="btn-icon" title="Xem sản phẩm">👁️</a>
                </td>
              </tr>
              <?php endforeach; ?>

              <?php if(empty($activityHD) && empty($activityGia)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                  Chưa có dữ liệu hoạt động
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid var(--border);text-align:right">
          <a href="donhang.php" style="font-size:13px;color:var(--blue-light)">Xem tất cả hoạt động →</a>
        </div>
      </div>

    </main>
  </div><!-- /main-wrapper -->
</div><!-- /flex wrapper -->

<?php
// Chuẩn bị data dưới dạng JSON để nhúng vào JS (không dùng heredoc)
$jsLabels    = json_encode($chartLabels);
$jsDT        = json_encode(array_map('floatval', $chartDT));
$jsDon       = json_encode(array_map('intval', $chartDon));

// Donut data
$jsDonutLabels = json_encode(array_column($coSua, 'TENLOAI'));
$donutPcts   = array();
foreach($coSua as $c) {
    $donutPcts[] = round($c['tong_tien'] / $tongDoanhThu * 100, 1);
}
$jsDonutData   = json_encode($donutPcts);
$jsDonutColors = json_encode(array_slice($donutColors, 0, count($coSua)));
$jsAdminUrl    = json_encode(ADMIN_URL);
?>
<script>
// ========================================================
// CHART 1: Doanh thu 6 tháng (Bar + Line combo)
// ========================================================
(function() {
  const labels  = <?= $jsLabels ?>;
  const dtData  = <?= $jsDT ?>;
  const donData = <?= $jsDon ?>;

  const ctx = document.getElementById('chartDoanhThu');
  if (!ctx || !labels.length) {
    if(ctx) ctx.parentElement.innerHTML = '<div style="text-align:center;padding:60px;color:var(--text-muted)">Chưa có dữ liệu doanh thu</div>';
    return;
  }


  new Chart(ctx, {
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Doanh thu (₫)',
          data: dtData,
          backgroundColor: dtData.map((v, i) => i === dtData.length - 1
            ? 'rgba(79,110,247,0.9)'
            : 'rgba(79,110,247,0.35)'),
          borderRadius: 6,
          borderSkipped: false,
          yAxisID: 'y',
        },
        {
          type: 'line',
          label: 'Số đơn',
          data: donData,
          borderColor: '#22c55e',
          backgroundColor: 'rgba(34,197,94,0.1)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#22c55e',
          pointRadius: 4,
          pointHoverRadius: 7,
          yAxisID: 'y1',
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(26,29,46,0.95)',
          borderColor: '#2a2f4a',
          borderWidth: 1,
          titleColor: '#e8eaf6',
          bodyColor: '#8892b0',
          callbacks: {
            label: ctx => ctx.dataset.label === 'Doanh thu (₫)'
              ? ' ' + new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(ctx.raw)
              : ' ' + ctx.raw + ' đơn'
          }
        }
      },
      scales: {
        x: { grid: { color: 'rgba(42,47,74,0.5)' }, ticks: { color: '#8892b0' } },
        y: {
          position: 'left',
          grid: { color: 'rgba(42,47,74,0.5)' },
          ticks: {
            color: '#8892b0',
            callback: v => new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v) + '₫'
          }
        },
        y1: {
          position: 'right',
          grid: { drawOnChartArea: false },
          ticks: { color: '#22c55e', callback: v => v + ' đơn' }
        }
      }
    }
  });
})();


// ========================================================
// CHART 2: Donut - Cơ cấu doanh số
// ========================================================
(function() {
  const labels = <?= $jsDonutLabels ?>;
  const data   = <?= $jsDonutData ?>;
  const colors = <?= $jsDonutColors ?>;

  const ctx = document.getElementById('chartDonut');
  if (!ctx || !labels.length) return;

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderColor: '#1a1d2e',
        borderWidth: 3,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(26,29,46,0.95)',
          borderColor: '#2a2f4a',
          borderWidth: 1,
          callbacks: {
            label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.raw + '%'; }
          }
        }
      }
    }
  });
})();

// ========================================================
// Activity Filter
// ========================================================
function filterActivity(type) {
  document.querySelectorAll('.activity-row').forEach(function(row) {
    row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
  });
  ['fAll','fHD','fGia'].forEach(function(id) {
    document.getElementById(id).style.borderColor = '';
    document.getElementById(id).style.color = '';
  });
  const activeBtn = type === 'all' ? 'fAll' : (type === 'hoadon' ? 'fHD' : 'fGia');
  document.getElementById(activeBtn).style.borderColor = 'var(--blue)';
  document.getElementById(activeBtn).style.color = 'var(--blue-light)';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
