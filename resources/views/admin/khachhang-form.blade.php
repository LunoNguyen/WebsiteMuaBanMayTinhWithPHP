@php($laSua = $kh->exists)
@extends('layouts.admin', ['title' => $laSua ? 'Sửa khách hàng' : 'Thêm khách hàng', 'breadcrumb' => ['Quản lý', 'Khách hàng', $laSua ? $kh->MAKH : 'Thêm mới']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>{{ $laSua ? 'Sửa khách hàng' : 'Thêm khách hàng' }}</h1>
            <p>{{ $laSua ? $kh->MAKH.' · '.$kh->TENKH : 'Mã khách hàng được tạo tự động khi lưu.' }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ $laSua ? route('admin.khachhang.show', $kh->MAKH) : route('admin.khachhang') }}" class="btn btn-outline">← Quay lại</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.khachhang.update', $kh->MAKH) : route('admin.khachhang.store') }}" class="card" style="max-width:820px">
        @csrf
        @if ($laSua) @method('PUT') @endif
        <div class="card-body">
            <div class="form-row">
                <x-truong name="TENKH" label="Họ tên" :value="$kh->TENKH" required maxlength="100" />
                <x-truong name="SDT_KH" label="Số điện thoại" :value="$kh->SDT_KH" inputmode="tel" />
            </div>
            <div class="form-row">
                <x-truong name="EMAIL_KH" label="Email" type="email" :value="$kh->EMAIL_KH" maxlength="100" />
                <x-truong name="NGAYSINH" label="Ngày sinh" type="date" :value="$kh->NGAYSINH" :max="today()->subDay()->format('Y-m-d')" />
            </div>
            <div class="form-row">
                <x-truong name="GIOITINH" label="Giới tính" :value="$kh->GIOITINH === null ? null : (int) $kh->GIOITINH" :options="[1 => 'Nam', 0 => 'Nữ']" />
                <x-truong name="DIACHI_KH" label="Địa chỉ" :value="$kh->DIACHI_KH" maxlength="200" />
            </div>
        </div>
        <div class="form-actions">
            <a href="{{ $laSua ? route('admin.khachhang.show', $kh->MAKH) : route('admin.khachhang') }}" class="btn btn-outline">Huỷ</a>
            <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu thay đổi' : 'Thêm khách hàng' }}</button>
        </div>
    </form>
@endsection
