<?php
// ================================================================
// auth/register.php — Trang đăng ký tài khoản Khách hàng
// ================================================================
require_once __DIR__ . '/../config/config.php';

// Đã đăng nhập → đi thẳng vào hệ thống
if (!empty($_SESSION['matk'])) {
    redirectByRole($_SESSION['loai'] ?? '');
}

$msg     = '';
$msgType = 'error';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = trim($_POST['hoten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sdt   = trim($_POST['sdt']   ?? '');
    $pass  = $_POST['password']         ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    // Validate
    if (!$hoten || !$email || !$pass) {
        $msg = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Email không hợp lệ!';
    } elseif (strlen($pass) < 6) {
        $msg = 'Mật khẩu phải ít nhất 6 ký tự!';
    } elseif (strlen($pass) > 72) {
        // bcrypt chỉ dùng 72 byte đầu, phần dư sẽ bị bỏ qua khi so khớp
        $msg = 'Mật khẩu quá dài (tối đa 72 byte)!';
    } elseif ($pass !== $pass2) {
        $msg = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra email đã tồn tại
        $existing = dbFetchOne("SELECT MATK FROM TAIKHOAN WHERE EMAIL_TK=?", [$email], 's');
        if ($existing) {
            $msg = 'Email này đã được sử dụng. Vui lòng dùng email khác!';
        } else {
            // Tạo mã KH mới
            $lastKH = dbFetchOne("SELECT MAKH FROM KHACHHANG ORDER BY MAKH DESC LIMIT 1");
            $soMoi  = 1;
            if ($lastKH) {
                preg_match('/\d+/', $lastKH['MAKH'], $m);
                $soMoi = (intval($m[0] ?? 0)) + 1;
            }
            $makh  = 'KH' . str_pad($soMoi, 4, '0', STR_PAD_LEFT);
            $matk  = 'TK' . str_pad($soMoi + 100, 4, '0', STR_PAD_LEFT);
            // Mã hóa mật khẩu bằng bcrypt (chuỗi 60 ký tự, đã gồm salt ngẫu nhiên)
            $hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

            try {
                if ($hash === false) {
                    throw new Exception('Không mã hóa được mật khẩu');
                }
                // Insert KHACHHANG
                dbExecute(
                    "INSERT INTO KHACHHANG (MAKH, TENKH, SDT_KH, EMAIL_KH) VALUES (?,?,?,?)",
                    [$makh, $hoten, $sdt, $email], 'ssss'
                );
                // Insert TAIKHOAN
                dbExecute(
                    "INSERT INTO TAIKHOAN (MATK, EMAIL_TK, MATKHAU, LOAI_TAIKHOAN, TRANGTHAI, MAKH) VALUES (?,?,?,'KhachHang','HoatDong',?)",
                    [$matk, $email, $hash, $makh], 'ssss'
                );
                $success = true;
                $msgType = 'ok';
                $msg     = "Đăng ký thành công! Tài khoản <strong>$email</strong> đã được tạo. Vui lòng đăng nhập.";
            } catch (Exception $e) {
                $msg = 'Đã xảy ra lỗi. Vui lòng thử lại!';
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
  <title>Đăng ký | NEXUS System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-primary);min-height:100vh;
      display:flex;align-items:center;justify-content:center;}

    .page{width:100%;max-width:480px;padding:24px 16px;}

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

    /* Role quick-fill tabs */
    .rtabs{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:20px;}
    .rt{padding:9px 4px;border-radius:8px;border:1px solid var(--border-light);background:var(--bg-card);
      text-align:center;cursor:pointer;transition:background-color .15s,border-color .15s;user-select:none;}
    .rt:hover{background:var(--bg-main);}
    .rt.on{border-color:var(--blue);background:rgba(58,86,228,.06);}
    .rt-ico{font-size:16px;display:block;margin-bottom:3px;}
    .rt-lbl{font-size:12px;font-weight:500;color:var(--text-secondary);}
    .rt.on .rt-lbl{color:var(--blue);}

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

    /* Demo box */
    .demo{margin-top:18px;border-radius:8px;overflow:hidden;border:1px solid var(--border);}
    .demo-hd{padding:9px 14px;background:var(--bg-card-hover);font-size:12px;font-weight:500;
      color:var(--text-secondary);display:flex;align-items:center;gap:7px;cursor:pointer;user-select:none;
      border-bottom:1px solid transparent;}
    .demo-hd:hover{background:var(--bg-main);}
    .demo-body{display:none;background:var(--bg-card);}
    .demo-row{display:flex;align-items:center;justify-content:space-between;gap:8px;
      padding:8px 14px;border-bottom:1px solid var(--border);
      font-size:12px;cursor:pointer;transition:background-color .15s;}
    .demo-row:last-child{border-bottom:none;}
    .demo-row:hover{background:var(--bg-card-hover);}
    .dr-left{display:flex;align-items:center;gap:7px;}
    .dbg{padding:1px 7px;border-radius:4px;font-size:11px;font-weight:500;}
    .dr-cred{font-family:monospace;font-size:11px;color:var(--text-muted);}
    .dr-fill{font-size:11px;font-weight:500;color:var(--blue);white-space:nowrap;}
    /* Form 2 cột */
    .frow{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .fg.full{grid-column:1/-1;}
    .fg label .req{color:var(--red);margin-left:2px;}
    .iw input.err{border-color:#fb2c36;}
    @media(max-width:480px){.frow{grid-template-columns:1fr;gap:0;}}
    /* Độ mạnh mật khẩu */
    .strength{margin-top:6px;}
    .str-bar{height:4px;background:var(--border);border-radius:2px;overflow:hidden;}
    .str-fill{height:100%;border-radius:2px;transition:width .3s,background-color .3s;}
    .str-lbl{font-size:12px;color:var(--text-muted);margin-top:4px;}
    /* Điều khoản */
    .terms{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:var(--text-secondary);margin:4px 0 16px;}
    .terms input[type=checkbox]{margin-top:1px;accent-color:var(--blue);width:18px;height:18px;flex-shrink:0;cursor:pointer;}
    .terms a{color:var(--blue);text-decoration:none;}
    .terms a:hover{text-decoration:underline;}
    .btn-sub:disabled{opacity:.5;cursor:not-allowed;}
    /* Đăng ký thành công */
    .success-box{text-align:center;padding:20px 0;}
    .success-ico{font-size:48px;margin-bottom:14px;}
    .success-box h2{font-size:18px;font-weight:600;color:#007a55;margin-bottom:6px;}
    .success-box p{font-size:13px;color:var(--text-muted);margin-bottom:20px;}
    .btn-login-go{display:inline-flex;align-items:center;gap:7px;height:40px;padding:0 24px;
      background:var(--blue-solid);color:#fff;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;transition:background-color .15s;}
    .btn-login-go:hover{background:#2f47c4;}
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

    <?php if ($success): ?>
    <!-- Success state -->
    <div class="success-box">
      <div class="success-ico"><?= icon('check-circle') ?></div>
      <h2>Đăng ký thành công!</h2>
      <p>Tài khoản <strong style="color:#e6edf3"><?= htmlspecialchars($_POST['email']??'') ?></strong><br>đã được tạo. Bạn có thể đăng nhập ngay.</p>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn-login-go">Đăng nhập ngay</a>
    </div>

    <?php else: ?>

    <!-- Heading -->
    <div class="hd">
      <h1>Tạo tài khoản mới 🚀</h1>
      <p>Đăng ký để mua sắm tại NEXUS Store</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-err"><?= icon('x') ?> <?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="regForm" novalidate>
      <div class="frow">
        <!-- Họ tên -->
        <div class="fg full">
          <label>Họ và tên <span class="req">*</span></label>
          <div class="iw">
            <span class="iw-ico"><?= icon('user') ?></span>
            <input type="text" name="hoten" id="hoten"
                   placeholder="Nguyễn Văn A"
                   value="<?= htmlspecialchars($_POST['hoten'] ?? '') ?>"
                   required maxlength="100" autofocus/>
          </div>
        </div>

        <!-- Email -->
        <div class="fg">
          <label>Email <span class="req">*</span></label>
          <div class="iw">
            <span class="iw-ico"><?= icon('mail') ?></span>
            <input type="email" name="email" id="regEmail"
                   placeholder="email@gmail.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required/>
          </div>
        </div>

        <!-- SĐT -->
        <div class="fg">
          <label>Số điện thoại</label>
          <div class="iw">
            <span class="iw-ico"><?= icon('smartphone') ?></span>
            <input type="tel" name="sdt" id="sdt"
                   placeholder="0912 345 678"
                   value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"
                   maxlength="15"/>
          </div>
        </div>

        <!-- Password -->
        <div class="fg">
          <label>Mật khẩu <span class="req">*</span></label>
          <div class="iw">
            <span class="iw-ico"><?= icon('lock') ?></span>
            <input type="password" name="password" id="regPass"
                   placeholder="Tối thiểu 6 ký tự"
                   required minlength="6"
                   oninput="checkStrength(this.value)"/>
            <button type="button" class="eye" onclick="togglePass('regPass')"><?= icon('eye') ?></button>
          </div>
          <div class="strength">
            <div class="str-bar"><div class="str-fill" id="strFill" style="width:0"></div></div>
            <div class="str-lbl" id="strLbl"></div>
          </div>
        </div>

        <!-- Confirm -->
        <div class="fg">
          <label>Xác nhận mật khẩu <span class="req">*</span></label>
          <div class="iw">
            <span class="iw-ico"><?= icon('lock') ?></span>
            <input type="password" name="password_confirm" id="regPass2"
                   placeholder="Nhập lại mật khẩu"
                   required
                   oninput="checkMatch()"/>
            <button type="button" class="eye" onclick="togglePass('regPass2')"><?= icon('eye') ?></button>
          </div>
          <div id="matchMsg" style="font-size:10px;margin-top:5px;"></div>
        </div>
      </div>

      <!-- Terms -->
      <div class="terms">
        <input type="checkbox" id="terms" name="terms" required/>
        <label for="terms">
          Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a>
          và <a href="#">Chính sách bảo mật</a> của NEXUS
        </label>
      </div>

      <button type="submit" class="btn-sub" id="submitBtn">
        Tạo tài khoản
      </button>
    </form>

    <div class="divider">hoặc</div>
    <div class="bottom-link">
      Đã có tài khoản?
      <a href="<?= BASE_URL ?>/auth/login.php">Đăng nhập →</a>
    </div>

    <?php endif; ?>
  </div>
</div>

<script>
const strLevels = [
  {w:'20%', bg:'#c81e1e', t:'Rất yếu'},
  {w:'40%', bg:'#b45309', t:'Yếu'},
  {w:'60%', bg:'#a16207', t:'Trung bình'},
  {w:'80%', bg:'#15803d', t:'Mạnh'},
  {w:'100%',bg:'#047857', t:'Rất mạnh'},
];
function checkStrength(v) {
  let sc = 0;
  if(v.length >= 6) sc++;
  if(v.length >= 10) sc++;
  if(/[A-Z]/.test(v)) sc++;
  if(/[0-9]/.test(v)) sc++;
  if(/[^A-Za-z0-9]/.test(v)) sc++;
  const l = strLevels[Math.min(sc, 4)];
  document.getElementById('strFill').style.width = v ? l.w : '0';
  document.getElementById('strFill').style.background = l.bg;
  document.getElementById('strLbl').textContent = v ? l.t : '';
}
function checkMatch() {
  const p1 = document.getElementById('regPass').value;
  const p2 = document.getElementById('regPass2').value;
  const el = document.getElementById('matchMsg');
  if(!p2) { el.textContent=''; return; }
  el.textContent = p1===p2 ? '✅ Mật khẩu khớp' : '❌ Không khớp';
  el.style.color = p1===p2 ? '#4ade80' : '#f87171';
}
function togglePass(id) {
  const p = document.getElementById(id);
  p.type = p.type==='password' ? 'text' : 'password';
}
// Disable submit if terms unchecked
document.getElementById('regForm')?.addEventListener('submit', function(e) {
  if(!document.getElementById('terms').checked) {
    e.preventDefault();
    alert('Vui lòng đồng ý với điều khoản dịch vụ!');
  }
});
</script>
</body>
</html>
