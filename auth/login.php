<?php
// ================================================================
// auth/login.php — Trang đăng nhập CHUNG cho tất cả 4 Role
// ================================================================
require_once __DIR__ . '/../config/config.php';

// Nếu đã đăng nhập → redirect đúng role
if (!empty($_SESSION['matk'])) {
    redirectByRole($_SESSION['loai'] ?? '');
}

$msg     = '';
$msgType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $tk = dbFetchOne("
            SELECT tk.*, nv.TENNV, nv.MANV, nv.MACV, kh.TENKH, kh.MAKH
            FROM TAIKHOAN tk
            LEFT JOIN NHANVIEN  nv ON tk.MANV = nv.MANV
            LEFT JOIN KHACHHANG kh ON tk.MAKH = kh.MAKH
            WHERE tk.EMAIL_TK=? AND tk.TRANGTHAI='HoatDong'
        ", [$email], 's');

        $authenticated = false;
        if ($tk) {
            if (password_verify($pass, $tk['MATKHAU']) || md5($pass) === $tk['MATKHAU'] || $pass === $tk['MATKHAU']) {
                $authenticated = true;
            }
        }

        // Demo fallback cho test nhanh
        if (!$authenticated) {
            $demos = [
                'admin@phongvu.com'       => ['pass'=>'admin123',   'loai'=>'Admin',       'tennv'=>'Nguyễn Ngân Lượng', 'manv'=>'NV001','matk'=>'TK_ADMIN1'],
                'banhang@phongvu.com'     => ['pass'=>'banhang123', 'loai'=>'NhanVienBan', 'tennv'=>'Lê Thị Hồng',       'manv'=>'NV002','matk'=>'TK_BAN1'],
                'qly.banhang@phongvu.com' => ['pass'=>'banhang123', 'loai'=>'NhanVienBan', 'tennv'=>'Trần Hữu Quản',    'manv'=>'NV002','matk'=>'TK_NV001'],
                'kho@phongvu.com'         => ['pass'=>'kho123',     'loai'=>'NhanVienKho', 'tennv'=>'Trần Quốc Bảo',     'manv'=>'NV004','matk'=>'TK_KHO1'],
                'qly.kho@phongvu.com'     => ['pass'=>'kho123',     'loai'=>'NhanVienKho', 'tennv'=>'Nguyễn Thị Kho',    'manv'=>'NV003','matk'=>'TK_NV002'],
                'khach@gmail.com'         => ['pass'=>'khach123',   'loai'=>'KhachHang',   'tennv'=>'Khách hàng VIP',    'manv'=>'','matk'=>'TK_KH1'],
            ];
            if (isset($demos[$email]) && $demos[$email]['pass'] === $pass) {
                $d = $demos[$email];
                $_SESSION['matk']  = $d['matk'];
                $_SESSION['email'] = $email;
                $_SESSION['loai']  = $d['loai'];
                $_SESSION['tennv'] = $d['tennv'];
                $_SESSION['manv']  = $d['manv'];
                redirectByRole($d['loai']);
            }
        }

        if ($authenticated && $tk) {
            // Xác định chính xác vai trò
            $role = $tk['LOAI_TAIKHOAN'];
            if ($role === 'NhanVien') {
                $role = ($tk['MACV'] === 'CV003') ? 'NhanVienKho' : 'NhanVienBan';
            }

            $_SESSION['matk']  = $tk['MATK'];
            $_SESSION['email'] = $tk['EMAIL_TK'];
            $_SESSION['loai']  = $role;
            $_SESSION['tennv'] = $tk['TENNV'] ?? $tk['TENKH'] ?? 'Người dùng';
            $_SESSION['manv']  = $tk['MANV'] ?? '';
            $_SESSION['makh']  = $tk['MAKH'] ?? '';
            redirectByRole($role);
        } else {
            $msg = 'Email hoặc mật khẩu không đúng!';
        }
    } else {
        $msg = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng nhập | NEXUS System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:#0d1117;color:#e6edf3;min-height:100vh;
      display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;}

    /* Animated background */
    .bg-orb{position:fixed;border-radius:50%;filter:blur(80px);z-index:0;pointer-events:none;}
    .bg-orb1{width:500px;height:500px;top:-100px;left:-150px;background:rgba(79,110,247,.12);}
    .bg-orb2{width:400px;height:400px;bottom:-80px;right:-100px;background:rgba(139,92,246,.1);}
    .bg-orb3{width:300px;height:300px;top:50%;left:50%;background:rgba(34,197,94,.06);}
    .grid{position:fixed;inset:0;z-index:0;
      background-image:radial-gradient(rgba(79,110,247,.06) 1px,transparent 1px);
      background-size:28px 28px;}

    .page{position:relative;z-index:1;width:100%;max-width:460px;padding:20px;}

    /* Card */
    .card{background:rgba(22,27,34,.95);border:1px solid #30363d;border-radius:20px;
      padding:36px 40px;backdrop-filter:blur(24px);
      box-shadow:0 0 0 1px rgba(255,255,255,.03),0 24px 64px rgba(0,0,0,.6);
      animation:up .4s cubic-bezier(.16,1,.3,1);}
    @keyframes up{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

    /* Logo */
    .logo{display:flex;align-items:center;gap:13px;justify-content:center;margin-bottom:28px;}
    .logo-ico{width:46px;height:46px;border-radius:13px;
      background:linear-gradient(135deg,#4f6ef7 0%,#7c3aed 100%);
      display:flex;align-items:center;justify-content:center;
      font-size:22px;font-weight:900;color:#fff;
      box-shadow:0 8px 24px rgba(79,110,247,.4);}
    .logo-text .n{font-size:21px;font-weight:900;color:#e6edf3;letter-spacing:-.3px;}
    .logo-text .s{font-size:11px;color:#8b949e;margin-top:1px;}

    /* Heading */
    .hd{text-align:center;margin-bottom:22px;}
    .hd h1{font-size:20px;font-weight:800;color:#e6edf3;margin-bottom:4px;}
    .hd p{font-size:12.5px;color:#8b949e;}

    /* Role quick-fill tabs */
    .rtabs{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:20px;}
    .rt{padding:9px 4px;border-radius:9px;border:1px solid #30363d;background:rgba(28,35,51,.5);
      text-align:center;cursor:pointer;transition:all .15s;user-select:none;}
    .rt:hover{border-color:#4f6ef7;background:rgba(79,110,247,.06);}
    .rt.on{border-color:rgba(79,110,247,.5);background:rgba(79,110,247,.12);}
    .rt-ico{font-size:17px;display:block;margin-bottom:3px;}
    .rt-lbl{font-size:10.5px;font-weight:600;color:#8b949e;}
    .rt.on .rt-lbl{color:#7b93f7;}

    /* Alert */
    .alert{padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:16px;
      display:flex;align-items:center;gap:9px;}
    .alert-err{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#f87171;}
    .alert-ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;}

    /* Form */
    .fg{margin-bottom:14px;}
    .fg label{display:block;font-size:10.5px;font-weight:700;color:#8b949e;
      text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;}
    .iw{position:relative;}
    .iw-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;}
    .iw input{width:100%;padding:11px 13px 11px 42px;
      background:rgba(13,17,23,.8);border:1px solid #30363d;border-radius:10px;
      color:#e6edf3;font-family:inherit;font-size:14px;outline:none;transition:all .2s;}
    .iw input:focus{border-color:#4f6ef7;box-shadow:0 0 0 3px rgba(79,110,247,.15);}
    .iw input::placeholder{color:#484f58;}

    /* Show/hide password */
    .eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;font-size:15px;color:#8b949e;padding:2px;}

    /* Submit */
    .btn-sub{width:100%;padding:13px;margin-top:6px;
      background:linear-gradient(135deg,#4f6ef7,#3a56e4);
      color:#fff;border:none;border-radius:11px;
      font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;
      box-shadow:0 4px 16px rgba(79,110,247,.35);transition:all .2s;}
    .btn-sub:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,110,247,.5);}
    .btn-sub:active{transform:translateY(0);}

    /* Bottom link */
    .bottom-link{text-align:center;margin-top:16px;font-size:12.5px;color:#8b949e;}
    .bottom-link a{color:#7b93f7;text-decoration:none;font-weight:600;}
    .bottom-link a:hover{text-decoration:underline;}

    /* Divider */
    .divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#484f58;font-size:11px;}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:#30363d;}

    /* Demo box */
    .demo{margin-top:18px;border-radius:11px;overflow:hidden;border:1px solid #30363d;}
    .demo-hd{padding:9px 14px;background:rgba(79,110,247,.06);font-size:11px;font-weight:700;
      color:#7b93f7;display:flex;align-items:center;gap:7px;cursor:pointer;user-select:none;
      border-bottom:1px solid transparent;transition:border-color .15s;}
    .demo-hd:hover{border-color:#30363d;}
    .demo-body{display:none;background:rgba(13,17,23,.6);}
    .demo-row{display:flex;align-items:center;justify-content:space-between;
      padding:8px 14px;border-bottom:1px solid rgba(48,54,61,.6);
      font-size:11px;cursor:pointer;transition:background .1s;}
    .demo-row:last-child{border-bottom:none;}
    .demo-row:hover{background:rgba(79,110,247,.05);}
    .dr-left{display:flex;align-items:center;gap:7px;}
    .dbg{padding:1px 7px;border-radius:4px;font-size:10px;font-weight:700;}
    .dr-cred{font-family:monospace;font-size:10px;color:#484f58;transition:color .1s;}
    .demo-row:hover .dr-cred{color:#7b93f7;}
    .dr-fill{font-size:10px;font-weight:700;color:#4f6ef7;opacity:.7;}
    .demo-row:hover .dr-fill{opacity:1;}
  </style>
</head>
<body>
<div class="bg-orb bg-orb1"></div>
<div class="bg-orb bg-orb2"></div>
<div class="bg-orb bg-orb3"></div>
<div class="grid"></div>

<div class="page">
  <div class="card">

    <!-- Logo -->
    <div class="logo">
      <div class="logo-ico">N</div>
      <div class="logo-text">
        <div class="n">NEXUS</div>
        <div class="s">Hệ thống Quản lý Bán Máy Tính</div>
      </div>
    </div>

    <!-- Heading -->
    <div class="hd">
      <h1>Chào mừng trở lại 👋</h1>
      <p>Đăng nhập để truy cập theo vai trò của bạn</p>
    </div>

    <!-- Quick-fill role tabs -->
    <div class="rtabs">
      <div class="rt" id="tab0" onclick="fillDemo('admin@phongvu.com','admin123',0)">
        <span class="rt-ico">🛡️</span>
        <span class="rt-lbl">Admin</span>
      </div>
      <div class="rt" id="tab1" onclick="fillDemo('banhang@phongvu.com','banhang123',1)">
        <span class="rt-ico">💼</span>
        <span class="rt-lbl">Bán hàng</span>
      </div>
      <div class="rt" id="tab2" onclick="fillDemo('kho@phongvu.com','kho123',2)">
        <span class="rt-ico">📦</span>
        <span class="rt-lbl">NV Kho</span>
      </div>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-err">❌ <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="" id="loginForm" autocomplete="off">
      <div class="fg">
        <label for="email">Email đăng nhập</label>
        <div class="iw">
          <span class="iw-ico">📧</span>
          <input type="email" id="email" name="email"
                 placeholder="email@phongvu.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required autofocus/>
        </div>
      </div>

      <div class="fg">
        <label for="password">Mật khẩu</label>
        <div class="iw">
          <span class="iw-ico">🔒</span>
          <input type="password" id="password" name="password"
                 placeholder="••••••••" required/>
          <button type="button" class="eye" onclick="togglePass()" title="Hiện/Ẩn mật khẩu">👁️</button>
        </div>
      </div>

      <button type="submit" class="btn-sub">🚀 Đăng nhập vào hệ thống</button>
    </form>

    <!-- Link đăng ký -->
    <div class="divider">hoặc</div>
    <div class="bottom-link">
      Chưa có tài khoản?
      <a href="<?= BASE_URL ?>/auth/register.php">Đăng ký ngay →</a>
    </div>

    <!-- Demo accounts -->
    <div class="demo">
      <div class="demo-hd" onclick="toggleDemo(this)">
        <span>💡</span> Tài khoản Demo — Click để điền nhanh
        <span style="margin-left:auto" id="demoArrow">▼</span>
      </div>
      <div class="demo-body" id="demoBody">
        <?php
        $demoList = [
          ['🛡️','Admin',       'admin@phongvu.com',   'admin123',   '#4f6ef7'],
          ['💼','NV Bán hàng', 'banhang@phongvu.com', 'banhang123', '#22c55e'],
          ['📦','NV Kho',      'kho@phongvu.com',     'kho123',     '#8b5cf6'],
          ['👤','Khách hàng',  'khach@gmail.com',     'khach123',   '#f59e0b'],
        ];
        foreach ($demoList as [$ico, $lbl, $em, $pw, $col]):
        ?>
        <div class="demo-row" onclick="fillDemo('<?= $em ?>','<?= $pw ?>',-1)">
          <div class="dr-left">
            <span><?= $ico ?></span>
            <span class="dbg" style="background:<?= $col ?>18;color:<?= $col ?>"><?= $lbl ?></span>
          </div>
          <div class="dr-cred"><?= $em ?> / <?= $pw ?></div>
          <span class="dr-fill">↑ Điền</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
function fillDemo(email, pass, tabIdx) {
  document.getElementById('email').value    = email;
  document.getElementById('password').value = pass;
  document.querySelectorAll('.rt').forEach((t,i) => t.classList.toggle('on', i===tabIdx));
}
function toggleDemo(hd) {
  const b = document.getElementById('demoBody');
  const a = document.getElementById('demoArrow');
  const open = b.style.display !== 'block';
  b.style.display = open ? 'block' : 'none';
  a.textContent   = open ? '▲' : '▼';
  hd.style.borderBottomColor = open ? '#30363d' : 'transparent';
}
function togglePass() {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
}
window.addEventListener('load', () => {
  toggleDemo(document.querySelector('.demo-hd'));
});
</script>
</body>
</html>
