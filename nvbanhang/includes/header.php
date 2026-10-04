<?php
// nvbanhang/includes/header.php
$pageTitle = $pageTitle ?? 'Nhân viên Bán hàng';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';
requireRole(['NhanVienBan','Admin']);
define('NVB_URL', BASE_URL . '/nvbanhang');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle) ?> | NEXUS Sales</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <style>
    body{opacity:0;animation:fadeIn .3s ease .05s forwards;}
    @keyframes fadeIn{to{opacity:1;}}
  </style>
</head>
<body>
