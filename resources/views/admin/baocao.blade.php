@extends('layouts.admin', ['title' => 'Báo cáo & Thống kê', 'breadcrumb' => ['Hệ thống', 'Báo cáo']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Báo cáo &amp; Thống kê</h1>
          <p>Tổng hợp hiệu quả kinh doanh từ dữ liệu hệ thống</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="window.print()">In báo cáo</button>
        </div>
      </div>

      <!-- KPI Summary -->
      <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card" style="--accent-color:#3a56e4">
          <div class="stat-card-top"><span class="stat-card-label">Tổng doanh thu</span><div class="stat-card-icon" style="color:var(--blue)">{!! icon('wallet') !!}</div></div>
          <div class="stat-card-value" style="font-size:18px;color:var(--blue)">{{ formatVND($kpiTotal['tong_dt'] ?? 0) }}</div>
          <div class="stat-card-sub">Từ {{ $kpiTotal['so_don'] ?? 0 }} đơn hoàn thành</div>
        </div>
        <div class="stat-card" style="--accent-color:#15803d">
          <div class="stat-card-top"><span class="stat-card-label">Giá trị trung bình/đơn</span><div class="stat-card-icon" style="color:var(--green)">{!! icon('chart') !!}</div></div>
          <div class="stat-card-value" style="color:var(--green)">{{ formatVND($kpiTotal['trung_binh'] ?? 0) }}</div>
          <div class="stat-card-sub">Giảm giá: {{ formatVND($kpiTotal['tong_giam'] ?? 0) }}</div>
        </div>
        <div class="stat-card" style="--accent-color:#6d28d9">
          <div class="stat-card-top"><span class="stat-card-label">Tổng đơn hoàn thành</span><div class="stat-card-icon" style="color:var(--purple)">{!! icon('check') !!}</div></div>
          <div class="stat-card-value" style="color:var(--purple)"><span data-count="{{ $kpiTotal['so_don'] ?? 0 }}">0</span></div>
          <div class="stat-card-sub">Tất cả thời gian</div>
        </div>
        <div class="stat-card" style="--accent-color:#b45309">
          <div class="stat-card-top"><span class="stat-card-label">Tháng hiện tại</span><div class="stat-card-icon" style="color:var(--orange)">{!! icon('calendar') !!}</div></div>
                    <div class="stat-card-value" style="font-size:18px;color:var(--orange)">{{ formatVND($thisMonth['doanhthu'] ?? 0) }}</div>
          <div class="stat-card-sub">{{ $thisMonth['so_don'] ?? 0 }} đơn trong tháng {{ date('m/Y') }}</div>
        </div>
      </div>

      <!-- Chart Row -->
      <div class="grid-7-5" style="margin-bottom:20px">
        <div class="card">
          <div class="card-header">
            <h3>Doanh thu 12 tháng gần nhất</h3>
          </div>
          <div class="card-body">
            <div style="height:250px"><canvas id="chartBaoCao"></canvas></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3>Phương thức thanh toán</h3></div>
          <div class="card-body">
            <div style="height:200px;display:flex;justify-content:center"><canvas id="chartPT"></canvas></div>
            <div style="margin-top:12px">
              @foreach ($dtPT as $pt) @php $color = $ptColors[$pt['PHUONG_THUC']] ?? '#4b5563'; @endphp
              <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border)">
                <div style="width:10px;height:10px;background:{{ $color }};border-radius:2px;flex-shrink:0"></div>
                <span style="font-size:13px"><span style="display:inline-flex;vertical-align:-2px">{!! icon('wallet', 13) !!}</span> {{ $pt['PHUONG_THUC'] }}</span>
                <span style="margin-left:auto;font-size:13px;font-weight:700;color:var(--text-primary)">{{ formatVND($pt['tong']) }}</span>
                <span style="font-size:11px;color:var(--text-muted)">{{ $pt['so_gd'] }} GD</span>
              </div>
              @endforeach
              @if (empty($dtPT))
              <p style="text-align:center;color:var(--text-muted);font-size:13px;padding:16px">Chưa có dữ liệu</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Tables Row -->
      <div class="grid-2">
        <!-- Top sản phẩm -->
        <div class="card">
          <div class="card-header"><h3>Top 5 Sản phẩm Bán chạy</h3></div>
          <div class="card-body" style="padding:16px">
            @foreach ($topSP as $rank => $sp) @php $pct = $sp['tong_dt'] / $maxDT * 100; $rankColors = ['var(--orange)','var(--text-muted)','#8a5a2b','var(--blue)','var(--green)']; $rc = $rankColors[$rank] ?? 'var(--text-muted)'; @endphp
            <div style="margin-bottom:14px">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                <span style="width:24px;height:24px;background:color-mix(in srgb,{{ $rc }} 12%,transparent);color:{{ $rc }};border:1px solid color-mix(in srgb,{{ $rc }} 30%,transparent);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">{{ $rank+1 }}</span>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $sp['TENSP'] }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $sp['TENLOAI'] }} &bull; {{ $sp['TENNSX'] }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                  <div style="font-size:13px;font-weight:700;color:var(--blue-light)">{{ formatVND($sp['tong_dt']) }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ formatNum($sp['tong_ban']) }} SP</div>
                </div>
              </div>
              <div style="height:4px;background:var(--border);border-radius:2px">
                <div style="height:100%;width:{{ round($pct) }}%;background:{{ $rc }};border-radius:2px;transition:width 0.8s ease"></div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Top khách hàng -->
        <div class="card">
          <div class="card-header"><h3>Top 5 Khách hàng VIP</h3></div>
          <div class="card-body table-wrapper" style="padding:0">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Khách hàng</th>
                  <th>Đơn</th>
                  <th>Chi tiêu</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($topKH as $rank => $kh)
                <tr>
                  <td style="font-weight:700;color:var(--text-muted)">{{ $rank + 1 }}</td>
                  <td>
                    <div style="font-weight:600;font-size:13px">{{ $kh['TENKH'] }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $kh['EMAIL_KH'] }}</div>
                  </td>
                  <td style="text-align:center;font-weight:600">{{ $kh['so_don'] }}</td>
                  <td style="font-weight:700;color:var(--blue-light)">{{ formatVND($kh['tong_chi_tieu']) }}</td>
                </tr>
                @endforeach
                @if (empty($topKH))
                <tr><td colspan="4"><div class="empty-state" style="padding:24px"><div class="empty-icon" style="font-size:32px">{!! icon('user') !!}</div><p>Chưa có dữ liệu</p></div></td></tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Top NV -->
      @if (!empty($topNV))
      <div class="card" style="margin-top:20px">
        <div class="card-header"><h3>Hiệu quả Nhân viên</h3></div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>#</th><th>Nhân viên</th><th>Chức vụ</th><th>Số đơn</th><th>Doanh số</th><th>% Tổng</th></tr></thead>
            <tbody>
              @foreach ($topNV as $rank => $nv) @php $pct = round($nv['tong_dt'] / $tongDT * 100, 1); @endphp
              <tr>
                <td>{{ ['🥇','🥈','🥉','4️⃣','5️⃣'][$rank] ?? ($rank+1) }}</td>
                <td style="font-weight:600">{{ $nv['TENNV'] }}</td>
                <td style="font-size:12px;color:var(--text-muted)">{{ $nv['TENCV'] ?? '—' }}</td>
                <td style="text-align:center;font-weight:600">{{ $nv['so_don'] }}</td>
                <td style="font-weight:700;color:var(--blue-light)">{{ formatVND($nv['tong_dt']) }}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px">
                    <div style="flex:1;height:6px;background:var(--border);border-radius:3px">
                      <div style="height:100%;width:{{ $pct }}%;background:var(--blue);border-radius:3px"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;min-width:36px">{{ $pct }}%</span>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif
@endsection

@push('scripts')
<script>
// Chart doanh thu 12 tháng
(function(){
  const labels  = @json($chartLabels);
  const dtData  = @json($chartDT);
  const donData = @json($chartDon);
  const ctx = document.getElementById('chartBaoCao');
  if(!ctx || !labels.length) {
    if(ctx) ctx.parentElement.innerHTML = '<div style="height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-muted)">Chưa có dữ liệu doanh thu trong 12 tháng gần nhất</div>';
    return;
  }
  new Chart(ctx,{
    data:{ labels, datasets:[
      { type:'bar', label:'Doanh thu',data:dtData, backgroundColor:'rgba(58,86,228,0.6)', borderRadius:6, yAxisID:'y' },
      { type:'line',label:'Đơn hàng',data:donData,borderColor:'#15803d',backgroundColor:'rgba(21,128,61,0.1)',fill:true,tension:0.4,yAxisID:'y1' }
    ]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'rgba(26,29,46,0.95)',borderColor:'#ececef',borderWidth:1 } },
      scales:{
        x:{grid:{color:'#ececef'},ticks:{color:'var(--text-muted)'}},
        y:{grid:{color:'#ececef'},ticks:{color:'var(--text-muted)',callback:function(v){return new Intl.NumberFormat('vi-VN',{notation:'compact'}).format(v)+'₫';}}},
        y1:{position:'right',grid:{drawOnChartArea:false},ticks:{color:'var(--green)',callback:function(v){return v+' đơn';}}}
      }
    }
  });
})();

// Donut PT TT
(function(){
  const labels = @json($ptLabels);
  const data   = @json($ptData);
  const colorMap = {'QR':'#3a56e4','COD':'#b45309','ChuyenKhoan':'#15803d','TienMat':'#6d28d9'};
  const colors = labels.map(function(l){ return colorMap[l] || '#4b5563'; });
  const ctx = document.getElementById('chartPT');
  if(!ctx||!labels.length) return;
  new Chart(ctx,{
    type:'doughnut',
    data:{ labels, datasets:[{data,backgroundColor:colors,borderColor:'#ffffff',borderWidth:3,hoverOffset:6}]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}} }
  });
})();
</script>
@endpush
