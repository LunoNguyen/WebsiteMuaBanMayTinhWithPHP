<?php
// admin/includes/header.php
$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Hệ thống quản trị bán máy tính - <?= e($pageTitle) ?>" />
  <title><?= e($pageTitle) ?> | NEXUS Admin</title>

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💻</text></svg>" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- Admin CSS -->
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css" />

  <style>
    /* Inline page-specific overrides if needed */
    body { opacity: 0; animation: fadeIn 0.3s ease 0.05s forwards; }
    @keyframes fadeIn { to { opacity: 1; } }
  </style>
</head>
<body>
