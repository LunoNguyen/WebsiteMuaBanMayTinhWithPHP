@extends('layouts.kho', ['title' => 'Đơn hàng cần xuất kho'])

@push('styles')
@include('partials.kho.style-trang')
@endpush

@section('content')
<div class="wsh">
@include('partials.kho.sidebar')
<div class="wmn">

  <!-- Topbar -->
  <div class="kho-topbar">
    <button type="button" class="w-menu-btn" onclick="toggleKhoSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>
    <span style="display:flex;color:var(--wm)">{!! icon('truck', 18) !!}</span>
    <h2>Đơn hàng cần xuất kho</h2>
    <div class="kto-user">
      <x-theme-toggle />
      <div style="text-align:right">
        <div style="font-size:12px;font-weight:700;color:var(--wt)">{{ auth()->user()->tenHienThi() }}</div>
        <div style="font-size:10px;color:var(--wm)">Nhân viên Kho &bull; {{ (auth()->user()->MANV ?? '—') }}</div>
      </div>
      <div class="kto-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(),0,2)) }}</div>
    </div>
  </div>

  <div class="wct">
    @if (session('thong_bao'))
    <div class="alert-msg">{!! icon('check') !!} {{ session('thong_bao') }}</div>
    @endif

    <!-- Page Header -->
    <div class="wph">
      <div>
        <h1>Đơn hàng cần Xuất kho &amp; Bàn giao</h1>
        <p>Soạn hàng theo đơn đã xác nhận · Kiểm tra trước khi giao vận chuyển</p>
      </div>
    </div>

    <!-- KPI -->
    <div class="wkg" data-rt-vung="kpi" data-rt-khi="don">
      <div class="wk">
        <div class="wki" style="background:rgba(180,83,9,.12)">{!! icon('clipboard') !!}</div>
        <div>
          <div class="wkl">CHỜ XUẤT KHO</div>
          <div class="wkv" style="color:var(--orange)">{{ $kpiCho['c']??0 }}</div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn hàng chờ soạn</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(58,86,228,.12)">{!! icon('truck') !!}</div>
        <div>
          <div class="wkl">ĐANG VẬN CHUYỂN</div>
          <div class="wkv" style="color:var(--blue)">{{ $kpiDangGiao['c']??0 }}</div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn đang trên đường</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(21,128,61,.1)">{!! icon('check') !!}</div>
        <div>
          <div class="wkl">GIAO THÀNH CÔNG HÔM NAY</div>
          <div class="wkv" style="color:var(--green)">{{ $kpiHomNay['c']??0 }}</div>
          <div style="font-size:10px;color:var(--wm);margin-top:2px">đơn đã hoàn tất</div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="wfl">
      <div class="wfi">
        <span style="color:var(--wm)">{!! icon('search') !!}</span>
        <input type="text" name="q" value="{{ $search }}" placeholder="Tìm mã đơn, tên khách hàng..."/>
      </div>
      <select name="trangthai" class="wse" onchange="this.form.submit()">
        <option value="">Tất cả trạng thái kho</option>
        <option value="DaXacNhan"  {{ $trangthai==='DaXacNhan' ?'selected':'' }}>⏳ Chờ xuất kho</option>
        <option value="DangGiao"   {{ $trangthai==='DangGiao'  ?'selected':'' }}>🚚 Đang giao</option>
        <option value="DaGiao"     {{ $trangthai==='DaGiao'    ?'selected':'' }}>✅ Đã giao</option>
      </select>
      <button type="submit" class="wbn wb-out" style="padding:6px 12px">Lọc</button>
      <a href="{{ route('kho.donhang') }}" class="wbn wb-out" style="padding:6px 12px">↩ Reset</a>
    </form>

    <!-- Table -->
    <div class="wtw" data-rt-vung="ds-don" data-rt-khi="don">
      <div class="wth">
        <h3>Danh sách Đơn hàng cần Xử lý (Kho)</h3>
        <span class="wcnt">{{ $total }} đơn</span>
      </div>
      <div class="wt-scroll">
      <table class="wt">
        <thead>
          <tr>
            <th>MÃ ĐƠN</th>
            <th>KHÁCH HÀNG</th>
            <th>SẢN PHẨM</th>
            <th>TỔNG TIỀN</th>
            <th>NGÀY ĐẶT</th>
            <th>ĐỊA CHỈ GIAO</th>
            <th>TRẠNG THÁI</th>
            <th>THAO TÁC KHO</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($donhang as $dh) @php $st = $stMap[$dh['TRANGTHAI']] ?? ['var(--text-secondary)','—','']; @endphp
          <tr>
            <td><span class="wcode">{{ $dh['MAHD'] }}</span></td>
            <td>
              <div style="font-weight:600;font-size:12px">{{ $dh['TENKH']??$dh['TEN_NGUOINHAN']??'—' }}</div>
              <div style="font-size:10px;color:var(--wm)">{{ $dh['SDT_KH']??$dh['SDT_NGUOINHAN']??'' }}</div>
            </td>
            <td style="text-align:center">
              <div style="font-weight:700">{{ $dh['so_sp']??0 }} SP</div>
              <div style="font-size:10px;color:var(--wm)">{{ $dh['tong_sl']??0 }} chiếc</div>
            </td>
            <td style="font-weight:700">{{ formatVND($dh['TONGTIEN_HD']??0) }}</td>
            <td style="font-size:11px">{{ $dh['NGAYLAP']?date('d/m/Y',strtotime($dh['NGAYLAP'])):'—' }}</td>
            <td style="font-size:11px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              {{ $dh['DIACHI_GIAO']??$dh['DIACHI_KH']??'—' }}
            </td>
            <td>
              <span class="wpil {{ $st[2] }}"
                style="background:color-mix(in srgb,{{ $st[0] }} 12%,transparent);color:{{ $st[0] }};border:1px solid color-mix(in srgb,{{ $st[0] }} 30%,transparent)">
                {{ $st[1] }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:5px;align-items:center">
                @if ($dh['TRANGTHAI']==='DaXacNhan')
                <x-nut-hanh-dong :action="route('kho.donhang.xuat-kho', $dh['MAHD'])" method="PATCH"
                   :confirm="'Xác nhận xuất kho đơn '.$dh['MAHD'].'?'"
                   class="wbn wb-suc" style="padding:5px 10px;font-size:11px">
                  Xuất kho
                </x-nut-hanh-dong>
                @elseif ($dh['TRANGTHAI']==='DangGiao')
                <span style="font-size:11px;color:var(--wm)">Đang vận chuyển</span>
                @else
                <span style="font-size:11px;color:var(--green)">Hoàn tất</span>
                @endif
                <a href="{{ route('kho.donhang.show', $dh['MAHD']) }}" class="btn-ic" title="Xem chi tiết">{!! icon('eye', 15) !!}</a>
              </div>
            </td>
          </tr>
          @endforeach
          @if (empty($donhang))
          <tr><td colspan="8">
            <div style="padding:32px;text-align:center;color:var(--wm)">
              <div style="font-size:36px;opacity:.3">{!! icon('truck') !!}</div>
              <p style="font-size:13px;margin:8px 0 0">Không có đơn hàng cần xử lý</p>
            </div>
          </td></tr>
          @endif
        </tbody>
      </table>
      </div>

      @if ($pages>1)
      <div class="wpg">
        <span style="font-size:11px;color:var(--wm)">
          Hiển thị {{ ($page-1)*$perPage+1 }}–{{ min($page*$perPage,$total) }} trên {{ $total }} đơn
        </span>
        <div class="wpa">
          @if ($page>1)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}" class="wpl2">‹</a>@endif
          @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
          <a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$p])) }}" class="wpl2 {{ $p===$page?'active':'' }}">{{ $p }}</a>
          @endfor
          @if ($page<$pages)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}" class="wpl2">›</a>@endif
        </div>
      </div>
      @endif
    </div>
  </div><!-- /wct -->
</div><!-- /wmn -->
</div><!-- /wsh -->
@endsection
