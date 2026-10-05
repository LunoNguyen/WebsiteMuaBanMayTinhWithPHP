@extends('layouts.banhang', ['title' => 'Quản lý Đơn hàng'])

@push('styles')
<style>
:root{--bg:var(--bg-main);--card:var(--bg-card);--card2:var(--bg-card-hover);--bd:var(--border);--tx:var(--text-primary);--mt:var(--text-muted);--gr:var(--green);--bl:var(--blue-solid);}
*{box-sizing:border-box;}
body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--tx);}
.shell{display:flex;min-height:100vh;}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
/* Topbar */
.topbar{height:54px;background:var(--card);border-bottom:1px solid var(--bd);
  display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.topbar h2{font-size:15px;font-weight:700;color:var(--tx);margin:0;}
.tb-search{flex:1;max-width:350px;}
.tb-search form{display:flex;gap:6px;}
.tb-search input{flex:1;background:var(--card2);border:1px solid var(--bd);border-radius:8px;
  padding:7px 12px;color:var(--tx);font-size:12px;outline:none;}
.tb-search input::placeholder{color:var(--mt);}
.tb-search button{background:var(--bl);color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer;}
.tb-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.tb-av{width:32px;height:32px;border-radius:50%;background:var(--green-solid);
  display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;}
/* Content */
.ct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:16px;}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
.ph h1{font-size:20px;font-weight:800;color:var(--tx);margin:0;}
.ph p{font-size:12px;color:var(--mt);margin:4px 0 0;}
/* KPI */
.kgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.kcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:14px 16px;
  display:flex;align-items:center;gap:12px;}
.kico{width:42px;height:42px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.kval{font-size:24px;font-weight:900;line-height:1;}
.klbl{font-size:10px;color:var(--mt);margin-top:1px;}
/* Filter */
.fbar{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:10px 14px;
  display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.fbar input,.fbar select{background:var(--card2);border:1px solid var(--bd);border-radius:7px;
  padding:7px 10px;color:var(--tx);font-size:12px;outline:none;color-scheme:dark;}
.fbar input::placeholder{color:var(--mt);}
.btn{padding:7px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;
  display:flex;align-items:center;gap:5px;border:none;text-decoration:none;transition:all .15s;white-space:nowrap;}
.btn-pri{background:var(--green-solid);color:#fff;}
.btn-out{background:transparent;border:1px solid var(--bd)!important;color:var(--mt);}
.btn-out:hover{border-color:var(--bl)!important;color:var(--tx);}
.btn-sm-blue{background:rgba(58,86,228,.15);color:var(--blue);border:1px solid rgba(58,86,228,.3)!important;padding:5px 10px;font-size:11px;}
.btn-sm-red{background:rgba(200,30,30,.12);color:var(--red);border:1px solid rgba(200,30,30,.3)!important;padding:5px 10px;font-size:11px;}
/* Table */
.tcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;overflow:hidden;}
.thead-row{padding:11px 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.thead-row h3{font-size:13px;font-weight:700;color:var(--tx);margin:0;}
.cnt{background:rgba(21,128,61,.15);color:var(--green);border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--mt);border-bottom:1px solid var(--bd);text-align:left;}
table.dt td{padding:9px 12px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--border);vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:rgba(0,0,0,.04);}
.mono{font-family:monospace;font-weight:700;color:var(--gr);}
.pil{display:inline-flex;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
/* Alert */
.amsg{padding:11px 16px;border-radius:8px;font-size:13px;margin-bottom:2px;}
.amsg.ok{background:rgba(21,128,61,.1);border:1px solid rgba(21,128,61,.2);color:var(--green);}
.amsg.info{background:rgba(58,86,228,.1);border:1px solid rgba(58,86,228,.2);color:var(--blue);}
/* Pagination */
.pg{padding:10px 14px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.pga{display:flex;gap:3px;}
.pga a{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--mt);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.pga a:hover{background:rgba(0,0,0,.04);color:var(--tx);}
.pga a.active{background:rgba(21,128,61,.2);border-color:rgba(21,128,61,.4);color:var(--gr);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px;}
@media(max-width:1100px){.kgrid{grid-template-columns:repeat(2,1fr);}}
</style>
@endpush

@section('content')
<div class="shell">
  @include('partials.banhang.sidebar')
  <div class="main">

    <!-- Topbar -->
    <div class="topbar">
      <button type="button" class="w-menu-btn" onclick="toggleNvbSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>
      <span style="display:flex;color:var(--mt)">{!! icon('cart', 18) !!}</span>
      <h2>Quản lý Đơn hàng</h2>
      <div class="tb-search">
        <form method="GET">
          <input type="text" name="q" value="{{ $search }}" placeholder="Tìm mã đơn, khách hàng..."/>
          <button type="submit" aria-label="Tìm" style="display:flex;align-items:center">{!! icon('search', 14) !!}</button>
        </form>
      </div>
      <div class="tb-right">
        <x-theme-toggle />
        <div style="text-align:right">
          <div style="font-size:12px;font-weight:700;color:var(--tx)">{{ auth()->user()->tenHienThi() }}</div>
          <div style="font-size:10px;color:var(--mt)">NV Bán hàng &bull; {{ (auth()->user()->MANV ?? '—') }}</div>
        </div>
        <div class="tb-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(),0,2)) }}</div>
      </div>
    </div>

    <div class="ct">
      @if (session('thong_bao'))
      <div class="amsg {{ session('loai') === 'info' ? 'info' : 'ok' }}">
        {!! icon(session('loai') === 'info' ? 'bulb' : 'check') !!} {{ session('thong_bao') }}
      </div>
      @endif

      <!-- Page header -->
      <div class="ph">
        <div>
          <h1>Quản lý Đơn hàng</h1>
          <p>Xác nhận &amp; Cập nhật trạng thái đơn hàng của khách</p>
        </div>
      </div>

      <!-- KPI -->
      <div class="kgrid">
        <div class="kcard">
          <div class="kico" style="background:rgba(180,83,9,.12)">{!! icon('clock') !!}</div>
          <div>
            <div class="klbl">CHỜ XÁC NHẬN</div>
            <div class="kval" style="color:var(--orange)">{{ $kpiCho['c']??0 }}</div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">cần xử lý ngay</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(109,40,217,.12)">{!! icon('truck') !!}</div>
          <div>
            <div class="klbl">ĐANG VẬN CHUYỂN</div>
            <div class="kval" style="color:var(--purple)">{{ $kpiGiao['c']??0 }}</div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn trên đường</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(21,128,61,.1)">{!! icon('check') !!}</div>
          <div>
            <div class="klbl">HOÀN THÀNH THÁNG NÀY</div>
            <div class="kval" style="color:var(--gr)">{{ $kpiHT['c']??0 }}</div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn thành công</div>
          </div>
        </div>
        <div class="kcard">
          <div class="kico" style="background:rgba(58,86,228,.1)">{!! icon('wallet') !!}</div>
          <div>
            <div class="klbl">DOANH THU THÁNG</div>
            <div style="font-size:14px;font-weight:900;color:var(--blue);margin-top:2px">{{ formatVND($kpiDT['t']??0) }}</div>
            <div style="font-size:10px;color:var(--mt);margin-top:2px">đơn đã hoàn thành</div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" class="fbar">
        <input type="text" name="q" value="{{ $search }}" placeholder="Tìm mã đơn, tên khách..." style="min-width:200px;flex:1"/>
        <select name="trangthai" onchange="this.form.submit()">
          <option value="">Tất cả trạng thái</option>
          @foreach (['ChoXacNhan','DaXacNhan','DangGiao','DaGiao','HoanThanh','DaHuy'] as $tt)
          <option value="{{ $tt }}" {{ $trangthai===$tt?'selected':'' }}>{{ $stMap[$tt][1]??$tt }}</option>
          @endforeach
        </select>
        <input type="text" class="js-date" name="from" value="{{ $from }}" placeholder="Từ ngày" title="Từ ngày">
        <input type="text" class="js-date" name="to" value="{{ $to }}" placeholder="Đến ngày" title="Đến ngày">
        <button type="submit" class="btn btn-pri">Lọc</button>
        <a href="{{ route('banhang.donhang') }}" class="btn btn-out">↩ Reset</a>
      </form>

      <!-- Table -->
      <div class="tcard">
        <div class="thead-row">
          <h3>Danh sách Đơn hàng</h3>
          <span class="cnt">{{ $total }} đơn hàng</span>
        </div>
        <div class="dt-scroll">
        <table class="dt">
          <thead>
            <tr>
              <th>MÃ ĐƠN</th><th>KHÁCH HÀNG</th><th>SP</th>
              <th>TỔNG TIỀN</th><th>NGÀY ĐẶT</th>
              <th>NV XỬ LÝ</th><th>TRẠNG THÁI</th><th>THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($donhang as $dh) @php $st = $stMap[$dh['TRANGTHAI']] ?? ['var(--text-secondary)','—']; $canNext = isset($validTransitions[$dh['TRANGTHAI']]); $canHuy  = in_array($dh['TRANGTHAI'],['ChoXacNhan','DaXacNhan']); @endphp
            <tr>
              <td><span class="mono">{{ $dh['MAHD'] }}</span></td>
              <td>
                <div style="font-weight:600;font-size:13px">{{ $dh['TENKH']??$dh['TEN_NGUOINHAN']??'—' }}</div>
                <div style="font-size:11px;color:var(--mt)">{{ $dh['SDT_KH']??'' }}</div>
              </td>
              <td style="text-align:center;font-weight:700">{{ $dh['so_sp']??0 }} SP</td>
              <td style="font-weight:700">{{ formatVND($dh['TONGTIEN_HD']??0) }}</td>
              <td style="font-size:12px">{{ $dh['NGAYLAP']?date('d/m/Y H:i',strtotime($dh['NGAYLAP'])):'—' }}</td>
              <td style="font-size:12px">{{ $dh['TENNV']??'Hệ thống' }}</td>
              <td>
                <span class="pil" style="background:color-mix(in srgb,{{ $st[0] }} 12%,transparent);color:{{ $st[0] }};border:1px solid color-mix(in srgb,{{ $st[0] }} 30%,transparent)">
                  {{ $st[1] }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                  @if ($canNext)
                  <x-nut-hanh-dong :action="route('banhang.donhang.buoc-tiep-theo', $dh['MAHD'])" method="PATCH"
                     :confirm="'Chuyển trạng thái đơn '.$dh['MAHD'].'?'" class="btn btn-sm-blue">
                    {!! icon('play', 12) !!} Tiếp theo
                  </x-nut-hanh-dong>
                  @endif
                  @if ($canHuy)
                  <x-nut-hanh-dong :action="route('banhang.donhang.huy', $dh['MAHD'])" method="PATCH"
                     :confirm="'Hủy đơn '.$dh['MAHD'].'?'" class="btn btn-sm-red">
                    {!! icon('x', 12) !!} Hủy
                  </x-nut-hanh-dong>
                  @endif
                  <a href="{{ url('ban-hang/xem-don') }}?mahd={{ $dh['MAHD'] }}"
                     style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none;background:rgba(0,0,0,.04);"
                     title="Xem">{!! icon('eye', 15) !!}</a>
                </div>
              </td>
            </tr>
            @endforeach
            @if (empty($donhang))
            <tr><td colspan="8">
              <div style="padding:32px;text-align:center;color:var(--mt)">
                <div style="font-size:36px;opacity:.3">{!! icon('cart') !!}</div>
                <p style="font-size:13px;margin:8px 0 0">Không có đơn hàng nào</p>
              </div>
            </td></tr>
            @endif
          </tbody>
        </table>
        </div>

        @if ($pages>1)
        <div class="pg">
          <span style="font-size:11px;color:var(--mt)">
            Hiển thị {{ ($page-1)*$perPage+1 }}–{{ min($page*$perPage,$total) }} trên {{ $total }} đơn
          </span>
          <div class="pga">
            @if ($page>1)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}">‹</a>@endif
            @for ($p=max(1,$page-2);$p<=min($pages,$page+2);$p++)
            <a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$p])) }}" class="{{ $p===$page?'active':'' }}">{{ $p }}</a>
            @endfor
            @if ($page<$pages)<a href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}">›</a>@endif
          </div>
        </div>
        @endif
      </div>
    </div><!-- /ct -->
  </div><!-- /main -->
</div><!-- /shell -->
@endsection
