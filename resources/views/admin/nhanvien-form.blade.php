@php($laSua = $nv->exists)
@extends('layouts.admin', ['title' => $laSua ? 'Sửa nhân viên' : 'Thêm nhân viên', 'breadcrumb' => ['Quản lý', 'Nhân viên', $laSua ? $nv->MANV : 'Thêm mới']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('admin.nhanvien') }}" class="qt-back">{!! icon('arrow-left', 14) !!} Nhân viên</a>
            <h1>{{ $laSua ? 'Sửa nhân viên' : 'Thêm nhân viên' }}</h1>
            <p>{{ $laSua ? $nv->MANV.' · '.$nv->TENNV : 'Mã nhân viên được tạo tự động khi lưu.' }}</p>
        </div>
        <div class="page-header-right">
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.nhanvien.update', $nv->MANV) : route('admin.nhanvien.store') }}" class="card" style="max-width:820px">
        @csrf
        @if ($laSua) @method('PUT') @endif
        <div class="card-body">
            <div class="form-row">
                <x-truong name="TENNV" label="Họ tên" :value="$nv->TENNV" required maxlength="100" />
                <x-truong name="MACV" label="Chức vụ" :value="$nv->MACV" required :options="$chucvuList->pluck('TENCV', 'MACV')" />
            </div>
            <div class="form-row">
                <x-truong name="SDT_NV" label="Số điện thoại" :value="$nv->SDT_NV" inputmode="tel" />
                <x-truong name="EMAIL_NV" label="Email" type="email" :value="$nv->EMAIL_NV" maxlength="100" />
            </div>
            <x-truong name="DIACHI_NV" label="Địa chỉ" :value="$nv->DIACHI_NV" maxlength="200" />
            <div class="form-row">
                <x-truong name="NGAYVAOLAM" label="Ngày vào làm" type="date" :value="$nv->NGAYVAOLAM" :max="today()->format('Y-m-d')" />
                <x-truong name="TRANGTHAI" label="Trạng thái" :value="(int) $nv->TRANGTHAI" required :options="[1 => 'Đang làm việc', 0 => 'Nghỉ việc']" />
            </div>
            @if ($laSua && $nv->taiKhoan)
                <div class="form-hint">Tài khoản đăng nhập: <a href="{{ route('admin.taikhoan.edit', $nv->taiKhoan->MATK) }}" style="color:var(--blue)">{{ $nv->taiKhoan->EMAIL_TK }}</a></div>
            @endif
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.nhanvien') }}" class="btn btn-outline">Huỷ</a>
            <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu thay đổi' : 'Thêm nhân viên' }}</button>
        </div>
    </form>
@endsection
