<?php
// nvbanhang/includes/header.php
$pageTitle = $pageTitle ?? 'Nhân viên Bán hàng';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';
requireRole(['NhanVienBan','Admin']);
?>
<!DOCTYPE html>
<html lang="vi"<?= themeHtmlAttr() ?>>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle) ?> | NEXUS Sales</title>
    <?= themeHead() ?>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css"/>
  <link rel="stylesheet" href="<?= NVB_URL ?>/assets/css/nvb.css"/>
</head>
<body>
