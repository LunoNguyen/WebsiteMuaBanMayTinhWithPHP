<?php
// admin/includes/footer.php
?>
  <!-- Toast Notifications -->
  <div id="toast-container"></div>

  <!-- Admin JS -->
  <script src="<?= ADMIN_URL ?>/assets/js/admin.js"></script>

  <!-- Page-specific scripts injected before footer -->
  <?php if(isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
