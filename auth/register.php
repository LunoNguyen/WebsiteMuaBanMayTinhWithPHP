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
            $hash  = password_hash($pass, PASSWORD_DEFAULT);

            try {
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
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng ký | NEXUS System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',sans-serif;background:#0d1117;color:#e6edf3;min-height:100vh;
      display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;padding:20px 0;}
    .bg-orb{position:fixed;border-radius:50%;filter:blur(80px);z-index:0;pointer-events:none;}
    .bg-orb1{width:500px;height:500px;top:-100px;right:-150px;background:rgba(34,197,94,.1);}
    .bg-orb2{width:400px;height:400px;bottom:-80px;left:-100px;background:rgba(79,110,247,.09);}
    .grid{position:fixed;inset:0;z-index:0;
      background-image:radial-gradient(rgba(34,197,94,.05) 1px,transparent 1px);
      background-size:28px 28px;}
    .page{position:relative;z-index:1;width:100%;max-width:480px;padding:20px;}
    .card{background:rgba(22,27,34,.95);border:1px solid #30363d;border-radius:20px;
      padding:36px 40px;backdrop-filter:blur(24px);
      box-shadow:0 0 0 1px rgba(255,255,255,.03),0 24px 64px rgba(0,0,0,.6);
      animation:up .4s cubic-bezier(.16,1,.3,1);}
    @keyframes up{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .logo{display:flex;align-items:center;gap:13px;justify-content:center;margin-bottom:26px;}
    .logo-ico{width:46px;height:46px;border-radius:13px;
      background:linear-gradient(135deg,#22c55e,#16a34a);
      display:flex;align-items:center;justify-content:center;
      font-size:22px;font-weight:900;color:#fff;
      box-shadow:0 8px 24px rgba(34,197,94,.4);}
    .logo-text .n{font-size:21px;font-weight:900;color:#e6edf3;}
    .logo-text .s{font-size:11px;color:#8b949e;margin-top:1px;}
    .hd{text-align:center;margin-bottom:22px;}
    .hd h1{font-size:19px;font-weight:800;color:#e6edf3;margin-bottom:4px;}
    .hd p{font-size:12.5px;color:#8b949e;}
    /* Alert */
    .alert{padding:11px 14px;border-radius:9px;font-size:13px;margin-bottom:16px;display:flex;align-items:flex-start;gap:9px;}
    .alert-err{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#f87171;}
    .alert-ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;}
    /* Form 2-col */
    .frow{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .fg{margin-bottom:14px;}
    .fg.full{grid-column:1/-1;}
    .fg label{display:block;font-size:10.5px;font-weight:700;color:#8b949e;
      text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;}
    .fg label .req{color:#ef4444;margin-left:2px;}
    .iw{position:relative;}
    .iw-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;}
    .iw input{width:100%;padding:11px 13px 11px 42px;
      background:rgba(13,17,23,.8);border:1px solid #30363d;border-radius:10px;
      color:#e6edf3;font-family:inherit;font-size:14px;outline:none;transition:all .2s;}
    .iw input:focus{border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.12);}
    .iw input::placeholder{color:#484f58;}
    .iw input.err{border-color:rgba(239,68,68,.5);}
    .eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;font-size:15px;color:#8b949e;padding:2px;}
    /* Strength bar */
    .strength{margin-top:6px;}
    .str-bar{height:4px;background:#30363d;border-radius:2px;overflow:hidden;}
    .str-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;}
    .str-lbl{font-size:10px;color:#8b949e;margin-top:4px;}
    /* Terms */
    .terms{display:flex;align-items:flex-start;gap:9px;font-size:12px;color:#8b949e;margin:4px 0 16px;}
    .terms input[type=checkbox]{margin-top:2px;accent-color:#22c55e;width:14px;height:14px;cursor:pointer;}
    .terms a{color:#4ade80;text-decoration:none;}
    .terms a:hover{text-decoration:underline;}
    /* Submit */
    .btn-sub{width:100%;padding:13px;
      background:linear-gradient(135deg,#22c55e,#16a34a);
      color:#fff;border:none;border-radius:11px;
      font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;
      box-shadow:0 4px 16px rgba(34,197,94,.3);transition:all .2s;}
    .btn-sub:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(34,197,94,.45);}
    .btn-sub:active{transform:translateY(0);}
    .btn-sub:disabled{opacity:.5;cursor:not-allowed;transform:none;}
    /* Bottom link */
    .divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#484f58;font-size:11px;}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:#30363d;}
    .bottom-link{text-align:center;font-size:12.5px;color:#8b949e;}
    .bottom-link a{color:#7b93f7;text-decoration:none;font-weight:600;}
    .bottom-link a:hover{text-decoration:underline;}
    /* Success state */
    .success-box{text-align:center;padding:20px 0;}
    .success-ico{font-size:56px;margin-bottom:14px;animation:bounce .6s ease;}
    @keyframes bounce{0%,100%{transform:scale(1)}50%{transform:scale(1.15)}}
    .success-box h2{font-size:18px;font-weight:800;color:#4ade80;margin-bottom:6px;}
    .success-box p{font-size:13px;color:#8b949e;margin-bottom:20px;}
    .btn-login-go{display:inline-flex;align-items:center;gap:7px;padding:12px 28px;
      background:linear-gradient(135deg,#4f6ef7,#3a56e4);color:#fff;border-radius:11px;
      font-size:14px;font-weight:700;text-decoration:none;
      box-shadow:0 4px 16px rgba(79,110,247,.35);transition:all .2s;}
    .btn-login-go:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,110,247,.5);}
  </style>
</head>
<body>
<div class="bg-orb bg-orb1"></div>
<div class="bg-orb bg-orb2"></div>
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

    <?php if ($success): ?>
    <!-- Success state -->
    <div class="success-box">
      <div class="success-ico">🎉</div>
      <h2>Đăng ký thành công!</h2>
      <p>Tài khoản <strong style="color:#e6edf3"><?= htmlspecialchars($_POST['email']??'') ?></strong><br>đã được tạo. Bạn có thể đăng nhập ngay.</p>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn-login-go">🚀 Đăng nhập ngay</a>
    </div>

    <?php else: ?>

    <!-- Heading -->
    <div class="hd">
      <h1>Tạo tài khoản mới 🚀</h1>
      <p>Đăng ký để mua sắm tại NEXUS Store</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-err">❌ <?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="regForm" novalidate>
      <div class="frow">
        <!-- Họ tên -->
        <div class="fg full">
          <label>Họ và tên <span class="req">*</span></label>
          <div class="iw">
            <span class="iw-ico">👤</span>
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
            <span class="iw-ico">📧</span>
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
            <span class="iw-ico">📱</span>
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
            <span class="iw-ico">🔒</span>
            <input type="password" name="password" id="regPass"
                   placeholder="Tối thiểu 6 ký tự"
                   required minlength="6"
                   oninput="checkStrength(this.value)"/>
            <button type="button" class="eye" onclick="togglePass('regPass')">👁️</button>
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
            <span class="iw-ico">🔐</span>
            <input type="password" name="password_confirm" id="regPass2"
                   placeholder="Nhập lại mật khẩu"
                   required
                   oninput="checkMatch()"/>
            <button type="button" class="eye" onclick="togglePass('regPass2')">👁️</button>
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
        ✅ Tạo tài khoản
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
  {w:'20%', bg:'#ef4444', t:'Rất yếu'},
  {w:'40%', bg:'#f59e0b', t:'Yếu'},
  {w:'60%', bg:'#eab308', t:'Trung bình'},
  {w:'80%', bg:'#22c55e', t:'Mạnh'},
  {w:'100%',bg:'#10b981', t:'Rất mạnh'},
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
