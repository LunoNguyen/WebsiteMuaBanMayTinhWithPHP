<aside class="nvb-sb" style="width:220px;min-width:220px;background:var(--bg-card);border-right:1px solid var(--border);
  display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;font-family:'Inter',sans-serif;">

  <!-- Logo -->
  <div style="padding:15px 13px 11px;border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:center;gap:9px;">
      <a href="{{ route('banhang.donhang') }}" style="display:flex;flex-direction:column;gap:4px;text-decoration:none" title="Về trang chính">
        <x-logo :height="40" />
        <div style="font-size:10px;color:var(--text-muted);">Bán hàng &amp; CRM</div>
      </a>
    </div>
  </div>

  <!-- Nav -->
  <div style="padding:11px 9px 4px;">
    <div style="font-size:10px;color:var(--text-muted);padding:0 7px;margin-bottom:4px;">Nghiệp Vụ</div>

    <a href="{{ route('banhang.donhang') }}"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:{{ request()->routeIs('banhang.donhang') ? 'var(--green)' : 'var(--text-muted)' }};font-size:12.5px;font-weight:{{ request()->routeIs('banhang.donhang') ? '600' : '500' }};
              text-decoration:none;background:{{ request()->routeIs('banhang.donhang') ? 'rgba(21,128,61,.12)' : '' }};">
      <span style="display:flex">{!! icon('cart') !!}</span>
      <div>
        <div>Quản lý Đơn hàng</div>
        <div style="font-size:10px;color:var(--text-muted)">Xử lý &amp; Theo dõi đơn</div>
      </div>
      <span data-rt-vung="so-don-cho" data-rt-khi="don" style="display:contents">
      @if ($soDonChoXacNhan > 0)
        <span style="margin-left:auto;background:var(--red-solid);color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;">{{ $soDonChoXacNhan }}</span>
      @endif
      </span>
    </a>

    <a href="{{ route('banhang.khachhang') }}"
       style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;
              color:{{ request()->routeIs('banhang.khachhang') ? 'var(--green)' : 'var(--text-muted)' }};font-size:12.5px;font-weight:{{ request()->routeIs('banhang.khachhang') ? '600' : '500' }};
              text-decoration:none;background:{{ request()->routeIs('banhang.khachhang') ? 'rgba(21,128,61,.12)' : '' }};">
      <span style="display:flex">{!! icon('user') !!}</span>
      <div>
        <div>Quản lý Khách hàng</div>
        <div style="font-size:10px;color:var(--text-muted)">Thông tin &amp; Lịch sử mua</div>
      </div>
    </a>
  </div>

  <!-- User & Logout -->
  <div style="margin-top:auto;padding:9px;">
    <div style="margin:9px;padding:9px 11px;border-radius:8px;background:rgba(21,128,61,.08);border:1px solid rgba(21,128,61,.2);">
      <div>
        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green-solid);margin-right:4px;animation:pl 2s infinite;"></span>
        <span style="font-size:12px;font-weight:700;color:var(--green)">● Online</span>
      </div>
      <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Nhân viên Bán hàng</div>
      <div style="font-size:10px;color:var(--text-muted);margin-top:5px;font-family:monospace;">
        {{ auth()->user()->tenHienThi() }} &bull; {{ auth()->user()->MANV ?? '—' }}
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin:0">
      @csrf
      <button type="submit" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--red);font-size:12.5px;margin-top:5px;width:100%;background:none;border:none;font-family:inherit;cursor:pointer">
        <span style="display:flex">{!! icon('logout') !!}</span> Đăng xuất
      </button>
    </form>
  </div>
</aside>
<div class="w-backdrop" onclick="toggleNvbSidebar()"></div>
<style>@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}</style>
