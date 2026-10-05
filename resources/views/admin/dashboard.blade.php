@extends('layouts.admin', ['title' => 'Tổng quan', 'breadcrumb' => ['Tổng quan']])

@section('content')

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
            <span>{!! icon('calendar') !!}</span>
            Tháng {{ date('m/Y') }}
          </div>
          <a href="{{ route('admin.baocao') }}" class="btn btn-outline">
            <span>{!! icon('chart') !!}</span> Báo cáo đầy đủ
          </a>
          <button class="btn btn-primary" onclick="exportTableCSV('activityTable','hoatdong_{{ date('Ymd') }}')">
            <span>{!! icon('download') !!}</span> Xuất báo cáo PDF
          </button>
        </div>
      </div>

      <!-- ======================================================
           KPI STATS CARDS
           ====================================================== -->
      <div class="stats-grid" data-rt-vung="kpi" data-rt-khi="don sp">
        <!-- Doanh thu -->
        <div class="stat-card" style="--accent-color:#3a56e4">
          <div class="stat-card-top">
            <span class="stat-card-label">Tổng doanh thu (HĐ)</span>
            <div class="stat-card-icon" style="color:var(--blue)">{!! icon('wallet') !!}</div>
          </div>
          <div class="stat-card-value" style="font-size:20px;color:var(--blue)">
            {{ formatVND($kpiDT['tong_dt'] ?? 0) }}
          </div>
          <div class="stat-card-sub">
            <span class="stat-card-trend trend-up">▲ +8.2%</span>
            so với T{{ date('m', strtotime('-1 month')) }}/{{ date('Y') }}
          </div>
        </div>

        <!-- Sản phẩm đang bán -->
        <div class="stat-card" style="--accent-color:#15803d">
          <div class="stat-card-top">
            <span class="stat-card-label">Sản phẩm đang bán</span>
            <div class="stat-card-icon" style="color:var(--green)">{!! icon('laptop') !!}</div>
          </div>
          <div class="stat-card-value" style="color:var(--green)">
            <span data-count="{{ $kpiSP['cnt'] ?? 0 }}" data-suffix=" SP">0 SP</span>
          </div>
          <div class="stat-card-sub">
            10 danh mục sản phẩm &bull;
            <strong style="color:var(--blue)">{{ $donCho['cnt'] ?? 0 }}</strong> đơn chờ duyệt
          </div>
        </div>

        <!-- Khách hàng -->
        <div class="stat-card" style="--accent-color:#6d28d9">
          <div class="stat-card-top">
            <span class="stat-card-label">Khách hàng</span>
            <div class="stat-card-icon" style="color:var(--purple)">{!! icon('user') !!}</div>
          </div>
          <div class="stat-card-value" style="color:var(--purple)">
            <span data-count="{{ $kpiKH['cnt'] ?? 0 }}">0</span>
          </div>
          <div class="stat-card-sub">
            <span class="stat-card-trend trend-up">▲ +3 KH</span>
            tháng này &bull;
            <strong style="color:var(--green)">{{ $kpiTK['cnt'] ?? 0 }}</strong> tài khoản
          </div>
        </div>

        <!-- Nhân viên -->
        <div class="stat-card" style="--accent-color:#b45309">
          <div class="stat-card-top">
            <span class="stat-card-label">Nhân viên hoạt động</span>
            <div class="stat-card-icon" style="color:var(--orange)">‍💼</div>
          </div>
          <div class="stat-card-value" style="color:var(--orange)">
            <span data-count="{{ $kpiNV['cnt'] ?? 0 }}">0</span>
          </div>
          <div class="stat-card-sub">
            <span data-count="{{ $donGiao['cnt'] ?? 0 }}">0</span> đơn đang giao &bull;
            <strong style="color:var(--green)">100%</strong> hoạt động
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
              <h3>Doanh thu &amp; Tăng trưởng 6 Tháng</h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:3px">Giai đoạn từ tháng {{ count($dtThang) ? $chartLabels[0] : '—' }} đến {{ count($dtThang) ? end($chartLabels) : '—' }}</p>
            </div>
            <div style="display:flex;gap:12px;font-size:12px;color:var(--text-muted)">
              <span style="color:var(--blue)">■</span> Doanh thu
              <span style="color:var(--green)">■</span> Số đơn/người
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
            <h3>Cơ cấu Doanh số</h3>
            <a href="{{ route('admin.baocao') }}" style="font-size:12px;color:var(--blue-light)">Xem chi tiết ›</a>
          </div>
          <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:16px">
              <div style="display:flex;justify-content:center">
                <canvas id="chartDonut" style="max-height:160px;max-width:160px"></canvas>
              </div>
              <div class="donut-legend">
                @foreach ($coSua as $i => $cs)
                <div class="legend-item">
                  <div class="legend-dot" style="background:{{ $donutColors[$i % count($donutColors)] }}"></div>
                  <span>{{ $cs['TENLOAI'] }}</span>
                  <span class="legend-value">{{ number_format($cs['tong_tien'] / $tongDoanhThu * 100, 0) }}%</span>
                  <span style="font-size:11px;color:var(--text-muted);margin-left:4px">({{ formatVND($cs['tong_tien']) }})</span>
                </div>
                @endforeach
                @if (empty($coSua))
                <p style="text-align:center;color:var(--text-muted);font-size:13px">Chưa có dữ liệu</p>
                @endif
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
        <div class="card" data-rt-vung="ton-kho" data-rt-khi="sp">
          <div class="card-header">
            <div>
              <h3>Cảnh báo Tồn kho &amp; Đề xuất Nhập</h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Thấp hơn mức tối thiểu 20 cái</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <span style="background:rgba(200,30,30,0.1);color:var(--red);border:1px solid rgba(200,30,30,0.3);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                {{ count($tonKhoThap) }} mặt hàng
              </span>
              <a href="{{ route('admin.sanpham') }}?filter=low_stock" class="btn btn-sm btn-outline">Xem tất cả kho</a>
            </div>
          </div>
          <div class="card-body" style="padding:16px">
            @foreach ($tonKhoThap as $sp) @php $stockClass = $sp['SOLUONGTON'] <= 5 ? 'stock-danger' : ($sp['SOLUONGTON'] <= 10 ? 'stock-warning' : 'stock-ok'); @endphp
            <div class="product-item">
              <div class="product-thumb">
                {{ (strpos($sp['TENLOAI'],'Laptop') !== false) ? '💻' : ((strpos($sp['TENLOAI'],'PC') !== false) ? '🖥️' : '🖱️') }}
              </div>
              <div class="product-info">
                <div class="product-name">{{ $sp['TENSP'] }}</div>
                <div class="product-meta">
                  {{ $sp['TENLOAI'] }} &bull; {{ $sp['TENNSX'] }} &bull; 
                  <strong style="color:var(--blue-light)">{{ formatVND($sp['DONGIA_SP']) }}</strong>
                </div>
              </div>
              <span class="stock-badge {{ $stockClass }}">
                Còn {{ $sp['SOLUONGTON'] }} cái
              </span>
              <a href="{{ route('admin.nhaphang.create', ['masp' => $sp['MASP']]) }}" class="btn btn-sm btn-primary" style="margin-left:8px">
                Nhập
              </a>
            </div>
            @endforeach
            @if (empty($tonKhoThap))
            <div class="empty-state" style="padding:24px">
              <div class="empty-icon">{!! icon('check') !!}</div>
              <p>Tất cả sản phẩm còn đủ hàng</p>
            </div>
            @endif

            @if (!empty($tonKhoThap))
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:13px;color:var(--text-secondary)">
                Tổng giá trị nhập dự kiến:
                <strong style="color:var(--text-primary)">
                  {{ formatVND(array_sum(array_map(fn($s) => $s['DONGIA_SP'] * max(1, 20 - $s['SOLUONGTON']), $tonKhoThap))) }}
                </strong>
              </span>
              <a href="{{ route('admin.nhaphang') }}" class="btn btn-sm btn-primary">Đặt mua ngay →</a>
            </div>
            @endif
          </div>
        </div>

        <!-- Khuyến mãi đang chạy -->
        <div class="card">
          <div class="card-header">
            <h3>Khuyến Mãi Đang Chạy</h3>
            <a href="{{ route('admin.khuyenmai') }}" style="font-size:12px;color:var(--blue-light)">Thêm mới ＋</a>
          </div>
          <div class="card-body" style="padding:16px">
            @foreach ($kmHoatDong as $km) @php $kmIsActive = $km['TRANGTHAI'] === 'HoatDong'; $pct = $km['SOLUONG_MA'] ? round($km['DA_SUDUNG'] / $km['SOLUONG_MA'] * 100) : 0; @endphp
            <div class="km-card">
              <div class="km-card-top">
                <div class="km-icon" style="background:{{ $kmIsActive ? 'rgba(21,128,61,0.15)' : 'rgba(180,83,9,0.15)' }}">
                  {{ $km['LOAI_KM'] === 'PhanTram' ? '🏷️' : '💵' }}
                </div>
                <div class="km-info">
                  <div class="km-name">{{ $km['TENKM'] }}</div>
                  <div class="km-code">
                    <code style="background:var(--bg-main);padding:1px 6px;border-radius:4px;font-size:11px">{{ $km['MA_CODE'] }}</code>
                    &nbsp;
                    @if ($kmIsActive)
                      <span style="color:var(--green);font-size:11px">● Hoạt động</span>
                    @else
                      <span style="color:var(--orange);font-size:11px">● Tạm dừng</span>
                    @endif
                  </div>
                </div>
                <div class="km-value" style="color:{{ $kmIsActive ? 'var(--green)' : 'var(--orange)' }}">
                  @if ($km['LOAI_KM'] === 'PhanTram')
                    Giảm {{ $km['GIATRI_KM'] }}%
                    @if ($km['SOTIENTOIDA_KM'])
                      <div style="font-size:11px;font-weight:400;color:var(--text-muted)">(Tối đa {{ formatVND($km['SOTIENTOIDA_KM']) }})</div>
                    @endif
                  @else
                    Giảm {{ formatVND($km['GIATRI_KM']) }}
                  @endif
                </div>
              </div>
              <div class="km-card-bottom">
                <span>
                  HSD: <strong>{{ $km['NGAYKT'] ? date('d/m/Y', strtotime($km['NGAYKT'])) : '∞' }}</strong>
                </span>
                @if ($km['SOLUONG_MA'])
                <span>
                  Đã dùng: {{ $km['DA_SUDUNG'] }}/{{ $km['SOLUONG_MA'] }}
                  <span style="display:inline-block;width:40px;height:4px;background:var(--border);border-radius:2px;margin-left:4px;vertical-align:middle">
                    <span style="display:block;width:{{ $pct }}%;height:100%;background:var(--blue-solid);border-radius:2px"></span>
                  </span>
                </span>
                @else
                <span style="color:var(--green)">Không giới hạn lượt dùng</span>
                @endif
              </div>
            </div>
            @endforeach
            @if (empty($kmHoatDong))
            <div class="empty-state" style="padding:24px">
              <div class="empty-icon">{!! icon('tag') !!}</div>
              <p>Chưa có khuyến mãi nào đang chạy</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- ======================================================
           ROW 4: NHẬT KÝ HOẠT ĐỘNG & KIỂM SOÁT
           ====================================================== -->
      <div class="card" data-rt-vung="nhat-ky" data-rt-khi="don sp">
        <div class="card-header">
          <div>
            <h3>Nhật ký Hoạt động &amp; Đối Soát Kiểm Toán</h3>
            <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Các hoạt động bán hàng và cập nhật hệ thống gần đây nhất</p>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('admin.donhang') }}" class="btn btn-sm btn-outline">Tất cả đơn hàng</a>
            <button class="btn btn-sm btn-outline" onclick="exportTableCSV('activityTable','nhatky')">
              Xuất CSV
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
              @foreach ($activityHD as $act)
              <tr class="activity-row" data-type="hoadon">
                <td>
                  <a href="{{ route('admin.donhang.show', $act['MAHD']) }}" style="font-family:monospace;font-size:13px;color:var(--blue-light);font-weight:700">
                    {{ $act['MAHD'] }}
                  </a>
                </td>
                <td>
                  <div style="font-size:13px;color:var(--text-primary)">
                    <strong>{{ $act['TENKH'] ?? 'Khách vãng lai' }}</strong> — Đặt hàng online
                  </div>
                  <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                    NV xử lý: {{ $act['TENNV'] ?? '—' }}
                  </div>
                </td>
                <td style="font-size:13px">{{ $act['TENNV'] ?? 'Hệ thống' }}</td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)">{{ date('d/m/Y', strtotime($act['NGAYLAP'])) }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ date('H:i', strtotime($act['NGAYLAP'])) }}</div>
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--blue-light)">{{ formatVND($act['TONGTIEN_HD']) }}</td>
                <td>{!! statusBadge($act['TRANGTHAI'], 'hoadon') !!}</td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="{{ route('admin.donhang.show', $act['MAHD']) }}" class="btn-icon" title="Xem chi tiết">{!! icon('eye', 15) !!}</a>
                    @if ($act['TRANGTHAI'] === 'ChoXacNhan')
                    <x-nut-hanh-dong :action="route('admin.donhang.buoc-tiep-theo', $act['MAHD'])" method="PATCH"
                        :confirm="'Xác nhận đơn '.$act['MAHD'].'?'"
                        class="btn-icon" title="Xác nhận" style="border-color:var(--green);color:var(--green)">{!! icon('check', 15) !!}</x-nut-hanh-dong>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach

              @foreach ($activityGia as $act)
              <tr class="activity-row" data-type="gia">
                <td>
                  <span style="font-family:monospace;font-size:12px;color:var(--purple)">{{ $act['MASP'] }}</span>
                </td>
                <td>
                  <div style="font-size:13px;color:var(--text-primary)">
                    Cập nhật giá sản phẩm: <strong>{{ mb_substr($act['TENSP'], 0, 35) }}...</strong>
                  </div>
                  <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $act['GHI_CHU'] }}</div>
                </td>
                <td style="font-size:13px">{{ $act['TENNV'] ?? 'Hệ thống' }}</td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)">{{ date('d/m/Y', strtotime($act['NGAY_CAPNHAT'])) }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ date('H:i', strtotime($act['NGAY_CAPNHAT'])) }}</div>
                </td>
                <td>
                  <div style="font-size:12px;color:var(--text-muted);text-decoration:line-through">{{ formatVND($act['DONGIA_CU']) }}</div>
                  <div style="font-size:13px;font-weight:700;color:var(--green)">{{ formatVND($act['DONGIA_MOI']) }}</div>
                </td>
                <td>
                  @php $diff = $act['DONGIA_MOI'] - $act['DONGIA_CU']; @endphp
                  <span style="color:{{ $diff < 0 ? 'var(--green)' : ($diff > 0 ? 'var(--red)' : 'var(--text-muted)') }};font-size:12px;font-weight:600">
                    {{ $diff != 0 ? ($diff > 0 ? '▲' : '▼') . ' ' . formatVND(abs($diff)) : 'Không đổi' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('admin.sanpham.edit', $act['MASP']) }}" class="btn-icon" title="Xem sản phẩm">{!! icon('eye', 15) !!}</a>
                </td>
              </tr>
              @endforeach

              @if (empty($activityHD) && empty($activityGia))
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                  Chưa có dữ liệu hoạt động
                </td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid var(--border);text-align:right">
          <a href="{{ route('admin.donhang') }}" style="font-size:13px;color:var(--blue-light)">Xem tất cả hoạt động →</a>
        </div>
      </div>
@endsection

@push('scripts')
<script>
// ========================================================
// CHART 1: Doanh thu 6 tháng (Bar + Line combo)
// ========================================================
(function() {
  const labels  = @json($chartLabels);
  const dtData  = @json($chartDT);
  const donData = @json($chartDon);

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
            ? 'rgba(58,86,228,0.9)'
            : 'rgba(58,86,228,0.35)'),
          borderRadius: 6,
          borderSkipped: false,
          yAxisID: 'y',
        },
        {
          type: 'line',
          label: 'Số đơn',
          data: donData,
          borderColor: '#15803d',
          backgroundColor: 'rgba(21,128,61,0.1)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#15803d',
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
          borderColor: '#ececef',
          borderWidth: 1,
          titleColor: '#2c2c2c',
          bodyColor: '#707070',
          callbacks: {
            label: ctx => ctx.dataset.label === 'Doanh thu (₫)'
              ? ' ' + new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(ctx.raw)
              : ' ' + ctx.raw + ' đơn'
          }
        }
      },
      scales: {
        x: { grid: { color: '#ececef' }, ticks: { color: 'var(--text-muted)' } },
        y: {
          position: 'left',
          grid: { color: '#ececef' },
          ticks: {
            color: 'var(--text-muted)',
            callback: v => new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v) + '₫'
          }
        },
        y1: {
          position: 'right',
          grid: { drawOnChartArea: false },
          ticks: { color: 'var(--green)', callback: v => v + ' đơn' }
        }
      }
    }
  });
})();


// ========================================================
// CHART 2: Donut - Cơ cấu doanh số
// ========================================================
(function() {
  const labels = @json($donutLabels);
  const data   = @json($donutData);
  const colors = @json($donutColorsUsed);

  const ctx = document.getElementById('chartDonut');
  if (!ctx || !labels.length) return;

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderColor: '#ffffff',
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
          borderColor: '#ececef',
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
@endpush
