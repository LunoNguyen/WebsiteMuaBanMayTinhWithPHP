@extends('layouts.admin', ['title' => 'Quản lý Nhân viên', 'breadcrumb' => ['Quản lý', 'Nhân viên']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Nhân viên</h1>
          <p>Tổng <strong style="color:var(--blue-light)">{{ formatNum($total) }}</strong> nhân viên</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('nvTable','nhanvien')">Xuất CSV</button>
          <a href="{{ url('admin/nhanvien-them') }}" class="btn btn-primary">＋ Thêm nhân viên</a>
        </div>
      </div>

      @include('partials.thong-bao')

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:260px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm tên, mã, email nhân viên..." />
          </div>
          <select name="macv" class="form-control" style="width:200px">
            <option value="">Tất cả chức vụ</option>
            @foreach ($chucvuList as $cv)
            <option value="{{ $cv['MACV'] }}" {{ $macv===$cv['MACV']?'selected':'' }}>
              {{ $cv['TENCV'] }}
            </option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.nhanvien') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Grid cards view -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px">
        @foreach ($nhanvien as $i => $nv) @php $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), explode(' ', $nv['TENNV']))); $initials = mb_substr($initials, 0, 2); $color = $avatarColors[$i % count($avatarColors)]; $isActive = $nv['TRANGTHAI']; @endphp
        <div class="card" style="overflow:visible">
          <div class="card-body" style="padding:20px">
            <div style="display:flex;align-items:flex-start;gap:12px">
              <!-- Avatar -->
              <div style="width:52px;height:52px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:white;flex-shrink:0;">
                {{ $initials }}
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ $nv['TENNV'] }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                  <code style="background:var(--bg-main);padding:1px 5px;border-radius:3px;font-size:11px">{{ $nv['MANV'] }}</code>
                </div>
                <span style="background:rgba(58,86,228,0.1);color:var(--blue-light);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;margin-top:4px;display:inline-block">
                  {{ $nv['TENCV'] ?? '—' }}
                </span>
              </div>
              <!-- Status toggle -->
              <div style="position:relative;width:36px;height:20px;flex-shrink:0" title="{{ $isActive ? 'Đang làm việc' : 'Nghỉ việc' }}">
                <x-nut-hanh-dong :action="route('admin.nhanvien.trang-thai', $nv['MANV'])" method="PATCH"
                    confirm="Thay đổi trạng thái nhân viên?" title="Đổi trạng thái làm việc"
                    style="background:none;border:none;padding:0;cursor:pointer">
                  <div style="width:36px;height:20px;background:{{ $isActive ? 'var(--green-solid)' : 'var(--border-light)' }};border-radius:10px;display:flex;align-items:center;padding:2px;transition:var(--transition)">
                    <div style="width:16px;height:16px;background:white;border-radius:50%;margin-left:{{ $isActive ? '16px' : '0' }};transition:var(--transition)"></div>
                  </div>
                </x-nut-hanh-dong>
              </div>
            </div>

            <div style="margin-top:16px;display:flex;flex-direction:column;gap:6px">
              @if ($nv['SDT_NV'])
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>{!! icon('phone') !!}</span> <span>{{ $nv['SDT_NV'] }}</span>
              </div>
              @endif
              @if ($nv['EMAIL_NV'])
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>{!! icon('mail') !!}</span> <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $nv['EMAIL_NV'] }}</span>
              </div>
              @endif
              @if ($nv['NGAYVAOLAM'])
              <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary)">
                <span>{!! icon('calendar') !!}</span> <span>Vào làm: {{ date('d/m/Y', strtotime($nv['NGAYVAOLAM'])) }}</span>
              </div>
              @endif
            </div>

            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:12px;color:var(--text-muted)">
                {{ $nv['so_hd'] ?? 0 }} đơn hàng đã xử lý
              </span>
              <div style="display:flex;gap:6px">
                <a href="{{ url('admin/nhanvien-sua') }}?manv={{ $nv['MANV'] }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                <a href="{{ route('admin.taikhoan') }}?manv={{ $nv['MANV'] }}" class="btn-icon" title="Tài khoản">{!! icon('key', 15) !!}</a>
              </div>
            </div>
          </div>
        </div>
        @endforeach
        @if (empty($nhanvien))
        <div style="grid-column:1/-1">
          <div class="empty-state"><div class="empty-icon">{!! icon('users') !!}</div><p>Không tìm thấy nhân viên nào</p></div>
        </div>
        @endif
      </div>

      <!-- Table view (hidden, for export) -->
      <table id="nvTable" style="display:none">
        <thead><tr><th>Mã NV</th><th>Tên NV</th><th>Chức vụ</th><th>SĐT</th><th>Email</th><th>Ngày vào làm</th><th>Trạng thái</th></tr></thead>
        <tbody>
        @foreach ($nhanvien as $nv)
        <tr>
          <td>{{ $nv['MANV'] }}</td>
          <td>{{ $nv['TENNV'] }}</td>
          <td>{{ $nv['TENCV'] }}</td>
          <td>{{ $nv['SDT_NV'] }}</td>
          <td>{{ $nv['EMAIL_NV'] }}</td>
          <td>{{ $nv['NGAYVAOLAM'] ? date('d/m/Y',strtotime($nv['NGAYVAOLAM'])) : '' }}</td>
          <td>{{ $nv['TRANGTHAI'] ? 'Đang làm việc' : 'Nghỉ việc' }}</td>
        </tr>
        @endforeach
        </tbody>
      </table>

      @if ($pages > 1)
      <div class="pagination">
        @if ($page>1)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}" class="page-link">‹</a>@endif
        @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
          <a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$p])) }}" class="page-link {{ $p===$page?'active':'' }}">{{ $p }}</a>
        @endfor
        @if ($page<$pages)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}" class="page-link">›</a>@endif
      </div>
      @endif
@endsection
