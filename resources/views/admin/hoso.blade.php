@extends('layouts.admin', ['title' => 'Hồ sơ của tôi', 'breadcrumb' => ['Tài khoản', 'Hồ sơ']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Hồ sơ của tôi</h1>
            <p>{{ $taiKhoan->EMAIL_TK }}{{ $nv?->chucVu ? ' · '.$nv->chucVu->TENCV : '' }}</p>
        </div>
    </div>

    @include('partials.thong-bao')

    <div class="grid-2">
        @if ($nv)
            <form method="POST" action="{{ route('admin.hoso.update') }}" class="card">
                @csrf
                @method('PUT')
                <div class="card-header"><h3>Thông tin cá nhân</h3></div>
                <div class="card-body">
                    <x-truong name="TENNV" label="Họ tên" :value="$nv->TENNV" required maxlength="100" />
                    <x-truong name="SDT_NV" label="Số điện thoại" :value="$nv->SDT_NV" inputmode="tel" />
                    <x-truong name="DIACHI_NV" label="Địa chỉ" :value="$nv->DIACHI_NV" maxlength="200" />
                    <dl class="dl">
                        <dt>Mã nhân viên</dt><dd>{{ $nv->MANV }}</dd>
                        <dt>Ngày vào làm</dt><dd>{{ $nv->NGAYVAOLAM?->format('d/m/Y') ?? '—' }}</dd>
                    </dl>
                </div>
                <div class="form-actions"><button type="submit" class="btn btn-primary">Lưu thay đổi</button></div>
            </form>
        @endif

        <form method="POST" action="{{ route('admin.hoso.mat-khau') }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-header"><h3>Đổi mật khẩu</h3></div>
            <div class="card-body">
                @foreach ([['mat_khau_cu', 'Mật khẩu hiện tại', 'current-password'], ['password', 'Mật khẩu mới', 'new-password'], ['password_confirmation', 'Nhập lại mật khẩu mới', 'new-password']] as [$ten, $nhan, $tuDien])
                    <div class="form-group">
                        <label class="form-label" for="mk_{{ $ten }}">{{ $nhan }} <span class="req">*</span></label>
                        <input type="password" id="mk_{{ $ten }}" name="{{ $ten }}" autocomplete="{{ $tuDien }}" required
                               @class(['form-control', 'is-invalid' => $errors->doiMatKhau->has($ten)])>
                        @if ($errors->doiMatKhau->has($ten))
                            <div class="form-error">{{ $errors->doiMatKhau->first($ten) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-outline">Đổi mật khẩu</button></div>
        </form>
    </div>
@endsection
