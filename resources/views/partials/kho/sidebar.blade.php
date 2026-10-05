@php($taiKhoan = auth()->user())
<aside class="wsb">
    <div class="wlogo">
        <div class="wlogo-r">
            <a href="{{ route('kho.nhaphang') }}" class="wlogo-a" title="Về trang chính">
                <x-logo :height="40" />
                <div class="wls">Kho Vận &amp; Logistics</div>
            </a>
        </div>
    </div>

    <div class="wng">
        <div class="wnl">Nghiệp Vụ Vận Hành</div>
        <a href="{{ route('kho.nhaphang') }}" @class(['wni', 'active' => request()->routeIs('kho.nhaphang*')])>
            <span class="ni">{!! icon('package') !!}</span>
            <div>
                <div>Nhập hàng &amp; Kiểm đếm</div>
                <div style="font-size:10px;color:var(--wm)">Phiếu nhập &amp; Nhà cung cấp</div>
            </div>
            @if ($soPhieuChoKiemDem > 0)
                <span class="nbg">{{ $soPhieuChoKiemDem }}</span>
            @endif
        </a>
        <a href="{{ route('kho.donhang') }}" @class(['wni', 'active' => request()->routeIs('kho.donhang*')])>
            <span class="ni">{!! icon('truck') !!}</span>
            <div>
                <div>Đơn hàng cần xuất kho</div>
                <div style="font-size:10px;color:var(--wm)">Soạn hàng &amp; Điều phối xuất</div>
            </div>
            <span data-rt-vung="so-don-xuat" data-rt-khi="don" style="display:contents">
            @if ($soDonChoXuatKho > 0)
                <span class="nbg" style="background:var(--green-solid)">{{ $soDonChoXuatKho }}</span>
            @endif
            </span>
        </a>
    </div>

    <div style="margin-top:auto;padding:9px;">
        <div class="wrb">
            <div><span class="rdot"></span><span style="font-size:12px;font-weight:700;color:var(--green)">● Online</span></div>
            <div style="font-size:10px;color:var(--wm);margin-top:2px">Nhân viên kho — Vận hành kho trung tâm</div>
            <div style="font-size:10px;color:var(--wm);margin-top:5px;font-family:monospace">
                {{ $taiKhoan->tenHienThi() }} &bull; {{ $taiKhoan->MANV ?? '—' }}
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="wni" style="color:var(--red);margin-top:5px;width:100%;background:none;border:none;font-family:inherit;cursor:pointer">
                <span class="ni">{!! icon('logout') !!}</span> Đăng xuất
            </button>
        </form>
    </div>
</aside>
<div class="w-backdrop" onclick="toggleKhoSidebar()"></div>
