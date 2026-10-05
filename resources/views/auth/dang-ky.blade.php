@extends('layouts.shop', ['title' => 'Đăng ký', 'anFlashLoi' => true])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Đăng ký</span></nav>

    <div class="s-auth">
        <section class="s-card s-auth-card">
            @if (session('dang_ky_email'))
                <div class="s-auth-done">
                    <span>{!! icon('check-circle', 40) !!}</span>
                    <h1>Đăng ký thành công</h1>
                    <p class="s-auth-sub">Tài khoản <strong>{{ session('dang_ky_email') }}</strong> đã được tạo. Bạn có thể đăng nhập ngay.</p>
                    <a href="{{ route('login', array_filter(['tiep' => request('tiep')])) }}" class="btn btn-primary btn-lg btn-block">Đăng nhập</a>
                </div>
            @else
                <h1>Tạo tài khoản</h1>
                <p class="s-auth-sub">Đăng ký để mua hàng, dùng mã giảm giá và theo dõi đơn.</p>

                <form method="POST" action="{{ route('register.store') }}" class="s-form" id="regForm">
                    @csrf
                    <div class="s-field">
                        <label for="hoten">Họ và tên <span class="req">*</span></label>
                        <input type="text" id="hoten" name="hoten" value="{{ old('hoten') }}" required maxlength="100" autofocus autocomplete="name"
                               @class(['s-input', 'is-invalid' => $errors->has('hoten')])>
                        @error('hoten') <div class="s-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="row2">
                        <div class="s-field">
                            <label for="email">Email <span class="req">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                   @class(['s-input', 'is-invalid' => $errors->has('email')])>
                            @error('email') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                        <div class="s-field">
                            <label for="sdt">Số điện thoại</label>
                            <input type="tel" id="sdt" name="sdt" value="{{ old('sdt') }}" maxlength="15" inputmode="tel" autocomplete="tel"
                                   @class(['s-input', 'is-invalid' => $errors->has('sdt')])>
                            @error('sdt') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row2">
                        <div class="s-field">
                            <label for="regPass">Mật khẩu <span class="req">*</span></label>
                            <div class="s-pass">
                                <input type="password" id="regPass" name="password" required minlength="6" autocomplete="new-password"
                                       @class(['s-input', 'is-invalid' => $errors->has('password')])>
                                <button type="button" data-hien-mk="regPass" aria-label="Hiện mật khẩu">{!! icon('eye', 18) !!}</button>
                            </div>
                            <div class="s-strength" aria-hidden="true"><span id="strFill"></span></div>
                            <div class="s-hint" id="strLbl">Ít nhất 6 ký tự.</div>
                            @error('password') <div class="s-err">{{ $message }}</div> @enderror
                        </div>
                        <div class="s-field">
                            <label for="regPass2">Nhập lại mật khẩu <span class="req">*</span></label>
                            <div class="s-pass">
                                <input type="password" id="regPass2" name="password_confirmation" required autocomplete="new-password" class="s-input">
                                <button type="button" data-hien-mk="regPass2" aria-label="Hiện mật khẩu">{!! icon('eye', 18) !!}</button>
                            </div>
                            <div class="s-hint" id="matchMsg"></div>
                        </div>
                    </div>

                    <label class="s-check s-terms">
                        <input type="checkbox" id="terms" name="terms" value="1" required @checked(old('terms'))>
                        <span>Tôi đồng ý với điều khoản dịch vụ và chính sách bảo mật của NEXUS</span>
                    </label>
                    @error('terms') <div class="s-err">{{ $message }}</div> @enderror

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Tạo tài khoản</button>
                </form>

                <p class="s-auth-switch">Đã có tài khoản? <a href="{{ route('login', array_filter(['tiep' => request('tiep')])) }}">Đăng nhập</a></p>
            @endif
        </section>

        @include('partials.shop.loi-ich-tai-khoan')
    </div>
@endsection

@push('scripts')
<script>
(function () {
  var p1 = document.getElementById('regPass');
  if (!p1) return;
  var p2 = document.getElementById('regPass2');
  var muc = [
    ['20%', 'var(--red)', 'Rất yếu'], ['40%', 'var(--orange)', 'Yếu'], ['60%', 'var(--orange)', 'Trung bình'],
    ['80%', 'var(--green)', 'Mạnh'], ['100%', 'var(--green)', 'Rất mạnh']
  ];
  p1.addEventListener('input', function () {
    var v = p1.value, d = 0;
    if (v.length >= 6) d++;
    if (v.length >= 10) d++;
    if (/[A-Z]/.test(v)) d++;
    if (/[0-9]/.test(v)) d++;
    if (/[^A-Za-z0-9]/.test(v)) d++;
    var m = muc[Math.min(d, 4)];
    var thanh = document.getElementById('strFill');
    thanh.style.width = v ? m[0] : '0';
    thanh.style.background = m[1];
    document.getElementById('strLbl').textContent = v ? 'Độ mạnh: ' + m[2] : 'Ít nhất 6 ký tự.';
  });
  p2.addEventListener('input', function () {
    var el = document.getElementById('matchMsg');
    if (!p2.value) { el.textContent = ''; return; }
    var khop = p1.value === p2.value;
    el.textContent = khop ? 'Mật khẩu khớp' : 'Mật khẩu chưa khớp';
    el.style.color = khop ? 'var(--green)' : 'var(--red)';
  });
})();
</script>
@endpush
