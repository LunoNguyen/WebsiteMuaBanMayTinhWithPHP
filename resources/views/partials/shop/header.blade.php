@php($khach = auth()->user()?->LOAI_TAIKHOAN === 'KhachHang' ? auth()->user() : null)
<header class="s-header">
    <div class="wrap s-header-main">
        <a href="{{ route('home') }}" class="s-logo" aria-label="NEXUS - Trang chủ"><x-logo :height="44" /></a>

        <form class="s-search" action="{{ route('sanpham.index') }}" method="GET" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Bạn cần tìm laptop, PC, linh kiện...?" aria-label="Tìm sản phẩm">
            <button type="submit" aria-label="Tìm">{!! icon('search', 18) !!}</button>
        </form>

        <div class="s-actions">
            <x-theme-toggle />

            @if ($khach)
                <div class="s-menu" data-menu>
                    <button type="button" class="s-act" data-menu-btn aria-haspopup="true" aria-expanded="false">
                        {!! icon('user', 20) !!}
                        <span class="lbl"><small>Xin chào</small>{{ \Illuminate\Support\Str::limit($khach->tenHienThi(), 18) }}</span>
                    </button>
                    <div class="s-menu-pop">
                        <a href="{{ route('taikhoan.edit') }}">{!! icon('user', 16) !!} Thông tin tài khoản</a>
                        <a href="{{ route('donhang.index') }}">{!! icon('receipt', 16) !!} Đơn hàng của tôi</a>
                        <hr>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" style="color:var(--red)">{!! icon('logout', 16) !!} Đăng xuất</button>
                        </form>
                    </div>
                </div>
            @elseif (auth()->check())
                {{-- Nhân viên / quản trị đang xem cửa hàng --}}
                <a href="{{ route(auth()->user()->vaiTro()?->trangChu() ?? 'login') }}" class="s-act">{!! icon('dashboard', 20) !!}<span class="lbl">Trang quản lý</span></a>
            @else
                {{-- Đang ở trang đăng nhập / đăng ký thì giữ nguyên trang cần quay lại, không lồng thêm --}}
                <a href="{{ route('login', array_filter(['tiep' => request()->routeIs('login', 'register') ? request('tiep') : request()->getRequestUri()])) }}"
                   @class(['s-act', 'on' => request()->routeIs('login', 'register')])>
                    {!! icon('user', 20) !!}
                    <span class="lbl"><small>Đăng nhập</small>Đăng ký</span>
                </a>
            @endif

            <a href="{{ $khach ? route('giohang.index') : route('login', ['tiep' => '/gio-hang']) }}" class="s-act" aria-label="Giỏ hàng">
                {!! icon('cart', 22) !!}
                @if ($soLuongGio > 0)
                    <span class="s-badge">{{ $soLuongGio > 99 ? '99+' : $soLuongGio }}</span>
                @endif
                <span class="lbl">Giỏ hàng</span>
            </a>
        </div>
    </div>

    <nav class="s-nav" aria-label="Danh mục">
        <div class="wrap">
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Trang chủ</a>
            @foreach ($danhMucNav as $loai)
                <a href="{{ route('sanpham.index', ['loai' => [$loai->MALOAI]]) }}"
                   @class(['active' => request()->routeIs('sanpham.index') && request('loai') === [$loai->MALOAI]])>{{ $loai->TENLOAI }}</a>
            @endforeach
        </div>
    </nav>
</header>
