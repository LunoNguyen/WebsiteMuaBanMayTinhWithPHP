<?php
// ================================================================
// auth/login.php — Trang đăng nhập chung cho nhân viên và quản trị
// Chỉ đăng nhập bằng tài khoản trong bảng TAIKHOAN. Role lấy từ loại tài khoản
// và chức vụ của nhân viên (xem resolveRole trong config/auth.php).
// ================================================================
require_once __DIR__ . '/../config/config.php';

// Đã đăng nhập với role nội bộ → vào thẳng trang của mình
if (!empty($_SESSION['matk']) && in_array($_SESSION['loai'] ?? '', INTERNAL_ROLES, true)) {
    redirectByRole($_SESSION['loai']);
}

$msg     = '';
$msgType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $msg = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $tk = dbFetchOne("
            SELECT tk.MATK, tk.EMAIL_TK, tk.MATKHAU, tk.LOAI_TAIKHOAN, tk.TRANGTHAI,
                   tk.MANV, tk.MAKH, nv.TENNV, nv.MACV, nv.TRANGTHAI AS NV_TRANGTHAI,
                   cv.TENCV, kh.TENKH
            FROM TAIKHOAN tk
            LEFT JOIN NHANVIEN  nv ON tk.MANV = nv.MANV
            LEFT JOIN CHUCVU    cv ON nv.MACV = cv.MACV
            LEFT JOIN KHACHHANG kh ON tk.MAKH = kh.MAKH
            WHERE tk.EMAIL_TK = ?
            LIMIT 1
        ", [$email], 's');

        // Cùng một câu báo cho sai email và sai mật khẩu, để không lộ email nào có trong hệ thống
        if (!$tk || !password_verify($pass, $tk['MATKHAU'])) {
            $msg = 'Email hoặc mật khẩu không đúng!';
        } elseif ($tk['TRANGTHAI'] !== 'HoatDong') {
            $msg = 'Tài khoản đang bị khoá. Vui lòng liên hệ quản trị viên.';
        } else {
            $reason = null;
            $role   = resolveRole($tk, $reason);
            if (!$role) {
                $msg = $reason;
            } else {
                // Mật khẩu lưu bằng thuật toán hay độ khó cũ thì mã hoá lại bằng bcrypt hiện tại
                if (password_needs_rehash($tk['MATKHAU'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST])) {
                    $newHash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                    if ($newHash) {
                        dbExecute("UPDATE TAIKHOAN SET MATKHAU=? WHERE MATK=?", [$newHash, $tk['MATK']], 'ss');
                    }
                }

                session_regenerate_id(true);
                $_SESSION['matk']  = $tk['MATK'];
                $_SESSION['email'] = $tk['EMAIL_TK'];
                $_SESSION['loai']  = $role;
                $_SESSION['macv']  = $tk['MACV'] ?? '';
                $_SESSION['tencv'] = $tk['TENCV'] ?? '';
                $_SESSION['tennv'] = $tk['TENNV'] ?? $tk['TENKH'] ?? 'Người dùng';
                $_SESSION['manv']  = $tk['MANV'] ?? '';
                $_SESSION['makh']  = $tk['MAKH'] ?? '';
                redirectByRole($role);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi"<?= themeHtmlAttr() ?>>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?= themeHead() ?>
  <title>Đăng nhập | NEXUS System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-primary);min-height:100vh;
      display:flex;align-items:center;justify-content:center;}

    .page{width:100%;max-width:440px;padding:24px 16px;}

    /* Card */
    .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:32px;}

    /* Logo */
    .logo{display:flex;flex-direction:column;align-items:center;gap:6px;margin-bottom:24px;}
    .logo .s{font-size:12px;color:var(--text-muted);}
    .theme-corner{position:fixed;top:16px;right:16px;}
    .logo-ico{width:40px;height:40px;border-radius:10px;background:var(--blue-solid);
      display:flex;align-items:center;justify-content:center;
      font-size:18px;font-weight:700;color:#fff;}
    .logo-text .n{font-size:18px;font-weight:700;color:var(--text-primary);}
    .logo-text .s{font-size:12px;color:var(--text-muted);margin-top:1px;}

    /* Heading */
    .hd{text-align:center;margin-bottom:20px;}
    .hd h1{font-size:20px;font-weight:600;color:var(--text-primary);margin-bottom:4px;}
    .hd p{font-size:13px;color:var(--text-muted);}

    /* Alert */
    .alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;
      display:flex;align-items:center;gap:9px;}
    .alert-err{background:#fef2f2;border:1px solid #ffc9c9;color:#c10007;}
    .alert-ok{background:#ecfdf5;border:1px solid #a4f4cf;color:#007a55;}

    /* Form */
    .fg{margin-bottom:14px;}
    .fg label{display:block;font-size:13px;font-weight:500;color:var(--text-primary);margin-bottom:6px;}
    .iw{position:relative;}
    .iw-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none;}
    .iw input{width:100%;height:40px;padding:0 12px 0 38px;
      background:var(--bg-card);border:1px solid var(--border-light);border-radius:8px;
      color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s;}
    .iw input:focus{border-color:var(--blue);box-shadow:0 0 0 2px rgba(58,86,228,.12);}
    .iw input::placeholder{color:var(--text-muted);}

    /* Show/hide password */
    .eye{position:absolute;right:8px;top:50%;transform:translateY(-50%);
      width:32px;height:32px;display:flex;align-items:center;justify-content:center;
      background:none;border:none;border-radius:6px;cursor:pointer;font-size:14px;color:var(--text-muted);}
    .eye:hover{background:var(--bg-main);}

    /* Submit */
    .btn-sub{width:100%;height:40px;margin-top:6px;background:var(--blue-solid);
      color:#fff;border:none;border-radius:8px;
      font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:background-color .15s;}
    .btn-sub:hover{background:#2f47c4;}

    /* Bottom link */
    .bottom-link{text-align:center;margin-top:16px;font-size:13px;color:var(--text-muted);}
    .bottom-link a{color:var(--blue);text-decoration:none;font-weight:500;}
    .bottom-link a:hover{text-decoration:underline;}

    /* Divider */
    .divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:var(--text-muted);font-size:12px;}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

  </style>
</head>
<body>

<div class="theme-corner"><?= themeToggle() ?></div>
<div class="page">
  <div class="card">

    <!-- Logo -->
    <div class="logo">
      <?= themeLogo(52) ?>
      <div class="s">Hệ thống Quản lý Bán Máy Tính</div>
    </div>

    <!-- Heading -->
    <div class="hd">
      <h1>Chào mừng trở lại</h1>
      <p>Đăng nhập để truy cập theo vai trò của bạn</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-err"><?= icon('x') ?> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="" id="loginForm" autocomplete="off">
      <div class="fg">
        <label for="email">Email đăng nhập</label>
        <div class="iw">
          <span class="iw-ico"><?= icon('mail') ?></span>
          <input type="email" id="email" name="email"
                 placeholder="email@phongvu.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required autofocus/>
        </div>
      </div>

      <div class="fg">
        <label for="password">Mật khẩu</label>
        <div class="iw">
          <span class="iw-ico"><?= icon('lock') ?></span>
          <input type="password" id="password" name="password"
                 placeholder="••••••••" required/>
          <button type="button" class="eye" onclick="togglePass()" title="Hiện/Ẩn mật khẩu"><?= icon('eye') ?></button>
        </div>
      </div>

      <button type="submit" class="btn-sub">Đăng nhập vào hệ thống</button>
    </form>

    <!-- Link đăng ký -->
    <div class="divider">hoặc</div>
    <div class="bottom-link">
      Chưa có tài khoản?
      <a href="<?= BASE_URL ?>/auth/register.php">Đăng ký ngay →</a>
    </div>

  </div>
</div>

<script>
function togglePass() {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
