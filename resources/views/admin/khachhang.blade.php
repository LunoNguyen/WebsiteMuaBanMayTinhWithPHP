@extends('layouts.admin', ['title' => 'Quản lý Khách hàng', 'breadcrumb' => ['Quản lý', 'Khách hàng']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Khách hàng</h1>
          <p>Tổng <strong style="color:var(--blue-light)">{{ formatNum($total) }}</strong> khách hàng</p>
        </div>
        <div class="page-header-right">
          <button class="btn btn-outline" onclick="exportTableCSV('khTable','khachhang')">Xuất CSV</button>
          <a href="{{ route('admin.khachhang.create') }}" class="btn btn-primary">＋ Thêm khách hàng</a>
        </div>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="flex:1">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm tên, SĐT, email khách hàng..." />
          </div>
          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
          <a href="{{ route('admin.khachhang') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="khTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Khách hàng</th>
                <th>Liên hệ</th>
                <th>Địa chỉ</th>
                <th>Đơn hàng</th>
                <th>Chi tiêu</th>
                <th>Hạng</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($khachhang as $i => $kh) @php $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), array_slice(explode(' ',$kh['TENKH']),-2))); $color = $avatarColors[$i % count($avatarColors)]; $chiTieu = floatval($kh['tong_chi_tieu'] ?? 0); $hang = $chiTieu >= 50000000 ? ['💎 VIP','#b45309'] : ($chiTieu >= 20000000 ? ['🥇 Gold','#15803d'] : ($chiTieu >= 5000000 ? ['🥈 Silver','#707070'] : ['🥉 Bronze','#8a5a2b'])); @endphp
              <tr>
                <td style="color:var(--text-muted);font-size:12px">{{ $offset+$i+1 }}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:38px;height:38px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white;flex-shrink:0">
                      {{ mb_substr($initials,0,2) }}
                    </div>
                    <div>
                      <div style="font-weight:600;font-size:13px">{{ $kh['TENKH'] }}</div>
                      <div style="font-size:11px;color:var(--text-muted)">
                        <code style="background:var(--bg-main);padding:1px 4px;border-radius:3px">{{ $kh['MAKH'] }}</code>
                        @if ($kh['GIOITINH'] !== null)
                          &bull; {{ $kh['GIOITINH'] ? '👨 Nam' : '👩 Nữ' }}
                        @endif
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="font-size:13px">{!! icon('phone') !!} {{ $kh['SDT_KH'] ?? '—' }}</div>
                  <div style="font-size:12px;color:var(--text-muted)">{!! icon('mail') !!} {{ $kh['EMAIL_KH'] ?? '—' }}</div>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);max-width:200px">
                  <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $kh['DIACHI_KH'] ?? '—' }}</div>
                </td>
                <td style="text-align:center">
                  <div style="font-size:18px;font-weight:700;color:var(--text-primary)">{{ $kh['so_hd'] ?? 0 }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">đơn hàng</div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--blue-light)">{{ formatVND($chiTieu) }}</div>
                </td>
                <td>
                  <span style="background:color-mix(in srgb,{{ $hang[1] }} 12%,transparent);color:{{ $hang[1] }};border:1px solid color-mix(in srgb,{{ $hang[1] }} 30%,transparent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    {{ $hang[0] }}
                  </span>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="{{ route('admin.khachhang.show', $kh['MAKH']) }}" class="btn-icon" title="Chi tiết">{!! icon('eye', 15) !!}</a>
                    <a href="{{ route('admin.khachhang.edit', $kh['MAKH']) }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                    <a href="{{ route('admin.donhang', ['makh' => $kh['MAKH']]) }}" class="btn-icon" title="Xem đơn hàng">{!! icon('cart', 15) !!}</a>
                  </div>
                </td>
              </tr>
              @endforeach
              @if (empty($khachhang))
              <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">{!! icon('user') !!}</div><p>Không tìm thấy khách hàng nào</p></div></td></tr>
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
