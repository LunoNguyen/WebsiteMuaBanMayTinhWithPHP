@php($taiKhoan = auth()->user())
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('admin.dashboard') }}" class="logo-text" title="Về trang tổng quan">
            <x-logo :height="40" />
            <span>ADMIN PORTAL</span>
        </a>
        <span class="sidebar-badge">v2.0</span>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">AD</div>
        <div class="user-info">
            <strong>{{ $taiKhoan->tenHienThi() }}</strong>
            <span>● Online</span>
        </div>
        <div class="user-status-dot"></div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Tổng Quan</div>
        <a href="{{ route('admin.dashboard') }}" @class(['nav-item', 'active' => request()->routeIs('admin.dashboard')])>
            <span class="nav-icon">{!! icon('dashboard') !!}</span> Tổng quan
        </a>

        <div class="nav-section-label">Quản Lý</div>
        <a href="{{ route('admin.nhanvien') }}" @class(['nav-item', 'active' => request()->routeIs('admin.nhanvien*')])>
            <span class="nav-icon">{!! icon('users') !!}</span> Quản lý Nhân viên
        </a>
        <a href="{{ route('admin.taikhoan') }}" @class(['nav-item', 'active' => request()->routeIs('admin.taikhoan*')])>
            <span class="nav-icon">{!! icon('key') !!}</span> Quản lý Tài khoản
        </a>
        <a href="{{ route('admin.sanpham') }}" @class(['nav-item', 'active' => request()->routeIs('admin.sanpham*')])>
            <span class="nav-icon">{!! icon('laptop') !!}</span> Quản lý Sản phẩm
        </a>
        <a href="{{ route('admin.nhaphang') }}" @class(['nav-item', 'active' => request()->routeIs('admin.nhaphang*')])>
            <span class="nav-icon">{!! icon('package') !!}</span> Quản lý Nhập hàng
        </a>
        <a href="{{ route('admin.khuyenmai') }}" @class(['nav-item', 'active' => request()->routeIs('admin.khuyenmai*')])>
            <span class="nav-icon">{!! icon('tag') !!}</span> Voucher &amp; Khuyến mãi
        </a>
        <a href="{{ route('admin.donhang') }}" @class(['nav-item', 'active' => request()->routeIs('admin.donhang*')])>
            <span class="nav-icon">{!! icon('cart') !!}</span> Quản lý Đơn hàng
            @if ($soDonChoXacNhan > 0)
                <span class="nav-badge">{{ $soDonChoXacNhan }}</span>
            @endif
        </a>
        <a href="{{ route('admin.khachhang') }}" @class(['nav-item', 'active' => request()->routeIs('admin.khachhang*')])>
            <span class="nav-icon">{!! icon('user') !!}</span> Quản lý Khách hàng
        </a>

        <div class="nav-section-label">Hệ Thống</div>
        <a href="{{ route('admin.chatbot') }}" @class(['nav-item', 'active' => request()->routeIs('admin.chatbot*')])>
            <span class="nav-icon">{!! icon('bot') !!}</span> Lịch sử Chatbot
        </a>
        <a href="{{ route('admin.baocao') }}" @class(['nav-item', 'active' => request()->routeIs('admin.baocao*')])>
            <span class="nav-icon">{!! icon('chart') !!}</span> Báo cáo & Thống kê
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="nav-item" style="color:var(--red);width:100%;background:none;border:none;font-family:inherit;text-align:left;cursor:pointer">
                <span class="nav-icon">{!! icon('logout') !!}</span> Đăng xuất
            </button>
        </form>
    </div>
</aside>
<div class="sidebar-backdrop" onclick="toggleSidebar()"></div>
