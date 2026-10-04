<?php
// ==============================================================
// Chế độ sáng / tối và logo dùng chung cho mọi trang.
// Lựa chọn lưu trong cookie "theme" để PHP vẽ đúng ngay từ đầu, không nháy trắng.
// ==============================================================

/** 'dark' hoặc 'light' theo cookie; chưa chọn bao giờ thì trả về '' (theo hệ điều hành). */
function themeCurrent(): string {
    $t = $_COOKIE['theme'] ?? '';
    return in_array($t, ['light', 'dark'], true) ? $t : '';
}

/** Thuộc tính đặt lên thẻ <html>. */
function themeHtmlAttr(): string {
    $t = themeCurrent();
    return $t ? ' data-theme="' . $t . '"' : '';
}

/** Đặt trong <head>: favicon, bảng màu, và đoán theo hệ điều hành khi chưa có cookie. */
function themeHead(): string {
    $v = @filemtime(__DIR__ . '/../assets/css/theme.css') ?: 1;
    return '<link rel="icon" type="image/png" href="' . BASE_URL . '/assets/img/favicon.png" />' . "\n"
         . '  <link rel="stylesheet" href="' . BASE_URL . '/assets/css/theme.css?v=' . $v . '" />' . "\n"
         . "  <script>(function(){var d=document.documentElement;if(!d.dataset.theme){d.dataset.theme=(window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light';}})();</script>\n"
         . '  <script src="' . BASE_URL . '/assets/js/theme.js?v=' . (@filemtime(__DIR__ . '/../assets/js/theme.js') ?: 1) . '" defer></script>';
}

/** Logo đầy đủ: một bản cho nền sáng, một bản cho nền tối. */
function themeLogo(int $height = 32, string $alt = 'NEXUS Computer Store'): string {
    $b = BASE_URL . '/assets/img/';
    $h = (int)$height;
    return '<img class="logo-img for-light" src="' . $b . 'logo.png" alt="' . htmlspecialchars($alt) . '" style="height:' . $h . 'px">'
         . '<img class="logo-img for-dark" src="' . $b . 'logo-dark.png" alt="' . htmlspecialchars($alt) . '" style="height:' . $h . 'px">';
}

/** Nút đổi sáng / tối. */
function themeToggle(): string {
    return '<button type="button" class="theme-toggle" onclick="toggleTheme()" title="Đổi giao diện sáng / tối" aria-label="Đổi giao diện sáng / tối">'
         . '<span class="ico-moon">' . icon('moon', 17) . '</span><span class="ico-sun">' . icon('sun', 17) . '</span>'
         . '</button>';
}
