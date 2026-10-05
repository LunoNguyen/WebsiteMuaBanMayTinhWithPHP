@extends('layouts.auth', ['title' => 'Đăng ký'])

@section('content')
    @if (session('dang_ky_email'))
        <div class="success-box">
            <div class="success-ico">{!! icon('check-circle') !!}</div>
            <h2>Đăng ký thành công!</h2>
            <p>Tài khoản <strong>{{ session('dang_ky_email') }}</strong><br>đã được tạo. Bạn có thể đăng nhập ngay.</p>
            <a href="{{ route('login') }}" class="btn-login-go">Đăng nhập ngay</a>
        </div>
    @else
        <div class="hd">
            <h1>Tạo tài khoản mới</h1>
            <p>Đăng ký để mua sắm tại NEXUS Store</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-err">{!! icon('x') !!} {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" id="regForm" novalidate>
            @csrf
            <div class="frow">
                <div class="fg full">
                    <label>Họ và tên <span class="req">*</span></label>
                    <div class="iw">
                        <span class="iw-ico">{!! icon('user') !!}</span>
                        <input type="text" name="hoten" id="hoten" placeholder="Nguyễn Văn A"
                               value="{{ old('hoten') }}" required maxlength="100" autofocus>
                    </div>
                </div>

                <div class="fg">
                    <label>Email <span class="req">*</span></label>
                    <div class="iw">
                        <span class="iw-ico">{!! icon('mail') !!}</span>
                        <input type="email" name="email" id="regEmail" placeholder="email@gmail.com"
                               value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="fg">
                    <label>Số điện thoại</label>
                    <div class="iw">
                        <span class="iw-ico">{!! icon('smartphone') !!}</span>
                        <input type="tel" name="sdt" id="sdt" placeholder="0912 345 678"
                               value="{{ old('sdt') }}" maxlength="15">
                    </div>
                </div>

                <div class="fg">
                    <label>Mật khẩu <span class="req">*</span></label>
                    <div class="iw">
                        <span class="iw-ico">{!! icon('lock') !!}</span>
                        <input type="password" name="password" id="regPass" placeholder="Tối thiểu 6 ký tự"
                               required minlength="6" oninput="checkStrength(this.value)">
                        <button type="button" class="eye" onclick="togglePass('regPass')">{!! icon('eye') !!}</button>
                    </div>
                    <div class="strength">
                        <div class="str-bar"><div class="str-fill" id="strFill" style="width:0"></div></div>
                        <div class="str-lbl" id="strLbl"></div>
                    </div>
                </div>

                <div class="fg">
                    <label>Xác nhận mật khẩu <span class="req">*</span></label>
                    <div class="iw">
                        <span class="iw-ico">{!! icon('lock') !!}</span>
                        <input type="password" name="password_confirmation" id="regPass2" placeholder="Nhập lại mật khẩu"
                               required oninput="checkMatch()">
                        <button type="button" class="eye" onclick="togglePass('regPass2')">{!! icon('eye') !!}</button>
                    </div>
                    <div id="matchMsg" style="font-size:12px;margin-top:5px;"></div>
                </div>
            </div>

            <div class="terms">
                <input type="checkbox" id="terms" name="terms" value="1" required @checked(old('terms'))>
                <label for="terms">
                    Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a>
                    và <a href="#">Chính sách bảo mật</a> của NEXUS
                </label>
            </div>

            <button type="submit" class="btn-sub" id="submitBtn">Tạo tài khoản</button>
        </form>

        <div class="divider">hoặc</div>
        <div class="bottom-link">
            Đã có tài khoản?
            <a href="{{ route('login') }}">Đăng nhập →</a>
        </div>
    @endif
@endsection

@push('scripts')
<script>
const strLevels = [
    {w: '20%', bg: 'var(--red)', t: 'Rất yếu'},
    {w: '40%', bg: 'var(--orange)', t: 'Yếu'},
    {w: '60%', bg: '#a16207', t: 'Trung bình'},
    {w: '80%', bg: 'var(--green)', t: 'Mạnh'},
    {w: '100%', bg: '#047857', t: 'Rất mạnh'},
];
function checkStrength(v) {
    let sc = 0;
    if (v.length >= 6) sc++;
    if (v.length >= 10) sc++;
    if (/[A-Z]/.test(v)) sc++;
    if (/[0-9]/.test(v)) sc++;
    if (/[^A-Za-z0-9]/.test(v)) sc++;
    const l = strLevels[Math.min(sc, 4)];
    document.getElementById('strFill').style.width = v ? l.w : '0';
    document.getElementById('strFill').style.background = l.bg;
    document.getElementById('strLbl').textContent = v ? l.t : '';
}
function checkMatch() {
    const p1 = document.getElementById('regPass').value;
    const p2 = document.getElementById('regPass2').value;
    const el = document.getElementById('matchMsg');
    if (!p2) { el.textContent = ''; return; }
    el.textContent = p1 === p2 ? 'Mật khẩu khớp' : 'Mật khẩu không khớp';
    el.style.color = p1 === p2 ? 'var(--green)' : 'var(--red)';
}
document.getElementById('regForm')?.addEventListener('submit', function (e) {
    if (!document.getElementById('terms').checked) {
        e.preventDefault();
        alert('Vui lòng đồng ý với điều khoản dịch vụ!');
    }
});
</script>
@endpush
