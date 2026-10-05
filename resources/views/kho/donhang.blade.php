@extends('layouts.kho', ['title' => 'Đơn hàng cần xuất kho'])

@push('styles')
<style>
/* Reuse WMS styles */
:root{--wc:var(--bg-card);--wc2:var(--bg-card-hover);--wb:var(--border);--wt:var(--text-primary);--wm:var(--text-muted);--wr:10px;}
*{box-sizing:border-box;}
body{background:var(--bg-main);font-family:'Inter',sans-serif;}
.wsh{display:flex;min-height:100vh;}
.wsb{width:218px;min-width:218px;background:var(--wc);border-right:1px solid var(--wb);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.wlogo{padding:15px 13px 11px;border-bottom:1px solid var(--wb);}
.wlogo-r{display:flex;align-items:center;gap:9px;}
.wli{width:33px;height:33px;border-radius:8px;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;}
.wln{font-size:14px;font-weight:800;color:var(--wt);}.wls{font-size:10px;color:var(--wm);}
.wng{padding:11px 9px 4px;}.wnl{font-size:10px;color:var(--wm);padding:0 7px;margin-bottom:4px;}
.wni{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;color:var(--wm);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .15s;}
.wni:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wni.active{background:rgba(58,86,228,.15);color:var(--blue);font-weight:600;}
.wni .ni{font-size:14px;flex-shrink:0;}
.nbg{margin-left:auto;background:var(--red-solid);color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;}
.wrb{margin:9px;padding:9px 11px;border-radius:8px;background:rgba(21,128,61,.08);border:1px solid rgba(21,128,61,.2);}
.rdot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green-solid);margin-right:4px;animation:pl 2s infinite;}
@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}
.wmn{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--bg-main);}
.wct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:14px;}
.wph{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.wph h1{font-size:20px;font-weight:800;color:var(--wt);margin:0;}
.wph p{font-size:12px;color:var(--wm);margin:4px 0 0;}
.wkg{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.wk{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:14px 16px;display:flex;align-items:center;gap:12px;}
.wki{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.wkv{font-size:26px;font-weight:900;line-height:1;}.wkl{font-size:10px;color:var(--wm);}
.wfl{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:10px 14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.wfi{flex:1;min-width:180px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 9px;}
.wfi input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wfi input::placeholder{color:var(--wm);}
.wse{background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 8px;color:var(--wt);font-size:12px;outline:none;cursor:pointer;}
.wtw{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);overflow:hidden;}
.wth{padding:10px 14px;border-bottom:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wth h3{font-size:13px;font-weight:700;color:var(--wt);margin:0;}
.wcnt{background:rgba(58,86,228,.15);color:var(--blue);border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.wt{width:100%;border-collapse:collapse;}
table.wt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--wm);border-bottom:1px solid var(--wb);text-align:left;}
table.wt td{padding:10px 12px;font-size:13px;color:var(--wt);border-bottom:1px solid var(--border);vertical-align:middle;}
table.wt tr:last-child td{border-bottom:none;}
table.wt tr:hover td{background:rgba(0,0,0,.04);}
.wcode{font-family:monospace;font-weight:700;color:var(--blue);}
.wpil{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.wbn{padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;transition:all .15s;text-decoration:none;white-space:nowrap;}
.wb-pri{background:var(--blue-solid);color:#fff;}
.wb-suc{background:var(--green-solid);color:#fff;}
.wb-out{background:transparent;border:1px solid var(--wb)!important;color:var(--wm);}
.wb-out:hover{border-color:var(--blue)!important;color:var(--wt);}
.btn-ic{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;text-decoration:none;transition:all .15s;}
.btn-ic:hover{background:rgba(0,0,0,.04);}
.alert-msg{padding:11px 16px;border-radius:8px;font-size:13px;background:rgba(21,128,61,.1);border:1px solid rgba(21,128,61,.2);color:var(--green);}
.wni.active{background:rgba(58,86,228,.15);color:var(--blue);font-weight:600;}
/* Topbar minimal */
.kho-topbar{height:52px;background:var(--wc);border-bottom:1px solid var(--wb);display:flex;align-items:center;padding:0 18px;gap:12px;flex-shrink:0;}
.kho-topbar h2{font-size:14px;font-weight:700;color:var(--wt);margin:0;}
.kto-user{display:flex;align-items:center;gap:8px;margin-left:auto;}
.kto-av{width:30px;height:30px;border-radius:50%;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;}
.wpg{padding:10px 14px;border-top:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wpa{display:flex;gap:3px;}
.wpl2{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--wm);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.wpl2:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wpl2.active{background:rgba(58,86,228,.2);border-color:rgba(58,86,228,.4);color:var(--blue);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--wb);border-radius:2px;}
.cok{background:rgba(21,128,61,.14);color:var(--green);border:1px solid rgba(21,128,61,.3);}
.csc{background:rgba(58,86,228,.14);color:var(--blue);border:1px solid rgba(58,86,228,.3);}
.cwarn{background:rgba(180,83,9,.14);color:var(--orange);border:1px solid rgba(180,83,9,.3);}
</style>
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
    <div class="wkg">
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
    <div class="wtw">
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
                <a href="{{ url('kho/xem-chitiet') }}?mahd={{ $dh['MAHD'] }}" class="btn-ic" title="Xem chi tiết">{!! icon('eye', 15) !!}</a>
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
