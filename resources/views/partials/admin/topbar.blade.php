<header class="topbar">
    <button type="button" class="topbar-btn topbar-menu-btn" onclick="toggleSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>

    <div class="topbar-breadcrumb">
        <span>Hệ thống Nexus</span>
        <span class="sep">/</span>
        @foreach ($breadcrumb as $crumb)
            @if ($loop->last)
                <span class="current">{{ $crumb }}</span>
            @else
                <span>{{ $crumb }}</span>
                <span class="sep">/</span>
            @endif
        @endforeach
    </div>

    <div class="topbar-search">
        <span class="search-icon">{!! icon('search', 15) !!}</span>
        <input type="text" id="globalSearch" placeholder="Tìm kiếm sản phẩm, đơn hàng, khách hàng...">
    </div>

    <div class="topbar-actions">
        <x-theme-toggle />

        <div class="topbar-btn" onclick="toggleNotif()" title="Thông báo" id="notifBtn">
            {!! icon('bell', 18) !!}
            <span class="topbar-notif-dot" id="notifDot"></span>
        </div>

        <a href="{{ url('admin/cai-dat') }}" class="topbar-btn" title="Cài đặt">{!! icon('settings', 18) !!}</a>

        <div class="topbar-profile" onclick="window.location='{{ url('admin/ho-so') }}'">
            <div class="av">AD</div>
            <span>{{ auth()->user()->tenHienThi() }}</span>
            <span style="color:var(--text-muted);display:flex">{!! icon('chevron', 14) !!}</span>
        </div>
    </div>
</header>

<div id="notifPanel" style="position:fixed;top:68px;right:20px;width:320px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);z-index:200;display:none;box-shadow:var(--shadow)">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <strong style="font-size:14px">Thông báo</strong>
        <span onclick="document.getElementById('notifPanel').style.display='none'" style="cursor:pointer;color:var(--text-muted)">{!! icon('x', 16) !!}</span>
    </div>
    <div style="padding:8px 0;max-height:300px;overflow-y:auto" id="notifList">
        <div style="padding:12px 16px;font-size:13px;color:var(--text-muted);text-align:center">Đang tải...</div>
    </div>
</div>

@push('scripts')
<script>
function toggleNotif() {
    const p = document.getElementById('notifPanel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    if (p.style.display === 'block') loadNotifs();
}

function loadNotifs() {
    fetch(@js(route('admin.thongbao')), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notifList');
            list.replaceChildren();
            if (!data.length) {
                list.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted)">Không có thông báo mới</div>';
                return;
            }
            data.forEach(n => {
                const row = document.createElement('div');
                row.style.cssText = 'padding:10px 16px;border-bottom:1px solid var(--border)';
                const text = document.createElement('div');
                text.style.cssText = 'font-size:13px;color:var(--text-primary)';
                const strong = document.createElement('strong');
                strong.textContent = n.so_luong;
                text.append(strong, ' ' + n.noi_dung);
                const time = document.createElement('div');
                time.style.cssText = 'font-size:11px;color:var(--text-muted);margin-top:3px';
                time.textContent = n.thoi_gian;
                row.append(text, time);
                list.append(row);
            });
        }).catch(() => {});
}

document.getElementById('globalSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && this.value.trim()) {
        window.location = @js(route('admin.sanpham')) + '?q=' + encodeURIComponent(this.value.trim());
    }
});
</script>
@endpush
