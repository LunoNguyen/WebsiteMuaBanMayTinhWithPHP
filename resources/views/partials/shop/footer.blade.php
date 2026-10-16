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
                    <li>Thanh toán COD, tại cửa hàng, chuyển khoản, QR</li>
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
        @php($viTri = config('cuahang.vi_tri'))
        <section class="s-store" aria-labelledby="viTriCuaHang">
            <div class="s-store-info">
                <h4 id="viTriCuaHang">Vị trí cửa hàng</h4>
                <p class="s-store-ten">{!! icon('map-pin', 18) !!} {{ $viTri['ten'] }}</p>
                <p>{{ $viTri['dia_chi'] }}</p>
                <p>Nhận hàng và thanh toán trực tiếp tại cửa hàng.</p>
                <a class="btn btn-outline btn-sm" href="https://www.google.com/maps/dir/?api=1&amp;destination={{ rawurlencode($viTri['ban_do']) }}" target="_blank" rel="noopener">
                    {!! icon('map-pin', 15) !!} Chỉ đường
                </a>
            </div>
            <iframe class="s-store-map" title="Bản đồ: {{ $viTri['ten'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q={{ rawurlencode($viTri['ban_do']) }}&amp;hl=vi&amp;z=16&amp;output=embed"></iframe>
        </section>
        <div class="s-copy">© {{ now()->year }} NEXUS Computer Store</div>
    </div>
</footer>
