{{-- Topbar chung khu quản trị. Thông báo, Danh mục, Hồ sơ là trang của quản trị viên nên chỉ hiện với Admin. --}}
@php
    $laAdmin = auth()->user()->vaiTro() === \App\Enums\VaiTro::Admin;
    $timO = match (auth()->user()->vaiTro()) {
        \App\Enums\VaiTro::NhanVienBan => ['banhang.donhang', 'Tìm mã đơn, tên khách...'],
        \App\Enums\VaiTro::NhanVienKho => ['kho.donhang', 'Tìm mã đơn, tên khách...'],
        default => ['admin.sanpham', 'Tìm sản phẩm theo tên hoặc mã...'],
    };
@endphp
<header class="topbar">
    <button type="button" class="topbar-btn topbar-menu-btn" onclick="toggleSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>

    <div class="topbar-breadcrumb">
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
        <input type="text" id="globalSearch" placeholder="{{ $timO[1] }}">
    </div>

    <div class="topbar-actions">
        <x-theme-toggle />

        @if ($laAdmin)
        <div class="topbar-btn" onclick="toggleNotif()" title="Thông báo" id="notifBtn">
            {!! icon('bell', 18) !!}
            <span class="topbar-notif-dot" id="notifDot"></span>
        </div>

        <a href="{{ route('admin.danhmuc') }}" class="topbar-btn" title="Danh mục">{!! icon('settings', 18) !!}</a>
        @endif

        <div class="tb-menu" data-tb-menu>
            <button type="button" class="topbar-profile" aria-haspopup="menu" aria-expanded="false" aria-controls="tbMenuTaiKhoan">
                <span class="av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</span>
                <span>{{ auth()->user()->tenHienThi() }}</span>
                <span class="tb-mui" aria-hidden="true">{!! icon('chevron', 14) !!}</span>
            </button>
            <div class="tb-pop" id="tbMenuTaiKhoan" role="menu" hidden>
                <div class="tb-pop-dau">
                    <strong>{{ auth()->user()->tenHienThi() }}</strong>
                    <span>{{ auth()->user()->EMAIL_TK }}</span>
                </div>
                @if ($laAdmin)
                    <a href="{{ route('admin.hoso') }}" role="menuitem">{!! icon('user', 16) !!} Hồ sơ của tôi</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" role="menuitem" class="tb-pop-thoat">{!! icon('logout', 16) !!} Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
</header>

@if ($laAdmin)
<div id="notifPanel" style="position:fixed;top:68px;right:20px;width:320px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);z-index:200;display:none;box-shadow:var(--shadow)">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <strong style="font-size:14px">Thông báo</strong>
        <span onclick="document.getElementById('notifPanel').style.display='none'" style="cursor:pointer;color:var(--text-muted)">{!! icon('x', 16) !!}</span>
    </div>
    <div style="padding:8px 0;max-height:300px;overflow-y:auto" id="notifList">
        <div style="padding:12px 16px;font-size:13px;color:var(--text-muted);text-align:center">Đang tải...</div>
    </div>
</div>

@endif

@push('scripts')
<script>
@if ($laAdmin)
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

@endif
document.getElementById('globalSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && this.value.trim()) {
        window.location = @js(route($timO[0])) + '?q=' + encodeURIComponent(this.value.trim());
    }
});
</script>
@endpush
