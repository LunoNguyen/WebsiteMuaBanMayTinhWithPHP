@extends('layouts.admin', ['title' => 'Quản lý Đơn hàng', 'breadcrumb' => ['Quản lý', 'Đơn hàng']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Đơn hàng</h1>
          <p>Tổng <strong style="color:var(--blue-light)">{{ formatNum($total) }}</strong> đơn hàng</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('hdTable','donhang_{{ date('Ymd') }}')">Xuất CSV</button>
          <a href="{{ route('admin.donhang.create') }}" class="btn btn-primary">＋ Tạo đơn mới</a>
        </div>
      </div>

      @include('partials.thong-bao')

      <!-- Quick Stats -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap" data-rt-vung="dem-don" data-rt-khi="don">
        @php $quickStats = [
          ['ChoXacNhan','dot','Chờ xác nhận','orange'],
          ['DaXacNhan', 'dot','Đã xác nhận', 'blue'],
          ['DangGiao',  'dot','Đang giao',   'purple'],
          ['HoanThanh', 'dot','Hoàn thành',  'green'],
          ['DaHuy',     'dot','Đã hủy',      'red'],
        ]; @endphp @foreach ($quickStats as [$key,$icon,$label,$clr]) @php $cnt = $statMap[$key] ?? 0; @endphp
        <a href="?trangthai={{ $key }}" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;transition:var(--transition);{{ $trangthai===$key?'border-color:var(--blue);background:var(--blue-glow)':'' }}">
          <span style="display:inline-flex;color:var(--{{ $clr }})">{!! icon('dot', 14) !!}</span>
          <span style="font-size:13px;color:var(--text-secondary)">{{ $label }}</span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px">{{ $cnt }}</strong>
        </a>
        @endforeach
        @if ($trangthai)
          <a href="{{ route('admin.donhang') }}" class="btn btn-sm btn-outline">Xóa lọc</a>
        @endif
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:240px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm mã HD, tên khách..." />
          </div>
          <select name="ptgh" class="form-control" style="width:150px">
            <option value="">Phương thức GH</option>
            <option value="GiaoHang" {{ $ptgh==='GiaoHang'?'selected':'' }}>Giao hàng</option>
            <option value="TaiQuay"  {{ $ptgh==='TaiQuay'?'selected':'' }}>Tại quầy</option>
          </select>
          <input type="date" name="from" value="{{ $from }}" class="form-control" style="width:140px" title="Từ ngày" />
          <input type="date" name="to"   value="{{ $to }}"   class="form-control" style="width:140px" title="Đến ngày" />
          <input type="hidden" name="trangthai" value="{{ $trangthai }}" />
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.donhang') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card" data-rt-vung="ds-don" data-rt-khi="don">
        <div class="table-wrapper">
          <table id="hdTable">
            <thead>
              <tr>
                <th>Mã HD</th>
                <th>Khách hàng</th>
                <th>Thời gian</th>
                <th>Giá trị</th>
                <th>Phương thức</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($donhang as $hd)
              <tr>
                <td>
                  <a href="{{ route('admin.donhang.show', $hd['MAHD']) }}" style="font-family:monospace;font-weight:700;color:var(--blue-light)">
                    {{ $hd['MAHD'] }}
                  </a>
                </td>
                <td>
                  <div style="font-weight:600;font-size:13px">{{ $hd['TEN_NGUOINHAN'] ?? $hd['TENKH'] ?? 'Khách vãng lai' }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $hd['SDT_NGUOINHAN'] ?? $hd['SDT_KH'] ?? '' }}</div>
                  @if ($hd['TENNV'])
                  <div style="font-size:11px;color:var(--text-muted)">NV: {{ $hd['TENNV'] }}</div>
                  @endif
                </td>
                <td>
                  <div style="font-size:13px">{{ date('d/m/Y', strtotime($hd['NGAYLAP'])) }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ date('H:i', strtotime($hd['NGAYLAP'])) }}</div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--text-primary)">{{ formatVND($hd['TONGTIEN_HD']) }}</div>
                  @if ($hd['TONGTIEN_GIAM'] > 0)
                  <div style="font-size:11px;color:var(--green)">Giảm {{ formatVND($hd['TONGTIEN_GIAM']) }}</div>
                  @endif
                </td>
                <td style="font-size:13px">
                  {{ $hd['PHUONG_THUC_GH'] === 'GiaoHang' ? '🚚 Giao hàng' : '🏪 Tại quầy' }}
                </td>
                <td>
                  <div style="font-size:12px;color:var(--text-secondary)">
                    <span style="display:inline-flex;vertical-align:-2px">{!! icon('wallet', 13) !!}</span> {{ $hd['PT_TT'] ?? '—' }}
                  </div>
                  {!! $hd['TT_TT'] ? statusBadge($hd['TT_TT'], 'thanhtoan') : '' !!}
                </td>
                <td>{!! statusBadge($hd['TRANGTHAI'], 'hoadon') !!}</td>
                <td>
                  <div style="display:flex;gap:5px;flex-wrap:wrap">
                    <a href="{{ route('admin.donhang.show', $hd['MAHD']) }}" class="btn-icon" title="Chi tiết">{!! icon('eye', 15) !!}</a>
                    @if (isset($validTransitions[$hd['TRANGTHAI']]))
                    <x-nut-hanh-dong :action="route('admin.donhang.buoc-tiep-theo', $hd['MAHD'])" method="PATCH"
                       :confirm="'Chuyển trạng thái đơn '.$hd['MAHD'].'?'"
                       class="btn btn-sm btn-success" style="padding:4px 10px;font-size:11px">
                      {{ $actionLabel[$hd['TRANGTHAI']] }}
                    </x-nut-hanh-dong>
                    @endif
                    @if (in_array($hd['TRANGTHAI'],['ChoXacNhan','DaXacNhan']))
                    <x-nut-hanh-dong :action="route('admin.donhang.huy', $hd['MAHD'])" method="PATCH"
                       :confirm="'Hủy đơn hàng '.$hd['MAHD'].'?'"
                       class="btn-icon" title="Hủy đơn" style="border-color:color-mix(in srgb,var(--red) 30%,transparent);color:var(--red)">{!! icon('ban', 15) !!}</x-nut-hanh-dong>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
              @if (empty($donhang))
              <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">{!! icon('cart') !!}</div><p>Không có đơn hàng nào</p></div></td></tr>
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
          <span style="font-size:12px;color:var(--text-muted);margin-left:8px">Trang {{ $page }}/{{ $pages }}</span>
        </div>
        @endif
      </div>
@endsection
