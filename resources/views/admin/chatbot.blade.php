@extends('layouts.admin', ['title' => 'Lịch sử Chatbot', 'breadcrumb' => ['Hệ thống', 'Chatbot']])

@section('content')


      <div class="page-header">
        <div class="page-header-left">
          <h1>Lịch sử Chatbot</h1>
          <p>Xem lại các cuộc hội thoại của khách hàng với chatbot AI</p>
        </div>
      </div>

      <!-- Stats -->
      <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px">{!! icon('message') !!}</span>
          <div><div style="font-size:12px;color:var(--text-muted)">Tổng phiên chat</div><div style="font-size:20px;font-weight:700">{{ $statsTotal['cnt'] ?? 0 }}</div></div>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px">{!! icon('calendar') !!}</span>
          <div><div style="font-size:12px;color:var(--text-muted)">Hôm nay</div><div style="font-size:20px;font-weight:700">{{ $statsToday['cnt'] ?? 0 }}</div></div>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px">
          <span style="font-size:28px">{!! icon('dot') !!}</span>
          <div><div style="font-size:12px;color:var(--text-muted)">Đang chat</div><div style="font-size:20px;font-weight:700;color:var(--green)">{{ $statsActive['cnt'] ?? 0 }}</div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:{{ $selectedPhien ? '360px 1fr' : '1fr' }};gap:20px">
        <!-- Danh sách phiên -->
        <div class="card">
          <div class="card-header">
            <h3>Danh sách phiên chat</h3>
            <form method="GET" style="display:flex;gap:6px">
              <input type="text" name="q" value="{{ $search }}" placeholder="Tìm khách..." class="form-control" style="width:160px;padding:6px 10px" />
              <button type="submit" class="btn btn-sm btn-primary">{!! icon('search') !!}</button>
            </form>
          </div>
          <div style="padding:0">
            @foreach ($phienList as $phien) @php $isActive = $phien['TRANGTHAI'] === 'DangChat'; $isSelected = $phien['MAPHIEN'] == $selectedPhien; @endphp
            <a href="?maphien={{ $phien['MAPHIEN'] }}{{ $search ? '&q='.urlencode($search) : '' }}"
               style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);text-decoration:none;transition:var(--transition);background:{{ $isSelected?'var(--blue-glow)':'' }};{{ $isSelected?'border-left:3px solid var(--blue)':'' }}"
               onmouseenter="this.style.background='var(--bg-card-hover)'"
               onmouseleave="this.style.background='{{ $isSelected?'var(--blue-glow)':'' }}'">
              <div style="width:38px;height:38px;border-radius:50%;background:var(--bg-input);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;border:1px solid var(--border);position:relative">
                👤
                @if ($isActive)
                <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;background:var(--green);border-radius:50%;border:2px solid var(--bg-sidebar)"></div>
                @endif
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:13px;color:var(--text-primary)">
                  {{ $phien['TENKH'] ?? $phien['EMAIL_TK'] ?? 'Khách ẩn danh' }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                  {{ date('d/m/Y H:i', strtotime($phien['THOIGIAN_BD'])) }}
                  &bull; {{ $phien['so_tin'] }} tin nhắn
                </div>
              </div>
              @if ($isActive)
              <span style="background:rgba(21,128,61,0.1);color:var(--green);border:1px solid rgba(21,128,61,0.3);padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;flex-shrink:0">LIVE</span>
              @endif
            </a>
            @endforeach
            @if (empty($phienList))
            <div class="empty-state"><div class="empty-icon">{!! icon('bot') !!}</div><p>Chưa có phiên chat nào</p></div>
            @endif
          </div>
          @if ($pages > 1)
          <div class="pagination" style="padding:10px 16px">
            @if ($page>1)<a href="?page={{ $page-1 }}&q={{ urlencode($search) }}" class="page-link">‹</a>@endif
            @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
              <a href="?page={{ $p }}&q={{ urlencode($search) }}" class="page-link {{ $p===$page?'active':'' }}">{{ $p }}</a>
            @endfor
            @if ($page<$pages)<a href="?page={{ $page+1 }}&q={{ urlencode($search) }}" class="page-link">›</a>@endif
          </div>
          @endif
        </div>

        @if ($selectedPhien && $currentPhien)
        <!-- Chi tiết phiên -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>Phiên #{{ $selectedPhien }} — {{ $currentPhien['TENKH'] ?? $currentPhien['EMAIL_TK'] ?? 'Khách ẩn danh' }}</h3>
              <p style="font-size:12px;color:var(--text-muted);margin-top:3px">
                {{ date('d/m/Y H:i', strtotime($currentPhien['THOIGIAN_BD'])) }}
                {{ $currentPhien['THOIGIAN_KT'] ? ' → '.date('H:i', strtotime($currentPhien['THOIGIAN_KT'])) : ' (Đang chat)' }}
              </p>
            </div>
            <a href="{{ route('admin.chatbot') }}" class="btn btn-sm btn-outline">Đóng</a>
          </div>
          <div style="padding:16px;max-height:500px;overflow-y:auto;display:flex;flex-direction:column;gap:12px">
            @foreach ($messages as $msg) @php $isBot = $msg['NGUOI_GUI'] === 'Bot'; @endphp
            <div style="display:flex;{{ $isBot?'':'flex-direction:row-reverse' }};gap:10px;align-items:flex-end">
              <div style="width:32px;height:32px;border-radius:50%;background:{{ $isBot?'var(--blue-solid)':'var(--green-solid)' }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
                {{ $isBot ? '🤖' : '👤' }}
              </div>
              <div style="max-width:70%">
                <div style="background:{{ $isBot?'var(--bg-card)':'rgba(58,86,228,0.15)' }};border:1px solid {{ $isBot?'var(--border)':'rgba(58,86,228,0.3)' }};border-radius:{{ $isBot?'4px 12px 12px 12px':'12px 4px 12px 12px' }};padding:10px 14px;font-size:13px;color:var(--text-primary);line-height:1.5">
                  {{ nl2br(e($msg['NOI_DUNG'])) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;text-align:{{ $isBot?'left':'right' }}">
                  {{ date('H:i', strtotime($msg['THOIGIAN'])) }}
                </div>
              </div>
            </div>
            @endforeach
            @if (empty($messages))
            <div class="empty-state"><div class="empty-icon">{!! icon('message') !!}</div><p>Chưa có tin nhắn nào</p></div>
            @endif
          </div>
        </div>
        @endif
      </div>
@endsection
