<?php
// kho/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="wsb">
  <div class="wlogo">
    <div class="wlogo-r">
      <a href="<?= KHO_URL ?>/index.php" class="wlogo-a" title="Về trang chính">
        <?= themeLogo(40) ?>
        <div class="wls">Kho Vận &amp; Logistics</div>
      </a>
    </div>
  </div>

  <div class="wng">
    <div class="wnl">Nghiệp Vụ Vận Hành</div>
    <a href="<?= KHO_URL ?>/index.php"
       class="wni <?= $currentPage==='index'?'active':'' ?>">
      <span class="ni"><?= icon('package') ?></span>
      <div>
        <div>Nhập hàng &amp; Kiểm đếm</div>
        <div style="font-size:10px;color:var(--wm)">Phiếu nhập &amp; Nhà cung cấp</div>
      </div>
      <?php
        try {
          $pc = dbFetchOne("SELECT COUNT(*) AS c FROM PHIEUNHAPHANG WHERE TRANGTHAI IN ('ChoDuyet','DaDuyet')");
          if(!empty($pc['c'])): ?><span class="nbg"><?= $pc['c'] ?></span><?php endif;
        } catch(Exception $e) {}
      ?>
    </a>
    <a href="<?= KHO_URL ?>/donhang.php"
       class="wni <?= $currentPage==='donhang'?'active':'' ?>">
      <span class="ni"><?= icon('truck') ?></span>
      <div>
        <div>Đơn hàng cần xuất kho</div>
        <div style="font-size:10px;color:var(--wm)">Soạn hàng &amp; Điều phối xuất</div>
      </div>
      <?php
        try {
          $pend = dbFetchOne("SELECT COUNT(*) AS c FROM HOADON WHERE TRANGTHAI='DaXacNhan'");
          if(!empty($pend['c'])): ?><span class="nbg" style="background:var(--green-solid)"><?= $pend['c'] ?></span><?php endif;
        } catch(Exception $e) {}
      ?>
    </a>
  </div>

  <div style="margin-top:auto;padding:9px;">
    <div class="wrb">
      <div><span class="rdot"></span><span style="font-size:12px;font-weight:700;color:var(--green)">● Online</span></div>
      <div style="font-size:10px;color:var(--wm);margin-top:2px">Nhân viên kho — Vận hành kho trung tâm</div>
      <div style="font-size:10px;color:var(--wm);margin-top:5px;font-family:monospace">
        <?= htmlspecialchars($_SESSION['tennv']??'NV Kho') ?>
        &bull;
        <?= htmlspecialchars($_SESSION['manv']??'NV004') ?>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="wni" style="color:var(--red);margin-top:5px;">
      <span class="ni"><?= icon('logout') ?></span> Đăng xuất
    </a>
  </div>
</aside>
<div class="w-backdrop" onclick="toggleKhoSidebar()"></div>
