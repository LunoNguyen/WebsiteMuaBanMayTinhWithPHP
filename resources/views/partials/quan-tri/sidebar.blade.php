{{-- Sidebar chung cho mọi vai trò quản trị (Admin, NV bán hàng, NV kho); mỗi người chỉ thấy mục mình được vào.
     Biến: $menu (nhóm => mục) và $vaiTro từ View composer trong AppServiceProvider. --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route($vaiTro->trangChu()) }}" class="logo-text" title="Về trang chính">
            <x-logo :height="40" />
        </a>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</div>
        <div class="user-info">
            <strong>{{ auth()->user()->tenHienThi() }}</strong>
            <span>{{ $vaiTro->nhan() }}</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach ($menu as $nhom => $mucs)
            <div class="nav-section-label">{{ $nhom }}</div>
            @foreach ($mucs as $muc)
                @php($dangChon = request()->routeIs(...$muc['dang_chon']))
                <a href="{{ route($muc['route']) }}" @class(['nav-item', 'active' => $dangChon]) @if ($dangChon) aria-current="page" @endif>
                    <span class="nav-icon">{!! icon($muc['icon']) !!}</span> {{ $muc['ten'] }}
                    @isset($muc['dem'])
                        <span data-rt-vung="dem-{{ $muc['route'] }}" data-rt-khi="don sp" style="display:contents">
                            @if ($muc['dem'] > 0)
                                <span class="nav-badge">{{ $muc['dem'] }}</span>
                            @endif
                        </span>
                    @endisset
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="nav-item nav-logout">
                <span class="nav-icon">{!! icon('logout') !!}</span> Đăng xuất
            </button>
        </form>
    </div>
</aside>
<div class="sidebar-backdrop" onclick="toggleSidebar()"></div>
