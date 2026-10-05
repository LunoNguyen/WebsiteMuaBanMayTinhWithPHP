@extends('layouts.admin', ['title' => 'Voucher & Khuyến mãi', 'breadcrumb' => ['Quản lý', 'Khuyến mãi']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Voucher &amp; Khuyến mãi</h1>
          <p>Quản lý mã giảm giá và chương trình khuyến mãi</p>
        </div>
        <div class="page-header-right">
          <a href="{{ route('admin.khuyenmai.create') }}" class="btn btn-primary">＋ Tạo khuyến mãi</a>
        </div>
      </div>

      @include('partials.thong-bao')

      <!-- Quick stats -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        @foreach ([
          ['HoatDong','🟢','Hoạt động'],
          ['TamDung', '🟡','Tạm dừng'],
          ['HetHan',  '⚫','Hết hạn'],
        ] as [$key,$icon,$label])
        <a href="?trangthai={{ $key }}" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;{{ $trangthai===$key?'border-color:var(--blue);background:var(--blue-glow)':'' }}">
          <span>{{ $icon }}</span>
          <span style="font-size:13px;color:var(--text-secondary)">{{ $label }}</span>
          <strong style="font-size:15px;color:var(--text-primary);margin-left:4px">{{ $statKMMap[$key] ?? 0 }}</strong>
        </a>
        @endforeach
        @if ($trangthai)<a href="{{ route('admin.khuyenmai') }}" class="btn btn-sm btn-outline">Xóa lọc</a>@endif
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:250px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm tên, mã voucher..." />
          </div>
          <select name="loaikm" class="form-control" style="width:180px">
            <option value="">Tất cả loại</option>
            <option value="PhanTram"     {{ $loaikm==='PhanTram'?'selected':'' }}>Phần trăm (%)</option>
            <option value="SoTienCoDinh" {{ $loaikm==='SoTienCoDinh'?'selected':'' }}>Số tiền cố định</option>
          </select>
          <input type="hidden" name="trangthai" value="{{ $trangthai }}" />
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.khuyenmai') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Voucher Grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
        @foreach ($khuyenmai as $km) @php $isActive = $km['TRANGTHAI'] === 'HoatDong'; $isExpired = $km['TRANGTHAI'] === 'HetHan'; $pct = $km['SOLUONG_MA'] ? min(100, round($km['DA_SUDUNG'] / $km['SOLUONG_MA'] * 100)) : null; $accentColor = $isActive ? '#15803d' : ($isExpired ? '#707070' : '#b45309'); @endphp
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:var(--transition);{{ $isExpired?'background:var(--bg-main)':'' }}"
             onmouseenter="this.style.borderColor='{{ $accentColor }}44'"
             onmouseleave="this.style.borderColor='var(--border)'">


          <div style="padding:16px">
            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px">
              <!-- Icon -->
              <div style="width:44px;height:44px;border-radius:10px;background:color-mix(in srgb,{{ $accentColor }} 12%,transparent);border:1px solid color-mix(in srgb,{{ $accentColor }} 30%,transparent);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                {{ $km['LOAI_KM'] === 'PhanTram' ? '🏷️' : '💵' }}
              </div>

              <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:4px">{{ $km['TENKM'] }}</div>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                  <code style="background:var(--bg-main);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-size:12px;color:var(--blue-light);">
                    {{ $km['MA_CODE'] ?? '—' }}
                  </code>
                  {!! statusBadge($km['TRANGTHAI'], 'khuyenmai') !!}
                </div>
              </div>

              <!-- Value -->
              <div style="text-align:right;flex-shrink:0">
                <div style="font-size:20px;font-weight:800;color:{{ $accentColor }}">
                  @if ($km['LOAI_KM']==='PhanTram')
                    -{{ $km['GIATRI_KM'] }}%
                  @else
                    -{{ formatVND($km['GIATRI_KM']) }}
                  @endif
                </div>
                @if ($km['SOTIENTOIDA_KM'] && $km['LOAI_KM']==='PhanTram')
                <div style="font-size:11px;color:var(--text-muted)">tối đa {{ formatVND($km['SOTIENTOIDA_KM']) }}</div>
                @endif
              </div>
            </div>

            <!-- Info grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;color:var(--text-muted);margin-bottom:12px">
              <div>
                <div>Bắt đầu</div>
                <div style="color:var(--text-secondary);font-weight:500">{{ $km['NGAYBD'] ? date('d/m/Y',strtotime($km['NGAYBD'])) : '—' }}</div>
              </div>
              <div>
                <div>Kết thúc</div>
                <div style="color:{{ (!$isExpired && $km['NGAYKT'] && strtotime($km['NGAYKT']) < strtotime('+3 days')) ? 'var(--red)' : 'var(--text-secondary)' }};font-weight:500">
                  {{ $km['NGAYKT'] ? date('d/m/Y',strtotime($km['NGAYKT'])) : '∞' }}
                </div>
              </div>
              <div>
                <div>Đơn tối thiểu</div>
                <div style="color:var(--text-secondary);font-weight:500">{{ $km['SOTIENTOITHIEU_NHANKM'] > 0 ? formatVND($km['SOTIENTOITHIEU_NHANKM']) : 'Không' }}</div>
              </div>
              <div>
                <div>Sản phẩm áp dụng</div>
                <div style="color:var(--text-secondary);font-weight:500">{{ $km['so_sp_ap_dung'] > 0 ? $km['so_sp_ap_dung'].' SP' : 'Tất cả' }}</div>
              </div>
            </div>

            <!-- Progress bar (nếu có giới hạn lượt) -->
            @if ($km['SOLUONG_MA'])
            <div style="margin-bottom:12px">
              <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <span>Đã dùng: {{ $km['DA_SUDUNG'] }}/{{ $km['SOLUONG_MA'] }} lượt</span>
                <span>{{ $pct }}%</span>
              </div>
              <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                <div style="height:100%;width:{{ $pct }}%;background:{{ $pct > 80 ? 'var(--red)' : ($pct > 50 ? 'var(--orange)' : 'var(--blue)') }};border-radius:3px;transition:width 0.5s ease"></div>
              </div>
            </div>
            @else
            <div style="font-size:12px;color:var(--green);margin-bottom:12px">Không giới hạn lượt dùng &bull; Đã dùng: {{ $km['DA_SUDUNG'] }} lần</div>
            @endif

            <!-- Actions -->
            <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--border)">
              <x-nut-hanh-dong :action="route('admin.khuyenmai.trang-thai', $km['MAKM'])" method="PATCH"
                 confirm="Thay đổi trạng thái khuyến mãi?"
                 class="btn btn-sm {{ $isActive ? 'btn-outline' : 'btn-success' }}">
                {!! $isActive ? icon('pause', 14).' Tạm dừng' : icon('play', 14).' Kích hoạt' !!}
              </x-nut-hanh-dong>
              <a href="{{ route('admin.khuyenmai.edit', $km['MAKM']) }}" class="btn btn-sm btn-outline">Sửa</a>
              <x-nut-hanh-dong :action="route('admin.khuyenmai.destroy', $km['MAKM'])" method="DELETE"
                 :confirm="'Xóa khuyến mãi '.$km['TENKM'].'?'"
                 class="btn btn-sm btn-outline" title="Xóa" style="border-color:color-mix(in srgb,var(--red) 30%,transparent);color:var(--red)">{!! icon('trash', 14) !!}</x-nut-hanh-dong>
            </div>
          </div>
        </div>
        @endforeach

        @if (empty($khuyenmai))
        <div style="grid-column:1/-1">
          <div class="empty-state"><div class="empty-icon">{!! icon('tag') !!}</div><p>Không tìm thấy khuyến mãi nào</p></div>
        </div>
        @endif
      </div>

      @if ($pages > 1)
      <div class="pagination" style="margin-top:16px">
        @if ($page>1)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}" class="page-link">‹</a>@endif
        @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
          <a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$p])) }}" class="page-link {{ $p===$page?'active':'' }}">{{ $p }}</a>
        @endfor
        @if ($page<$pages)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}" class="page-link">›</a>@endif
      </div>
      @endif
@endsection
