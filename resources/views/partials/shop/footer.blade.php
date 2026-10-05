<footer class="s-footer">
    <div class="wrap">
        <div class="s-footer-grid">
            <div>
                <x-logo :height="44" />
                <p style="margin-top:12px;max-width:320px">NEXUS Computer Store - laptop, PC, màn hình và linh kiện chính hãng, giao hàng toàn quốc.</p>
            </div>
            <div>
                <h4>Sản phẩm</h4>
                <ul>
                    @foreach ($danhMucNav->take(5) as $loai)
                        <li><a href="{{ route('sanpham.index', ['loai' => [$loai->MALOAI]]) }}">{{ $loai->TENLOAI }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4>Hỗ trợ khách hàng</h4>
                <ul>
                    <li>Giao hàng toàn quốc</li>
                    <li>Bảo hành chính hãng</li>
                    <li>Đổi mới trong 7 ngày nếu lỗi nhà sản xuất</li>
                    <li>Thanh toán COD, chuyển khoản, QR</li>
                </ul>
            </div>
            <div>
                <h4>Tài khoản</h4>
                <ul>
                    @if (auth()->user()?->LOAI_TAIKHOAN === 'KhachHang')
                        <li><a href="{{ route('donhang.index') }}">Đơn hàng của tôi</a></li>
                        <li><a href="{{ route('taikhoan.edit') }}">Thông tin cá nhân</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}">Đăng ký</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="s-copy">© {{ now()->year }} NEXUS Computer Store</div>
    </div>
</footer>
