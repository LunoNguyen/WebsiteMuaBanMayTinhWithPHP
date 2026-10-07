@extends('layouts.admin', ['title' => 'Nhân viên', 'breadcrumb' => ['Hệ thống', 'Nhân viên']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Nhân viên</h1>
            <p>{{ formatNum($dem[1][2]) }} đang làm việc · {{ formatNum($dem[2][2]) }} đã nghỉ</p>
        </div>
        <div class="page-header-right">
            <button type="button" class="btn btn-outline" onclick="exportTableCSV('nvTable','nhanvien')">{!! icon('download', 16) !!} Xuất CSV</button>
            <a href="{{ route('admin.nhanvien.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Thêm nhân viên</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" :dang="$trangthai" />

    <div class="card">
        <form method="GET" class="qt-toolbar">
            @if ($trangthai !== '')
                <input type="hidden" name="trangthai" value="{{ $trangthai }}">
            @endif
            <x-qt.tim :value="$search" placeholder="Tìm tên, mã hoặc email nhân viên" />
            <x-qt.chon name="macv" :value="$macv" :options="['' => 'Mọi chức vụ'] + $chucvuList" />
        </form>

        @if (empty($nhanvien))
            <div class="qt-empty">
                {!! icon('users', 40) !!}
                <h3>Không có nhân viên nào khớp điều kiện lọc</h3>
                <a href="{{ route('admin.nhanvien') }}" class="btn btn-outline">Xoá bộ lọc</a>
            </div>
        @else
            <div class="table-wrapper">
                <table id="nvTable">
                    <thead>
                        <tr><th>Nhân viên</th><th>Chức vụ</th><th>Liên hệ</th><th>Ngày vào làm</th><th class="num">Đơn đã xử lý</th><th>Trạng thái</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($nhanvien as $nv)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <span class="user-avatar" style="width:34px;height:34px;font-size:12px">{{ mb_strtoupper(mb_substr($nv['TENNV'], 0, 2)) }}</span>
                                        <span>
                                            <a href="{{ route('admin.nhanvien.edit', $nv['MANV']) }}" style="color:var(--text-primary);font-weight:600">{{ $nv['TENNV'] }}</a>
                                            <span class="qt-sub">{{ $nv['MANV'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $nv['TENCV'] ?? '—' }}</td>
                                <td>{{ $nv['SDT_NV'] ?: '—' }}<span class="qt-sub">{{ $nv['EMAIL_NV'] }}</span></td>
                                <td>{{ $nv['NGAYVAOLAM'] ? date('d/m/Y', strtotime($nv['NGAYVAOLAM'])) : '—' }}</td>
                                <td class="num">{{ formatNum($nv['so_hd'] ?? 0) }}</td>
                                <td>
                                    <span class="badge-tt" style="--c:{{ $nv['TRANGTHAI'] ? 'var(--green)' : 'var(--text-muted)' }}">{{ $nv['TRANGTHAI'] ? 'Đang làm việc' : 'Đã nghỉ' }}</span>
                                </td>
                                <td>
                                    <div class="qt-actions">
                                        <a href="{{ route('admin.nhanvien.edit', $nv['MANV']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $nv['TENNV'] }}">{!! icon('pencil', 15) !!}</a>
                                        <a href="{{ route('admin.taikhoan', ['q' => $nv['MANV']]) }}" class="btn-icon" title="Tài khoản" aria-label="Tài khoản của {{ $nv['TENNV'] }}">{!! icon('key', 15) !!}</a>
                                        <x-nut-hanh-dong :action="route('admin.nhanvien.trang-thai', $nv['MANV'])" method="PATCH" class="btn-icon"
                                            :confirm="$nv['TRANGTHAI'] ? 'Chuyển '.$nv['TENNV'].' sang đã nghỉ?' : 'Cho '.$nv['TENNV'].' làm việc lại?'"
                                            :title="$nv['TRANGTHAI'] ? 'Cho nghỉ việc' : 'Làm việc lại'" :aria-label="$nv['TRANGTHAI'] ? 'Cho nghỉ việc' : 'Làm việc lại'">
                                            {!! $nv['TRANGTHAI'] ? icon('pause', 15) : icon('play', 15) !!}
                                        </x-nut-hanh-dong>
                                        @if ((int) ($nv['so_hd'] ?? 0) === 0)
                                            <x-nut-hanh-dong :action="route('admin.nhanvien.destroy', $nv['MANV'])" method="DELETE" class="btn-icon" title="Xoá" aria-label="Xoá {{ $nv['TENNV'] }}"
                                                :confirm="'Xoá nhân viên '.$nv['TENNV'].'? Nhân viên đã có tài khoản hoặc chứng từ thì không xoá được.'">{!! icon('trash', 15) !!}</x-nut-hanh-dong>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="nhân viên" />
        @endif
    </div>
@endsection
