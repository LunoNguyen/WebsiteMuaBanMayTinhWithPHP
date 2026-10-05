@extends('layouts.admin', ['title' => 'Quản lý Nhập hàng', 'breadcrumb' => ['Quản lý', 'Nhập hàng']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Nhập hàng</h1>
          <p>Tổng tháng này: <strong style="color:var(--blue-light)">{{ formatVND($tongNhapThang['tong'] ?? 0) }}</strong></p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('pnhTable','phieunhap')">Xuất CSV</button>
          <a href="{{ url('admin/nhaphang-them') }}" class="btn btn-primary">＋ Tạo phiếu nhập</a>
        </div>
      </div>

      <!-- Quick stats -->
      <div class="stats-grid stats-grid-3" style="gap:12px;margin-bottom:20px">
        @foreach ([
          ['ChuaThanhToan','💳','Chưa thanh toán','orange'],
          ['DaThanhToan',  '✅','Đã thanh toán',  'green'],
          ['HoanTien',     '↩️','Hoàn tiền',       'gray'],
        ] as [$key,$icon,$label,$c])
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:16px;display:flex;align-items:center;gap:12px">
          <span style="font-size:24px">{{ $icon }}</span>
          <div>
            <div style="font-size:12px;color:var(--text-muted)">{{ $label }}</div>
            <div style="font-size:20px;font-weight:700;color:var(--text-primary)">{{ $statsTT[$key] }} phiếu</div>
          </div>
        </div>
        @endforeach
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:220px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Mã phiếu, tên NCC..." />
          </div>
          <select name="trangthai" class="form-control" style="width:160px">
            <option value="">Tất cả trạng thái</option>
            @foreach (['ChoDuyet','DaDuyet','DaNhan','HoanThanh','DaHuy'] as $tt)
            <option value="{{ $tt }}" {{ $trangthai===$tt?'selected':'' }}>{{ $statusMap[$tt][1] ?? $tt }}</option>
            @endforeach
          </select>
          <select name="mancc" class="form-control" style="width:180px">
            <option value="">Tất cả NCC</option>
            @foreach ($nccList as $ncc)
            <option value="{{ $ncc['MANCC'] }}" {{ $mancc===$ncc['MANCC']?'selected':'' }}>{{ $ncc['TENNCC'] }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.nhaphang') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="pnhTable">
            <thead>
              <tr>
                <th>Mã phiếu</th>
                <th>Nhà cung cấp</th>
                <th>Nhân viên kho</th>
                <th>Ngày đặt / Nhận</th>
                <th>SP / SL</th>
                <th>Tổng tiền</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($phieunhap as $pnh) @php $st = $statusMap[$pnh['TRANGTHAI']] ?? ['var(--text-secondary)','—']; $tt = $ttMap[$pnh['TRANGTHAI_THANHTOAN']] ?? ['var(--text-secondary)','—']; @endphp
              <tr>
                <td><span style="font-family:monospace;font-weight:700;color:var(--blue-light)">{{ $pnh['MAPNH'] }}</span></td>
                <td>
                  <div style="font-weight:600;font-size:13px">{{ $pnh['TENNCC'] }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $pnh['MANCC'] }}</div>
                </td>
                <td style="font-size:13px">{{ $pnh['TENNV'] ?? '—' }}</td>
                <td>
                  <div style="font-size:12px">{{ $pnh['NGAY_DATMUA'] ? date('d/m/Y',strtotime($pnh['NGAY_DATMUA'])) : '—' }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $pnh['NGAYNHAN'] ? '📥 '.date('d/m/Y',strtotime($pnh['NGAYNHAN'])) : '—' }}</div>
                </td>
                <td style="text-align:center">
                  <div style="font-weight:600">{{ $pnh['so_san_pham'] }} SP</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ formatNum($pnh['tong_sl'] ?? 0) }} cái</div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--text-primary)">{{ formatVND($pnh['TONGCONG_PNH'] ?? 0) }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">VAT {{ $pnh['THUE_VAT'] }}% / CK {{ $pnh['CHIETKHAU'] }}%</div>
                </td>
                <td>
                  <span style="background:color-mix(in srgb,{{ $tt[0] }} 12%,transparent);color:{{ $tt[0] }};border:1px solid color-mix(in srgb,{{ $tt[0] }} 30%,transparent);padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600">{{ $tt[1] }}</span>
                </td>
                <td>
                  <span style="background:color-mix(in srgb,{{ $st[0] }} 12%,transparent);color:{{ $st[0] }};border:1px solid color-mix(in srgb,{{ $st[0] }} 30%,transparent);padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600">{{ $st[1] }}</span>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="{{ url('admin/nhaphang-chitiet') }}?mapnh={{ $pnh['MAPNH'] }}" class="btn-icon" title="Chi tiết">{!! icon('eye', 15) !!}</a>
                    @if ($pnh['TRANGTHAI'] === 'ChoDuyet')
                    <a href="{{ url('admin/nhaphang-sua') }}?mapnh={{ $pnh['MAPNH'] }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
              @if (empty($phieunhap))
              <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">{!! icon('package') !!}</div><p>Không có phiếu nhập hàng nào</p></div></td></tr>
              @endif
            </tbody>
          </table>
        </div>
        @if ($pages > 1)
        <div class="pagination">
          @if ($page>1)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}" class="page-link">‹</a>@endif
          @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
            <a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$p])) }}" class="page-link {{ $p===$page?'active':'' }}">{{ $p }}</a>
          @endfor
          @if ($page<$pages)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}" class="page-link">›</a>@endif
        </div>
        @endif
      </div>
@endsection
