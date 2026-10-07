@extends('layouts.admin', ['title' => 'Khuyến mãi', 'breadcrumb' => ['Bán hàng', 'Khuyến mãi']])

@section('content')
    @php
        $mauTrangThai = ['HoatDong' => ['var(--green)', 'Hoạt động'], 'TamDung' => ['var(--orange)', 'Tạm dừng'], 'HetHan' => ['var(--text-muted)', 'Hết hạn']];
    @endphp
    <div class="page-header">
        <div class="page-header-left">
            <h1>Khuyến mãi</h1>
            <p>{{ formatNum($dem[1][2]) }} mã đang hoạt động trên {{ formatNum($dem[0][2]) }} chương trình</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.khuyenmai.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Tạo khuyến mãi</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" :dang="$trangthai" />

    <div class="card">
        <form method="GET" class="qt-toolbar">
            @if ($trangthai !== '')
                <input type="hidden" name="trangthai" value="{{ $trangthai }}">
            @endif
            <x-qt.tim :value="$search" placeholder="Tìm tên hoặc mã voucher" />
            <x-qt.chon name="loaikm" :value="$loaikm" :options="['' => 'Mọi loại giảm', 'PhanTram' => 'Giảm theo %', 'SoTienCoDinh' => 'Giảm số tiền']" />
        </form>

        @if (empty($khuyenmai))
            <div class="qt-empty">
                {!! icon('tag', 40) !!}
                <h3>Không có khuyến mãi nào khớp điều kiện lọc</h3>
                <a href="{{ route('admin.khuyenmai') }}" class="btn btn-outline">Xoá bộ lọc</a>
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Chương trình</th><th class="num">Mức giảm</th><th class="num">Đơn tối thiểu</th><th>Thời gian</th><th>Lượt dùng</th><th>Trạng thái</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($khuyenmai as $km)
                            @php
                                [$mau, $nhan] = $mauTrangThai[$km['TRANGTHAI']] ?? ['var(--text-secondary)', $km['TRANGTHAI']];
                                $phanTram = $km['SOLUONG_MA'] ? min(100, (int) round($km['DA_SUDUNG'] / $km['SOLUONG_MA'] * 100)) : null;
                                $sapHet = $km['TRANGTHAI'] !== 'HetHan' && $km['NGAYKT'] && strtotime($km['NGAYKT']) < strtotime('+3 days');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.khuyenmai.edit', $km['MAKM']) }}" style="color:var(--text-primary);font-weight:600">{{ $km['TENKM'] }}</a>
                                    <span class="qt-sub"><span class="qt-ma">{{ $km['MA_CODE'] ?? $km['MAKM'] }}</span> · {{ $km['so_sp_ap_dung'] > 0 ? $km['so_sp_ap_dung'].' sản phẩm' : 'mọi sản phẩm' }}</span>
                                </td>
                                <td class="num">
                                    <strong>{{ $km['LOAI_KM'] === 'PhanTram' ? '−'.(float) $km['GIATRI_KM'].'%' : '−'.formatVND($km['GIATRI_KM']) }}</strong>
                                    @if ($km['LOAI_KM'] === 'PhanTram' && $km['SOTIENTOIDA_KM'])
                                        <span class="qt-sub">tối đa {{ formatVND($km['SOTIENTOIDA_KM']) }}</span>
                                    @endif
                                </td>
                                <td class="num">{{ $km['SOTIENTOITHIEU_NHANKM'] > 0 ? formatVND($km['SOTIENTOITHIEU_NHANKM']) : '—' }}</td>
                                <td>
                                    {{ $km['NGAYBD'] ? date('d/m/Y', strtotime($km['NGAYBD'])) : '—' }} – {{ $km['NGAYKT'] ? date('d/m/Y', strtotime($km['NGAYKT'])) : 'không hạn' }}
                                    @if ($sapHet)
                                        <span class="qt-late">Sắp hết hạn</span>
                                    @endif
                                </td>
                                <td style="min-width:130px">
                                    @if ($phanTram !== null)
                                        {{ formatNum($km['DA_SUDUNG']) }} / {{ formatNum($km['SOLUONG_MA']) }}
                                        <div class="qt-meter"><span style="width:{{ $phanTram }}%"></span></div>
                                    @else
                                        {{ formatNum($km['DA_SUDUNG']) }}<span class="qt-sub">không giới hạn</span>
                                    @endif
                                </td>
                                <td><span class="badge-tt" style="--c:{{ $mau }}">{{ $nhan }}</span></td>
                                <td>
                                    <div class="qt-actions">
                                        <a href="{{ route('admin.khuyenmai.edit', $km['MAKM']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $km['TENKM'] }}">{!! icon('pencil', 15) !!}</a>
                                        <x-nut-hanh-dong :action="route('admin.khuyenmai.trang-thai', $km['MAKM'])" method="PATCH" class="btn-icon"
                                            confirm="Thay đổi trạng thái khuyến mãi?"
                                            :title="$km['TRANGTHAI'] === 'HoatDong' ? 'Tạm dừng' : 'Kích hoạt'" :aria-label="$km['TRANGTHAI'] === 'HoatDong' ? 'Tạm dừng' : 'Kích hoạt'">
                                            {!! $km['TRANGTHAI'] === 'HoatDong' ? icon('pause', 15) : icon('play', 15) !!}
                                        </x-nut-hanh-dong>
                                        <x-nut-hanh-dong :action="route('admin.khuyenmai.destroy', $km['MAKM'])" method="DELETE" class="btn-icon" title="Xoá" aria-label="Xoá {{ $km['TENKM'] }}"
                                            :confirm="'Xoá khuyến mãi '.$km['TENKM'].'?'">{!! icon('trash', 15) !!}</x-nut-hanh-dong>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="chương trình" />
        @endif
    </div>
@endsection
