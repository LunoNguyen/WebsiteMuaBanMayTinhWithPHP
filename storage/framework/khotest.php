<?php $__env->startPush('styles'); ?>
<style>
:root{--wc:var(--bg-card);--wc2:var(--bg-card-hover);--wb:var(--border);--wt:var(--text-primary);--wm:var(--text-muted);--wr:10px;}
*{box-sizing:border-box;}
body{background:var(--bg-main);font-family:'Inter',sans-serif;}
.wsh{display:flex;min-height:100vh;}
.wsb{width:218px;min-width:218px;background:var(--wc);border-right:1px solid var(--wb);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.wlogo{padding:15px 13px 11px;border-bottom:1px solid var(--wb);}
.wlogo-r{display:flex;align-items:center;gap:9px;}
.wli{width:33px;height:33px;border-radius:8px;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;}
.wln{font-size:14px;font-weight:800;color:var(--wt);}
.wls{font-size:10px;color:var(--wm);}
.wng{padding:11px 9px 4px;}
.wnl{font-size:10px;color:var(--wm);padding:0 7px;margin-bottom:4px;}
.wni{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;color:var(--wm);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .15s;}
.wni:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wni.active{background:rgba(58,86,228,.15);color:var(--blue);font-weight:600;}
.wni .ni{font-size:14px;flex-shrink:0;}
.nbg{margin-left:auto;background:var(--red-solid);color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;}
.wrb{margin:9px;padding:9px 11px;border-radius:8px;background:rgba(21,128,61,.08);border:1px solid rgba(21,128,61,.2);}
.rdot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green-solid);margin-right:4px;animation:pl 2s infinite;}
@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}
.wmn{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--bg-main);}
.wtb{height:53px;background:var(--wc);border-bottom:1px solid var(--wb);display:flex;align-items:center;gap:9px;padding:0 15px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.wbc{font-size:11px;color:var(--wm);display:flex;align-items:center;gap:5px;white-space:nowrap;}
.wbc span{color:var(--wt);font-weight:600;}
.wsti{flex:1;max-width:380px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:8px;padding:6px 10px;}
.wsti input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wsti input::placeholder{color:var(--wm);}
.wkbd{background:var(--wb);color:var(--wm);border-radius:4px;padding:1px 5px;font-size:10px;font-family:monospace;}
.wloc{display:flex;align-items:center;gap:6px;padding:5px 10px;background:rgba(58,86,228,.1);border:1px solid rgba(58,86,228,.3);border-radius:8px;font-size:11px;color:var(--blue);white-space:nowrap;cursor:pointer;}
.wbn{padding:6px 11px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;transition:all .15s;text-decoration:none;white-space:nowrap;}
.wb-out{background:transparent;border:1px solid var(--wb)!important;color:var(--wm);}
.wb-out:hover{border-color:var(--blue)!important;color:var(--wt);}
.wb-pri{background:var(--blue-solid);color:#fff;}
.wb-pri:hover{filter:brightness(.92);}
.wb-suc{background:var(--green-solid);color:#fff;}
.wb-suc:hover{filter:brightness(.92);}
.wb-sc{background:rgba(14,116,144,.12);border:1px solid rgba(14,116,144,.3)!important;color:var(--cyan);}
.wav{width:32px;height:32px;border-radius:50%;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;border:2px solid rgba(58,86,228,.4);cursor:pointer;flex-shrink:0;}
.wntf{width:31px;height:31px;border-radius:50%;background:var(--wc2);border:1px solid var(--wb);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;font-size:13px;}
.wntd{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--red-solid);border:2px solid var(--wc);}
.wct{flex:1;overflow:auto;padding:16px;display:flex;flex-direction:column;gap:12px;}
.wph{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.wph h1{font-size:19px;font-weight:800;color:var(--wt);margin:0;}
.wph p{font-size:12px;color:var(--wm);margin:3px 0 0;}
.wkg{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;}
.wk{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:13px 15px;display:flex;align-items:center;gap:11px;}
.wk-al{border-color:rgba(200,30,30,.35);background:rgba(200,30,30,.04);}
.wk-dk{background:var(--wc2);}
.wki{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.wkv{font-size:24px;font-weight:900;line-height:1;}
.wkl{font-size:10px;color:var(--wm);margin-top:1px;}
.wsp{display:grid;grid-template-columns:1fr 385px;gap:12px;min-height:0;}
.wpla{display:flex;flex-direction:column;gap:9px;}
.wfl{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:9px 13px;display:flex;gap:7px;flex-wrap:wrap;align-items:center;}
.wfi{flex:1;min-width:170px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 9px;}
.wfi input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wfi input::placeholder{color:var(--wm);}
.wse{background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 8px;color:var(--wt);font-size:12px;outline:none;cursor:pointer;color-scheme:dark;}
.wft{display:flex;align-items:center;gap:5px;padding:6px 10px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;font-size:12px;color:var(--wm);cursor:pointer;text-decoration:none;white-space:nowrap;transition:all .15s;}
.wft:hover,.wft.active{background:rgba(58,86,228,.12);border-color:rgba(58,86,228,.4);color:var(--blue);}
.wfr{font-size:12px;color:var(--wm);cursor:pointer;padding:6px 8px;border-radius:7px;text-decoration:none;}
.wfr:hover{color:var(--wt);}
.wtw{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);overflow:hidden;}
.wth{padding:10px 13px;border-bottom:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wth h3{font-size:13px;font-weight:700;color:var(--wt);margin:0;}
.wcnt{background:rgba(58,86,228,.15);color:var(--blue);border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.wt{width:100%;border-collapse:collapse;}
table.wt th{padding:8px 10px;font-size:10px;font-weight:700;color:var(--wm);border-bottom:1px solid var(--wb);text-align:left;white-space:nowrap;}
table.wt td{padding:9px 10px;font-size:13px;color:var(--wt);border-bottom:1px solid var(--border);vertical-align:middle;}
table.wt tr:last-child td{border-bottom:none;}
table.wt tr{cursor:pointer;transition:background .1s;}
table.wt tr:hover td{background:rgba(0,0,0,.04);}
table.wt tr.sel td{background:rgba(58,86,228,.08);}
table.wt tr.sel td:first-child{border-left:3px solid var(--blue);padding-left:7px;}
.wcode{font-family:monospace;font-weight:700;color:var(--blue);font-size:13px;}
.wpil{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.wpg{padding:9px 13px;border-top:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wpa{display:flex;gap:3px;}
.wpl2{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:var(--wm);background:transparent;border:1px solid transparent;text-decoration:none;cursor:pointer;transition:all .15s;}
.wpl2:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wpl2.active{background:rgba(58,86,228,.2);border-color:rgba(58,86,228,.4);color:var(--blue);}
.wpr{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);display:flex;flex-direction:column;overflow:hidden;position:sticky;top:65px;max-height:calc(100vh - 82px);}
.wdh{padding:12px 14px;border-bottom:1px solid var(--wb);flex-shrink:0;}
.wdpn{font-family:monospace;font-size:17px;font-weight:900;color:var(--blue);}
.wdsu{font-size:10px;color:var(--wm);margin-top:1px;}
.wdnc{font-size:13px;font-weight:700;color:var(--wt);margin-top:6px;}
.wdmt{font-size:10px;color:var(--wm);}
.wdpr{font-size:19px;font-weight:900;color:var(--green);white-space:nowrap;}
.wdb{flex:1;overflow-y:auto;padding:12px 14px;display:flex;flex-direction:column;gap:11px;}
.wprw{background:var(--wc2);border:1px solid var(--wb);border-radius:8px;padding:10px;}
.wprr{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:11px;}
.wptr{height:5px;background:rgba(0,0,0,.04);border-radius:3px;overflow:hidden;}
.wpfl{height:100%;border-radius:3px;background:var(--blue-solid);transition:width .6s;}
.wsl{font-size:10px;font-weight:700;color:var(--wm);display:flex;justify-content:space-between;}
.wpc{background:var(--wc2);border:1px solid var(--wb);border-radius:8px;overflow:hidden;margin-top:6px;}
.wpc-h{padding:8px 10px;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--wb);}
.wpic{width:30px;height:30px;border-radius:6px;background:rgba(58,86,228,.12);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.wpnm{font-size:11px;font-weight:700;color:var(--wt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.wpsk{font-size:10px;color:var(--wm);margin-top:1px;font-family:monospace;}
.wch{display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;}
.cok{background:rgba(21,128,61,.14);color:var(--green);border:1px solid rgba(21,128,61,.3);}
.csc{background:rgba(58,86,228,.14);color:var(--blue);border:1px solid rgba(58,86,228,.3);}
.cda{background:rgba(200,30,30,.14);color:var(--red);border:1px solid rgba(200,30,30,.3);}
.wpc-b{padding:8px 10px;}
.wpcr{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;font-size:10px;}
.pll{color:var(--wm);margin-bottom:1px;}
.plv{font-weight:700;color:var(--wt);}
.pll2{color:var(--red);}
.wsr{margin-top:6px;padding:5px 8px;background:rgba(0,0,0,.2);border-radius:6px;font-size:10px;color:var(--wm);display:flex;align-items:center;justify-content:space-between;}
.wlb{padding:8px 10px;background:rgba(58,86,228,.08);border:1px solid rgba(58,86,228,.2);border-radius:8px;display:flex;align-items:center;justify-content:space-between;}
.wda{padding:10px 13px;border-top:1px solid var(--wb);display:flex;flex-direction:column;gap:6px;flex-shrink:0;}
.war{display:flex;gap:6px;}
.war .wbn{flex:1;justify-content:center;}
.wtrg{padding:7px 10px;background:rgba(21,128,61,.05);border:1px solid rgba(21,128,61,.15);border-radius:7px;display:flex;align-items:center;justify-content:space-between;}
.wtrg .tt{font-size:10px;color:var(--wm);}
.wtrg .tt strong{color:var(--wt);}
.wrdy{background:rgba(21,128,61,.15);color:var(--green);border:1px solid rgba(21,128,61,.3);border-radius:5px;padding:2px 7px;font-size:10px;font-weight:800;}
.wde{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--wm);gap:8px;padding:24px;text-align:center;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--wb);border-radius:2px;}
@media(max-width:1200px){.wkg{grid-template-columns:repeat(2,1fr);}.wsp{grid-template-columns:1fr;}.wpr{position:static;max-height:none;}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="wsh">
<?php echo $__env->make('partials.kho.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- MAIN -->
<div class="wmn">
  <!-- TopBar -->
  <header class="wtb">
    <button type="button" class="w-menu-btn" onclick="toggleKhoSidebar()" aria-label="Mở menu"><?php echo icon('menu', 18); ?></button>
    <div class="wbc">Kho vận &rsaquo; <span>Nhập hàng &amp; Nhà cung cấp</span></div>
    <form method="GET" style="flex:1;max-width:380px;display:flex">
      <div class="wsti" style="flex:1">
        <span style="color:var(--wm);display:flex"><?php echo icon('search', 14); ?></span>
        <input id="searchInput" type="text" name="q" value="<?php echo e($search); ?>" placeholder="Tìm mã phiếu nhập, mã đơn hàng, SKU hàng hóa..."/>
        <span class="wkbd">F2</span>
      </div>
      <input type="hidden" name="pn" value="<?php echo e($selectedMapnh); ?>">
    </form>
    <div class="wloc" title="Tổng kho Tân Bình (Kho chính)">&#128205; <span class="wtb-lbl">Tổng kho Tân Bình</span> &#9660;</div>
    <div class="wntf" title="Thông báo"><?php echo icon('bell', 15); ?><span class="wntd"></span></div>
    <?php if (isset($component)) { $__componentOriginal2090438866f3dcdb76cd8b070bcc302d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $attributes = $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $component = $__componentOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
    <div class="wusr">
      <div class="wusr-t">
        <div style="font-size:12px;font-weight:700;color:var(--wt)"><?php echo e(auth()->user()->tenHienThi()); ?></div>
        <div style="font-size:10px;color:var(--wm)">NV Kho &bull; <?php echo e((auth()->user()->MANV ?? '—')); ?></div>
      </div>
      <div class="wav"><?php echo e(mb_strtoupper(mb_substr(auth()->user()->tenHienThi(),0,2))); ?></div>
    </div>
    <div class="wtb-act">
      <button class="wbn wb-sc" title="Quét mã vạch Barcode/QR (F3)" onclick="openBarcodeModal()">&#128247; <span class="wtb-lbl">Quét mã vạch</span> <span class="wkbd">F3</span></button>
      <button class="wbn wb-out" title="Xuất biên bản kiểm kê" onclick="showToast('Đang xuất biên bản kiểm kê...')">&#128203; <span class="wtb-lbl">Xuất biên bản kiểm kê</span></button>
    </div>
  </header>

  <!-- Content -->
  <div class="wct">
    <div class="wph">
      <div>
        <h1>Quản lý Phiếu nhập hàng &amp; Kiểm đếm kho</h1>
        <p>Nhận hàng từ nhà cung cấp &middot; Kiểm đếm &amp; Đối chiếu SKU &middot; Cập nhật tồn kho thời gian thực</p>
      </div>
      <a href="<?php echo e(route('admin.nhaphang')); ?>" class="wbn wb-pri" style="padding:9px 15px;font-size:13px">&#65291; Tạo phiếu nhập kho mới</a>
    </div>

    <!-- KPI -->
    <div class="wkg">
      <div class="wk">
        <div class="wki" style="background:rgba(58,86,228,.12)"><?php echo icon('clipboard', 18); ?></div>
        <div>
          <div class="wkl">TỔNG PHIẾU THÁNG NÀY</div>
          <div class="wkv" style="color:var(--blue)"><?php echo e($kpiPhieu['tong']??0); ?></div>
          <div style="font-size:11px;color:var(--wm);margin-top:2px">phiếu</div>
          <div style="font-size:10px;margin-top:3px;color:var(--green)">&#8679; +6 phiếu mới tuần này</div>
        </div>
      </div>
      <div class="wk">
        <div class="wki" style="background:rgba(180,83,9,.12)"><?php echo icon('clock', 18); ?></div>
        <div>
          <div class="wkl">ĐANG CHỜ KIỂM ĐẾM</div>
          <div class="wkv" style="color:var(--orange)"><?php echo e(str_pad($kpiCho['tong']??0,2,'0',STR_PAD_LEFT)); ?></div>
          <div style="font-size:11px;color:var(--wm);margin-top:2px">lô hàng tại dock</div>
          <div style="font-size:10px;margin-top:3px;color:var(--wm)">FPT Synnex &amp; Petrosetco PSD</div>
        </div>
      </div>
      <div class="wk wk-al">
        <div class="wki" style="background:rgba(200,30,30,.12)"><?php echo icon('alert', 18); ?></div>
        <div>
          <div class="wkl" style="color:var(--red)">CẢNH BÁO TỒN THẤP (&lt;10 SP)</div>
          <div class="wkv" style="color:var(--red)"><?php echo e($kpiTonThap['tong']??0); ?></div>
          <div style="font-size:11px;color:var(--red);margin-top:2px">SKU cần hàng</div>
          <div style="font-size:10px;margin-top:3px;color:var(--red)">Cần ưu tiên nhập [V_TONKHO]</div>
        </div>
      </div>
      <div class="wk wk-dk">
        <div class="wki" style="background:rgba(21,128,61,.1)"><?php echo icon('wallet', 18); ?></div>
        <div style="flex:1">
          <div class="wkl">TỔNG GIÁ TRỊ NHẬP KHO</div>
          <div style="font-size:15px;font-weight:900;color:var(--wt);margin-top:2px"><?php echo e(formatVND($kpiGiaTri['tong']??0)); ?></div>
          <div style="font-size:10px;margin-top:3px;color:var(--wm)">Giá trị luân chuyển nội bộ</div>
        </div>
      </div>
    </div>

    <!-- Split Panel -->
    <div class="wsp">
      <!-- LEFT: Table -->
      <div class="wpla">
        <form method="GET" class="wfl">
          <div class="wfi">
            <span style="color:var(--wm);display:flex"><?php echo icon('search', 14); ?></span>
            <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="Tìm theo mã phiếu (PN001..), tên NCC, SKU sản phẩm..."/>
          </div>
          <input type="hidden" name="pn" value="<?php echo e($selectedMapnh); ?>">
          <a href="?filter=cho&pn=<?php echo e($selectedMapnh); ?>" class="wft <?php echo e($filter==='cho'?'active':''); ?>">Chờ kiểm đếm</a>
          <select name="mancc" class="wse" onchange="this.form.submit()">
            <option value="">Tất cả nhà cung cấp</option>
            <?php $__currentLoopData = $nccList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($n['MANCC']); ?>" <?php echo e($mancc===$n['MANCC']?'selected':''); ?>><?php echo e($n['TENNCC']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <select name="thang" class="wse" onchange="this.form.submit()">
            <?php for($i=0;$i<12;$i++): ?> <?php $m=date('Y-m',strtotime("first day of -$i month")); ?>
            <option value="<?php echo e($m); ?>" <?php echo e($thang===$m?'selected':''); ?>>Tháng <?php echo e(date('m/Y',strtotime($m.'-01'))); ?></option>
            <?php endfor; ?>
          </select>
          <a href="<?php echo e(route('kho.nhaphang')); ?>" class="wfr">Đặt lại</a>
          <button type="submit" class="wbn wb-out" style="padding:6px 11px">Tìm</button>
        </form>

        <div class="wtw">
          <div class="wth">
            <h3>Danh sách phiếu nhập kho</h3>
            <span class="wcnt"><?php echo e($total); ?> bản ghi</span>
          </div>
          <div class="wt-scroll">
          <table class="wt">
            <thead>
              <tr>
                <th>MÃ PN</th><th>NHÀ CUNG CẤP</th><th>SỐ LƯỢNG / SKU</th>
                <th>TỔNG TIỀN</th><th>NGÀY &amp; THỦ KHO</th><th>TRẠNG THÁI</th>
              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $phieunhap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pnh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php $st = $stMap[$pnh['TRANGTHAI']] ?? ['var(--text-secondary)','Không rõ']; $isSel = ($pnh['MAPNH'] === $selectedMapnh); $ngay = $pnh['NGAYTAO'] ?? ''; ?>
              <tr class="<?php echo e($isSel?'sel':''); ?>" onclick="selectPhieu('<?php echo e($pnh['MAPNH']); ?>')">
                <td>
                  <span class="wcode"><?php echo e($pnh['MAPNH']); ?></span>
                  <div style="font-size:10px;color:var(--wm)">Lô #<?php echo e(substr($pnh['MAPNH'],2)); ?></div>
                </td>
                <td>
                  <div style="font-weight:600;font-size:12px"><?php echo e($pnh['TENNCC']?? '—'); ?></div>
                  <div style="font-size:10px;color:var(--wm)"><?php echo e($pnh['MANCC']??''); ?></div>
                </td>
                <td style="text-align:center">
                  <div style="font-weight:700"><?php echo e($pnh['tong_sl']??0); ?> chiếc</div>
                  <div style="font-size:10px;color:var(--wm)"><?php echo e($pnh['so_sku']??0); ?> SKU</div>
                </td>
                <td><div style="font-weight:700;font-size:13px"><?php echo e(formatVND($pnh['TONGCONG_PNH']??0)); ?></div></td>
                <td>
                  <div style="font-size:11px"><?php echo e($ngay?date('d/m/Y',strtotime($ngay)):'Hôm nay'); ?></div>
                  <div style="font-size:10px;color:var(--wm)"><?php echo e($pnh['TENNV']?? '—'); ?></div>
                </td>
                <td>
                  <span class="wpil" style="background:color-mix(in srgb,<?php echo e($st[0]); ?> 12%,transparent);color:<?php echo e($st[0]); ?>;border:1px solid color-mix(in srgb,<?php echo e($st[0]); ?> 30%,transparent)">
                    <?php echo e($st[1]); ?>

                  </span>
                </td>
              </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if(empty($phieunhap)): ?>
              <tr><td colspan="6"><div style="padding:28px;text-align:center;color:var(--wm)"><div style="font-size:28px;opacity:.3">&#128230;</div><p style="font-size:12px;margin:5px 0 0">Không có phiếu nhập nào</p></div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          </div>
          <?php if($pages>1): ?>
          <div class="wpg">
            <span style="font-size:11px;color:var(--wm)">Hiển thị <?php echo e(($page-1)*$perPage+1); ?>&ndash;<?php echo e(min($page*$perPage,$total)); ?> trên <?php echo e($total); ?> phiếu</span>
            <div class="wpa">
              <?php if($page>1): ?><a href="?<?php echo e(http_build_query(array_merge(request()->query(),['page'=>$page-1]))); ?>" class="wpl2">&lsaquo;</a><?php endif; ?>
              <?php for($p=max(1,$page-2);$p<=min($pages,$page+2);$p++): ?>
              <a href="?<?php echo e(http_build_query(array_merge(request()->query(),['page'=>$p]))); ?>" class="wpl2 <?php echo e($p===$page?'active':''); ?>"><?php echo e($p); ?></a>
              <?php endfor; ?>
              <?php if($page<$pages): ?><a href="?<?php echo e(http_build_query(array_merge(request()->query(),['page'=>$page+1]))); ?>" class="wpl2">&rsaquo;</a><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div><!-- /wpla -->

      <!-- RIGHT: Detail -->
      <div class="wpr">
        <?php if($detail): ?> <?php $stD = $stMap[$detail['TRANGTHAI']] ?? ['var(--text-secondary)','&mdash;']; $totalSku = count($ctpnList); $doneSku  = count(array_filter($ctpnList, fn($c)=>((int)($c['SOLUONG']??0))>0)); $pct = $totalSku>0 ? round($doneSku/$totalSku*100) : 0; ?>
        <div class="wdh">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:7px">
                <span class="wdpn"><?php echo e($detail['MAPNH']); ?></span>
                <span class="wch csc" style="background:color-mix(in srgb,<?php echo e($stD[0]); ?> 12%,transparent);color:<?php echo e($stD[0]); ?>;border-color:color-mix(in srgb,<?php echo e($stD[0]); ?> 30%,transparent)"><?php echo e($stD[1]); ?></span>
              </div>
              <div class="wdsu">
                Tạo lúc: <?php echo e($detail['NGAYTAO']?date('H:i',strtotime($detail['NGAYTAO'])):'&mdash;'); ?>

                &mdash; <?php echo e(($detail['NGAYTAO']&&date('Y-m-d',strtotime($detail['NGAYTAO']))==date('Y-m-d'))?'Hôm nay':($detail['NGAYTAO']?date('d/m/Y',strtotime($detail['NGAYTAO'])):'&mdash;')); ?>

              </div>
              <div class="wdnc"><?php echo e($detail['TENNCC']?? '—'); ?></div>
              <div class="wdmt">Mã NCC: <?php echo e($detail['MANCC']?? '—'); ?> <?php if(!empty($detail['GHICHU'])): ?> &bull; <?php echo e(substr($detail['GHICHU'],0,28)); ?> <?php endif; ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div class="wdpr"><?php echo e(formatVND($detail['TONGCONG_PNH']??0)); ?></div>
              <div style="font-size:10px;color:var(--wm);margin-top:2px">Tổng giá trị<br>nhập kho</div>
            </div>
          </div>
        </div>

        <div class="wdb">
          <!-- Progress -->
          <div class="wprw">
            <div class="wprr">
              <span style="color:var(--wm)">Tiến độ kiểm đếm thực tế:</span>
              <span style="font-weight:700;color:var(--wt)"><?php echo e($doneSku); ?> / <?php echo e($totalSku); ?> sản phẩm <span style="color:var(--blue)">(<?php echo e($pct); ?>%)</span></span>
            </div>
            <div class="wptr"><div class="wpfl" style="width:<?php echo e($pct); ?>%"></div></div>
          </div>

          <!-- Products -->
          <div>
            <div class="wsl"><span>CHI TIẾT SẢN PHẨM</span><span><?php echo e($totalSku); ?> mặt hàng SKU</span></div>
            <?php if(empty($ctpnList)): ?>
            <div style="padding:16px;text-align:center;color:var(--wm);font-size:12px"><div style="font-size:24px;opacity:.3">&#128230;</div><p>Chưa có chi tiết sản phẩm</p></div>
            <?php endif; ?>
            <?php $__currentLoopData = $ctpnList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php $slDat  = (int)($ct['SOLUONG']??0); $slNhan = (int)($ct['SOLUONG']??0); $lech   = $slNhan - $slDat; $isLap  = stripos($ct['TENSP']??'','macbook')!==false || stripos($ct['TENSP']??'','lenovo')!==false || stripos($ct['TENSP']??'','laptop')!==false; $emoji  = $isLap?'&#128187;':'&#128377;'; $isOK   = ($lech===0 && $slDat>0); $chipC  = $isOK?'cok':($lech<0?'cda':'csc'); $chipTx = $isOK?'Khớp 100%':($lech<0?'Thiếu hàng':'Đang quét serial'); ?>
            <div class="wpc">
              <div class="wpc-h">
                <div class="wpic"><?php echo e($emoji); ?></div>
                <div style="flex:1;min-width:0">
                  <div class="wpnm"><?php echo e($ct['TENSP']); ?></div>
                  <div class="wpsk">SKU: <?php echo e($ct['MASP']); ?> <?php if(!empty($ct['TENLOAI'])): ?> &bull; <?php echo e($ct['TENLOAI']); ?> <?php endif; ?>@if (!empty($ct['TENNSX'])) &bull; <?php echo e($ct['TENNSX']); ?> <?php endif; ?></div>
                </div>
                <span class="wch <?php echo e($chipC); ?>"><?php echo e($chipTx); ?></span>
              </div>
              <div class="wpc-b">
                <div class="wpcr">
                  <div><div class="pll">SL đặt</div><div class="plv"><?php echo e($slDat); ?></div></div>
                  <div><div class="pll">Thực nhận</div><div class="plv"><?php echo e($slNhan); ?></div></div>
                  <div><div class="pll">Lệch</div><div class="plv <?php echo e($lech<0?'pll2':''); ?>"><?php echo e($lech>0?'+'.$lech:$lech); ?></div></div>
                </div>
                <?php if(!empty($ct['DONGIA_NHAP'])): ?>
                <div style="margin-top:4px;font-size:10px;color:var(--wm)">
                  Đơn giá nhập: <strong style="color:var(--wt)"><?php echo e(formatVND($ct['DONGIA_NHAP'])); ?></strong>
                  &bull; Thành tiền: <strong style="color:var(--blue)"><?php echo e(formatVND(($ct['DONGIA_NHAP']??0)*$slDat)); ?></strong>
                </div>
                <?php endif; ?>
                <?php if(!$isOK): ?>
                <div class="wsr">
                  <span>Serial vừa quét: <strong style="color:var(--wt);font-family:monospace">PF4BKZ88</strong></span>
                  <a href="#" style="font-size:10px;color:var(--blue)">Hủy</a>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>

          <!-- Location -->
          <div class="wlb">
            <div>
              <div style="font-size:10px;font-weight:700;color:var(--blue);margin-bottom:2px">Vị trí kho đề xuất:</div>
              <div style="font-size:11px;color:var(--wm)"><strong style="color:var(--blue)">Dãy A &bull; Kệ 03 &bull; Tầng 2</strong></div>
            </div>
            <button class="wlnk" onclick="showToast('Chức năng đang phát triển')">Đổi vị trí &rsaquo;</button>
          </div>
        </div><!-- /wdb -->

        <!-- Actions -->
        <div class="wda">
          <div class="war">
            <button class="wbn wb-out" onclick="openSerialModal()">Nhập mã Serial / IMEI</button>
            <button class="wbn wb-out" onclick="showToast('Đang gửi lệnh in tem mã vạch...')">In tem mã vạch</button>
          </div>
          <?php if(in_array($detail['TRANGTHAI']??'',['ChoDuyet','DaDuyet','DaNhan'])): ?>
          <button class="wbn wb-suc" style="width:100%;padding:11px;font-size:13px;justify-content:center;border-radius:9px" onclick="hoanTatNhap(<?php echo \Illuminate\Support\Js::from(route('kho.nhaphang.hoan-tat', $detail['MAPNH']))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($detail['MAPNH'])->toHtml() ?>)">
            Hoàn tất nhập kho &amp; Tăng tồn kho
          </button>
          <?php else: ?>
          <button disabled style="width:100%;padding:11px;font-size:13px;background:rgba(0,0,0,.04);border:1px solid var(--wb);border-radius:9px;color:var(--wm);cursor:not-allowed">Đã hoàn tất nhập kho</button>
          <?php endif; ?>
          <div class="wtrg">
            <div class="tt">Tự động kích hoạt <strong>Trigger QL_BANMT</strong><br><span style="font-size:9px">Cập nhật tức thì vào bảng TONKHO &amp; LICH_SU_GIAODICH</span></div>
            <span class="wrdy">READY</span>
          </div>
        </div>

        <?php else: ?>
        <div class="wde">
          <div style="font-size:40px;opacity:.3"><?php echo icon('clipboard', 18); ?></div>
          <p style="font-size:13px">Chọn một phiếu nhập để xem chi tiết</p>
          <p style="font-size:11px">Bấm vào dòng bất kỳ trong bảng bên trái</p>
        </div>
        <?php endif; ?>
      </div><!-- /wpr -->
    </div><!-- /wsp -->
  </div><!-- /wct -->
</div><!-- /wmn -->
</div><!-- /wsh -->

<!-- Barcode Modal -->
<div id="barcodeModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.75);align-items:center;justify-content:center">
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:24px;width:380px;max-width:90vw">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="margin:0;color:var(--text-primary);font-size:15px">Quét Barcode / QR</h3>
      <button onclick="closeBarcodeModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&#10005;</button>
    </div>
    <div style="background:var(--bg-main);border:1px solid var(--border);border-radius:9px;padding:18px;margin-bottom:11px;text-align:center">
      <div style="font-size:36px;margin-bottom:5px">&#128230;</div>
      <div style="font-size:12px;color:var(--text-muted)">Đưa mã vạch vào vùng quét hoặc nhập thủ công</div>
    </div>
    <div style="display:flex;gap:7px">
      <input id="barcodeInput" type="text" placeholder="Nhập mã serial / barcode..."
        style="flex:1;background:var(--bg-card-hover);border:1px solid var(--border);border-radius:8px;padding:8px 10px;color:var(--text-primary);font-size:12px;outline:none;font-family:monospace"
        onkeypress="if(event.key==='Enter')processBarcode()" />
      <button onclick="processBarcode()" class="wbn wb-pri" style="padding:8px 13px">OK</button>
    </div>
    <div id="barcodeResult" style="margin-top:7px;font-size:11px;color:var(--green);min-height:15px"></div>
  </div>
</div>

<!-- Serial Modal -->
<div id="serialModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.75);align-items:center;justify-content:center">
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:24px;width:400px;max-width:90vw">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="margin:0;color:var(--text-primary);font-size:15px">Nhập mã Serial / IMEI</h3>
      <button onclick="closeSerialModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&#10005;</button>
    </div>
    <textarea id="serialInput" rows="5" placeholder="Nhập từng serial một dòng, hoặc dán danh sách..."
      style="width:100%;background:var(--bg-card-hover);border:1px solid var(--border);border-radius:8px;padding:8px;color:var(--text-primary);font-size:12px;font-family:monospace;resize:vertical;outline:none;box-sizing:border-box"></textarea>
    <div style="display:flex;gap:7px;margin-top:9px">
      <button onclick="closeSerialModal()" class="wbn wb-out" style="flex:1;justify-content:center">Hủy</button>
      <button onclick="saveSerials()" class="wbn wb-pri" style="flex:1;justify-content:center">Lưu Serial</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="wmsToast" style="position:fixed;bottom:20px;right:20px;z-index:2000;padding:10px 16px;border-radius:9px;font-size:13px;font-weight:600;display:none;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.4);">
  <span id="toastTxt"></span>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function selectPhieu(m){var p=new URLSearchParams(window.location.search);p.set('pn',m);window.location.href=<?php echo \Illuminate\Support\Js::from(route('kho.nhaphang'))->toHtml() ?>+'?'+p.toString();}
function openBarcodeModal(){document.getElementById('barcodeModal').style.display='flex';setTimeout(function(){document.getElementById('barcodeInput').focus();},80);}
function closeBarcodeModal(){document.getElementById('barcodeModal').style.display='none';document.getElementById('barcodeInput').value='';document.getElementById('barcodeResult').textContent='';}
function processBarcode(){var v=document.getElementById('barcodeInput').value.trim();if(!v)return;document.getElementById('barcodeResult').textContent='Đã ghi nhận: '+v;document.getElementById('barcodeInput').value='';showToast('Quét thành công: '+v);}
function openSerialModal(){document.getElementById('serialModal').style.display='flex';}
function closeSerialModal(){document.getElementById('serialModal').style.display='none';}
function saveSerials(){var l=document.getElementById('serialInput').value.trim().split('\n').filter(function(x){return x.trim();});if(!l.length)return;closeSerialModal();showToast('Đã lưu '+l.length+' mã serial thành công!');}
function hoanTatNhap(url,m){
  if(!confirm('Xác nhận hoàn tất nhập kho phiếu '+m+'?
Thao tác sẽ cộng số lượng vào tồn kho của sản phẩm.'))return;
  fetch(url,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
    .then(function(r){return r.json();})
    .then(function(d){if(d.success){showToast('Nhập kho hoàn tất! Tồn kho đã cập nhật.');setTimeout(function(){location.reload();},1500);}else{showToast(d.error||'Không hoàn tất được phiếu nhập.','error');}})
    .catch(function(){showToast('Không kết nối được máy chủ.','error');});
}
function showToast(msg,t){var el=document.getElementById('wmsToast');document.getElementById('toastTxt').textContent=msg;el.style.background=t==='error'?'#c81e1e':'#15803d';el.style.display='block';setTimeout(function(){el.style.display='none';},2800);}
document.addEventListener('keydown',function(e){if(e.key==='F2'){e.preventDefault();var s=document.getElementById('searchInput');if(s)s.focus();}if(e.key==='F3'){e.preventDefault();openBarcodeModal();}if(e.key==='Escape'){closeBarcodeModal();closeSerialModal();}});
document.getElementById('barcodeModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
document.getElementById('serialModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.kho', ['title' => 'Nhân viên Kho - Quản lý Nhập hàng'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>