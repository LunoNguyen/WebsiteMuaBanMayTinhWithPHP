@extends('layouts.admin', ['title' => 'Tài khoản', 'breadcrumb' => ['Hệ thống', 'Tài khoản']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Tài khoản</h1>
            <p>{{ formatNum($dem[0][2]) }} tài khoản đăng nhập của quản trị, nhân viên và khách hàng</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.taikhoan.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Tạo tài khoản</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" ten="loai" :dang="$loaifil" />

    <div class="card">
        <form method="GET" class="qt-toolbar">
            @if ($loaifil !== '')
                <input type="hidden" name="loai" value="{{ $loaifil }}">
            @endif
            <x-qt.tim :value="$search" placeholder="Tìm mã, email hoặc tên người dùng" />
            <x-qt.chon name="trangthai" :value="$ttfil" :options="['' => 'Mọi trạng thái'] + $ttLabel" />
        </form>

        @if (empty($taikhoan))
            <div class="qt-empty">
                {!! icon('key', 40) !!}
                <h3>Không có tài khoản nào khớp điều kiện lọc</h3>
                <a href="{{ route('admin.taikhoan') }}" class="btn btn-outline">Xoá bộ lọc</a>
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Tài khoản</th><th>Loại</th><th>Người dùng</th><th>Trạng thái</th><th>Ngày tạo</th><th>Cập nhật</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($taikhoan as $tk)
                            @php
                                $loai = $tk['LOAI_TAIKHOAN'];
                                $mauLoai = $loaiColor[$loai] ?? 'var(--text-muted)';
                            @endphp
                            <tr>
                                <td><span class="qt-ma">{{ $tk['MATK'] }}</span><span class="qt-sub">{{ $tk['EMAIL_TK'] }}</span></td>
                                <td><span class="tag-loai" style="--c:{{ $mauLoai }}">{!! icon($loaiIcon[$loai] ?? 'user', 13) !!} {{ $loaiNhan[$loai] ?? $loai }}</span></td>
                                <td>{{ $tk['TENKH'] ?? $tk['TENNV'] ?? '—' }}<span class="qt-sub">{{ $tk['TENCV'] ?? $tk['SDT_KH'] ?? '' }}</span></td>
                                <td><span class="badge-tt" style="--c:{{ $ttColor[$tk['TRANGTHAI']] ?? 'var(--text-secondary)' }}">{{ $ttLabel[$tk['TRANGTHAI']] ?? $tk['TRANGTHAI'] }}</span></td>
                                <td>{{ date('d/m/Y H:i', strtotime($tk['NGAYTAO'])) }}</td>
                                <td>{{ $tk['NGAY_CAPNHAT'] ? date('d/m/Y H:i', strtotime($tk['NGAY_CAPNHAT'])) : '—' }}</td>
                                <td>
                                    <div class="qt-actions">
                                        <a href="{{ route('admin.taikhoan.edit', $tk['MATK']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $tk['EMAIL_TK'] }}">{!! icon('pencil', 15) !!}</a>
                                        @if ($loai !== 'Admin')
                                            <x-nut-hanh-dong :action="route('admin.taikhoan.trang-thai', $tk['MATK'])" method="PATCH" class="btn-icon"
                                                confirm="Thay đổi trạng thái tài khoản?"
                                                :title="$tk['TRANGTHAI'] === 'HoatDong' ? 'Khoá' : 'Mở khoá'" :aria-label="$tk['TRANGTHAI'] === 'HoatDong' ? 'Khoá' : 'Mở khoá'">
                                                {!! $tk['TRANGTHAI'] === 'HoatDong' ? icon('lock', 15) : icon('unlock', 15) !!}
                                            </x-nut-hanh-dong>
                                        @endif
                                        @if ($tk['MATK'] !== auth()->id())
                                            <x-nut-hanh-dong :action="route('admin.taikhoan.destroy', $tk['MATK'])" method="DELETE" class="btn-icon" title="Xoá" aria-label="Xoá {{ $tk['EMAIL_TK'] }}"
                                                :confirm="'Xoá tài khoản '.$tk['EMAIL_TK'].'? Tài khoản đã có đơn hàng thì chỉ khoá được.'">{!! icon('trash', 15) !!}</x-nut-hanh-dong>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="tài khoản" />
        @endif
    </div>
@endsection
