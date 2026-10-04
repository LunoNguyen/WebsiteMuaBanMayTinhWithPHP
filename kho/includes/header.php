<?php
// kho/includes/header.php
$pageTitle = $pageTitle ?? 'Nhân viên Kho';
require_once __DIR__ . '/../../config/auth.php';
requireRole(['NhanVienKho','Admin']); // Admin cũng có thể xem
?>
<!DOCTYPE html>
<html lang="vi"<?= themeHtmlAttr() ?>>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="NEXUS WMS - Quản lý kho vận" />
  <title><?= htmlspecialchars($pageTitle) ?> | NEXUS WMS</title>
    <?= themeHead() ?>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= KHO_URL ?>/assets/css/kho.css" />
</head>
<body>
