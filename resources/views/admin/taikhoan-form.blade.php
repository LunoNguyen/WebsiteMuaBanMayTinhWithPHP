@php
    $laSua = $tk->exists;
    $trangThai = ['HoatDong' => 'Hoạt động', 'KhoaTamThoi' => 'Khoá tạm thời', 'KhoaVinhVien' => 'Khoá vĩnh viễn'];
    $loai = old('LOAI_TAIKHOAN', $tk->LOAI_TAIKHOAN);
@endphp
@extends('layouts.admin', ['title' => $laSua ? 'Sửa tài khoản' : 'Tạo tài khoản', 'breadcrumb' => ['Quản lý', 'Tài khoản', $laSua ? $tk->MATK : 'Tạo mới']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('admin.taikhoan') }}" class="qt-back">{!! icon('arrow-left', 14) !!} Tài khoản</a>
            <h1>{{ $laSua ? 'Sửa tài khoản' : 'Tạo tài khoản' }}</h1>
            <p>{{ $laSua ? $tk->MATK.' · '.$tk->tenHienThi() : 'Mỗi nhân viên / khách hàng có một tài khoản đăng nhập.' }}</p>
        </div>
        <div class="page-header-right">
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.taikhoan.update', $tk->MATK) : route('admin.taikhoan.store') }}" class="card" style="max-width:720px">
        @csrf
        @if ($laSua) @method('PUT') @endif
        <div class="card-body">
            @if ($laSua)
                <dl class="dl" style="margin-bottom:20px">
                    <dt>Loại tài khoản</dt><dd>{{ ['Admin' => 'Quản trị', 'NhanVien' => 'Nhân viên', 'KhachHang' => 'Khách hàng'][$tk->LOAI_TAIKHOAN] }}</dd>
                    @if ($tk->nhanVien)
                        <dt>Nhân viên</dt><dd>{{ $tk->MANV }} · {{ $tk->nhanVien->TENNV }} ({{ $tk->nhanVien->chucVu?->TENCV }})</dd>
                    @endif
                    @if ($tk->khachHang)
                        <dt>Khách hàng</dt><dd><a href="{{ route('admin.khachhang.show', $tk->MAKH) }}" style="color:var(--blue)">{{ $tk->MAKH }} · {{ $tk->khachHang->TENKH }}</a></dd>
                    @endif
                    <dt>Ngày tạo</dt><dd>{{ $tk->NGAYTAO?->format('d/m/Y H:i') }}</dd>
                </dl>
            @else
                <x-truong name="LOAI_TAIKHOAN" label="Loại tài khoản" :value="$tk->LOAI_TAIKHOAN" required data-loai
                          :options="['NhanVien' => 'Nhân viên', 'Admin' => 'Quản trị', 'KhachHang' => 'Khách hàng']" />
                <div data-cho="nv" @if ($loai === 'KhachHang') hidden @endif>
                    <x-truong name="MANV" label="Nhân viên" :value="$tk->MANV"
                              :options="$nhanVienChuaCo->mapWithKeys(fn ($nv) => [$nv->MANV => $nv->MANV.' · '.$nv->TENNV])"
                              :hint="$nhanVienChuaCo->isEmpty() ? 'Mọi nhân viên đều đã có tài khoản.' : 'Chỉ hiện nhân viên chưa có tài khoản.'" />
                </div>
                <div data-cho="kh" @if ($loai !== 'KhachHang') hidden @endif>
                    <x-truong name="MAKH" label="Khách hàng" :value="$tk->MAKH"
                              :options="$khachHangChuaCo->mapWithKeys(fn ($kh) => [$kh->MAKH => $kh->MAKH.' · '.$kh->TENKH])"
                              :hint="$khachHangChuaCo->isEmpty() ? 'Mọi khách hàng đều đã có tài khoản.' : 'Chỉ hiện khách hàng chưa có tài khoản.'" />
                </div>
            @endif

            <div class="form-row">
                <x-truong name="EMAIL_TK" label="Email đăng nhập" type="email" :value="$tk->EMAIL_TK" required maxlength="100" autocomplete="off" />
                <x-truong name="TRANGTHAI" label="Trạng thái" :value="$tk->TRANGTHAI" required :options="$trangThai" />
            </div>
            <div class="form-row">
                <x-truong name="password" :label="$laSua ? 'Mật khẩu mới' : 'Mật khẩu'" type="password" :required="! $laSua"
                          minlength="6" maxlength="72" autocomplete="new-password" :hint="$laSua ? 'Để trống nếu giữ nguyên mật khẩu.' : 'Ít nhất 6 ký tự, lưu dạng bcrypt.'" />
                <x-truong name="password_confirmation" label="Nhập lại mật khẩu" type="password" :required="! $laSua" autocomplete="new-password" />
            </div>
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.taikhoan') }}" class="btn btn-outline">Huỷ</a>
            <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu thay đổi' : 'Tạo tài khoản' }}</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.querySelector('[data-loai]')?.addEventListener('change', function () {
  var khach = this.value === 'KhachHang';
  document.querySelector('[data-cho="nv"]').hidden = khach;
  document.querySelector('[data-cho="kh"]').hidden = !khach;
});
</script>
@endpush
