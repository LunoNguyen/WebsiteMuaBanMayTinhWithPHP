<aside class="s-card s-side">
    <a href="{{ route('taikhoan.edit') }}" @class(['active' => request()->routeIs('taikhoan.*')])>{!! icon('user', 17) !!} Thông tin cá nhân</a>
    <a href="{{ route('donhang.index') }}" @class(['active' => request()->routeIs('donhang.*')])>{!! icon('receipt', 17) !!} Đơn hàng của tôi</a>
    <a href="{{ route('giohang.index') }}">{!! icon('cart', 17) !!} Giỏ hàng</a>
    <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="btn btn-ghost btn-block" style="justify-content:flex-start;padding:0 12px;font-weight:400">{!! icon('logout', 17) !!} Đăng xuất</button>
    </form>
</aside>
