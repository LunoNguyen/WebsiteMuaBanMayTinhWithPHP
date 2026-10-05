@extends('layouts.admin', ['title' => 'Quản lý Tài khoản', 'breadcrumb' => ['Quản lý', 'Tài khoản']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Tài khoản</h1>
          <p>Tổng <strong style="color:var(--blue-light)">{{ formatNum($total) }}</strong> tài khoản trong hệ thống</p>
        </div>
        <div class="page-header-right">
          <a href="{{ url('admin/taikhoan-them') }}" class="btn btn-primary">＋ Tạo tài khoản</a>
        </div>
      </div>

      @include('partials.thong-bao')

      <!-- Type stats chips -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        @foreach (['Admin','NhanVien','KhachHang'] as $lt)
        <a href="?loai={{ $lt }}" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;{{ $loaifil===$lt?'border-color:var(--blue);background:var(--blue-glow)':'' }}">
          <span style="display:inline-flex">{!! icon($loaiIcon[$lt], 14) !!}</span>
          <span style="font-size:13px;color:var(--text-secondary)">{{ $lt }}</span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px">{{ $typeMap[$lt] ?? 0 }}</strong>
        </a>
        @endforeach
        @if ($loaifil || $ttfil)<a href="{{ route('admin.taikhoan') }}" class="btn btn-sm btn-outline">Xóa lọc</a>@endif
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:260px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm mã TK, email, tên..." />
          </div>
          <select name="trangthai" class="form-control" style="width:160px">
            <option value="">Tất cả trạng thái</option>
            <option value="HoatDong"     {{ $ttfil==='HoatDong'?'selected':'' }}>Hoạt động</option>
            <option value="KhoaTamThoi"  {{ $ttfil==='KhoaTamThoi'?'selected':'' }}>Khoá tạm</option>
            <option value="KhoaVinhVien" {{ $ttfil==='KhoaVinhVien'?'selected':'' }}>Khoá vĩnh viễn</option>
          </select>
          <input type="hidden" name="loai" value="{{ $loaifil }}" />
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.taikhoan') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Tài khoản</th>
                <th>Loại</th>
                <th>Người dùng</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Cập nhật</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($taikhoan as $tk) @php $loai = $tk['LOAI_TAIKHOAN']; $icon = $loaiIcon[$loai] ?? 'user'; $color = $loaiColor[$loai] ?? 'var(--text-muted)'; $name = $tk['TENKH'] ?? $tk['TENNV'] ?? 'Không xác định'; @endphp
              <tr>
                <td>
                  <div style="font-family:monospace;font-weight:700;font-size:13px;color:var(--blue-light)">{{ $tk['MATK'] }}</div>
                  <div style="font-size:12px;color:var(--text-muted)">{!! icon('mail') !!} {{ $tk['EMAIL_TK'] }}</div>
                </td>
                <td>
                  <span style="background:color-mix(in srgb,{{ $color }} 12%,transparent);color:{{ $color }};border:1px solid color-mix(in srgb,{{ $color }} 30%,transparent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    {!! icon($icon, 13) !!} {{ $loai }}
                  </span>
                </td>
                <td>
                  <div style="font-size:13px;font-weight:600">{{ $name }}</div>
                  @if ($tk['TENCV'])
                  <div style="font-size:11px;color:var(--text-muted)">{{ $tk['TENCV'] }}</div>
                  @elseif ($tk['SDT_KH'])
                  <div style="font-size:11px;color:var(--text-muted)">{!! icon('phone') !!} {{ $tk['SDT_KH'] }}</div>
                  @endif
                </td>
                <td>
                  @php $ttC = $ttColor[$tk['TRANGTHAI']] ?? 'var(--text-secondary)'; @endphp
                  <span style="background:color-mix(in srgb,{{ $ttC }} 12%,transparent);color:{{ $ttC }};border:1px solid color-mix(in srgb,{{ $ttC }} 30%,transparent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600">
                    {{ $ttLabel[$tk['TRANGTHAI']] ?? $tk['TRANGTHAI'] }}
                  </span>
                </td>
                <td style="font-size:12px;color:var(--text-secondary)">{{ date('d/m/Y H:i', strtotime($tk['NGAYTAO'])) }}</td>
                <td style="font-size:12px;color:var(--text-muted)">{{ $tk['NGAY_CAPNHAT'] ? date('d/m/Y H:i', strtotime($tk['NGAY_CAPNHAT'])) : '—' }}</td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="{{ url('admin/taikhoan-sua') }}?matk={{ $tk['MATK'] }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                    @if ($tk['LOAI_TAIKHOAN'] !== 'Admin')
                    <x-nut-hanh-dong :action="route('admin.taikhoan.trang-thai', $tk['MATK'])" method="PATCH"
                       confirm="Thay đổi trạng thái tài khoản?"
                       class="btn-icon" :title="$tk['TRANGTHAI'] === 'HoatDong' ? 'Khoá' : 'Mở khoá'"
                       style="border-color:{{ $tk['TRANGTHAI'] === 'HoatDong' ? 'color-mix(in srgb,var(--red) 30%,transparent)' : 'color-mix(in srgb,var(--green) 30%,transparent)' }}">
                      {!! $tk['TRANGTHAI'] === 'HoatDong' ? icon('lock', 15) : icon('unlock', 15) !!}
                    </x-nut-hanh-dong>
                    @endif
                    <button class="btn-icon" title="Reset mật khẩu" onclick="showToast('Tính năng đang phát triển','info')">{!! icon('refresh', 15) !!}</button>
                  </div>
                </td>
              </tr>
              @endforeach
              @if (empty($taikhoan))
              <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">{!! icon('key', 15) !!}</div><p>Không tìm thấy tài khoản nào</p></div></td></tr>
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
