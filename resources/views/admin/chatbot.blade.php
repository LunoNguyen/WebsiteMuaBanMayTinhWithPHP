@extends('layouts.admin', ['title' => 'Lịch sử chatbot', 'breadcrumb' => ['Hệ thống', 'Chatbot']])

@section('content')


    <div class="page-header">
        <div class="page-header-left">
            <h1>Lịch sử chatbot</h1>
            <p>{{ formatNum($statsTotal['cnt']) }} phiên · {{ formatNum($statsToday['cnt']) }} hôm nay · {{ formatNum($statsActive['cnt']) }} đang chat</p>
        </div>
    </div>

      <div style="display:grid;grid-template-columns:{{ $selectedPhien ? '360px 1fr' : '1fr' }};gap:20px">
        <!-- Danh sách phiên -->
        <div class="card">
          <form method="GET" class="qt-toolbar">
            <x-qt.tim :value="$search" placeholder="Tìm tên hoặc email khách" />
          </form>
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
          @if ($total > 0)
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="phiên" />
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
                <div style="background:{{ $isBot?'var(--bg-card)':'rgba(58,86,228,0.15)' }};border:1px solid {{ $isBot?'var(--border)':'rgba(58,86,228,0.3)' }};border-radius:{{ $isBot?'16px 16px 16px 6px':'16px 16px 6px 16px' }};padding:9px 13px;font-size:13px;color:var(--text-primary);line-height:1.5">
                  {!! nl2br(e($msg['NOI_DUNG'])) !!}
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
