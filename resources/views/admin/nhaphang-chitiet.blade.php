@extends('layouts.admin', ['title' => 'Phiếu nhập '.$pn->MAPNH, 'breadcrumb' => ['Quản lý', 'Nhập hàng', $pn->MAPNH]])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chi-tiet.css') }}?v={{ filemtime(public_path('assets/css/chi-tiet.css')) }}">
@endpush

@php
    [$mau, $nhan] = $trangThai[$pn->TRANGTHAI] ?? ['var(--text-secondary)', $pn->TRANGTHAI];
    $tienHang = (float) $pn->chiTiets->sum('THANHTIEN');
    $buoc = ['ChoDuyet' => 'Chờ duyệt', 'DaDuyet' => 'Đã duyệt', 'DaNhan' => 'Đã nhận', 'HoanThanh' => 'Nhập kho xong'];
    $viTri = array_search($pn->TRANGTHAI, array_keys($buoc), true);
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Phiếu nhập {{ $pn->MAPNH }}</h1>
            <p>
                <span style="color:{{ $mau }};font-weight:600">{{ $nhan }}</span>
                · tạo {{ $pn->NGAYTAO?->format('d/m/Y H:i') }}{{ $pn->nhanVien ? ' bởi '.$pn->nhanVien->TENNV : '' }}
            </p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.nhaphang') }}" class="btn btn-outline">← Danh sách</a>
            @if ($huyDuoc)
                <x-nut-hanh-dong :action="route('admin.nhaphang.huy', $pn->MAPNH)" method="PATCH" class="btn btn-outline" style="color:var(--red)"
                                 :confirm="'Huỷ phiếu '.$pn->MAPNH.'?'">Huỷ phiếu</x-nut-hanh-dong>
            @endif
            @if (in_array($pn->TRANGTHAI, ['ChoDuyet', 'DaHuy'], true))
                <x-nut-hanh-dong :action="route('admin.nhaphang.destroy', $pn->MAPNH)" method="DELETE" class="btn btn-outline" style="color:var(--red)"
                                 :confirm="'Xoá hẳn phiếu '.$pn->MAPNH.'?'">Xoá</x-nut-hanh-dong>
            @endif
            @if ($suaDuoc)
                <a href="{{ route('admin.nhaphang.edit', $pn->MAPNH) }}" class="btn btn-outline">Sửa phiếu</a>
            @endif
            @if ($pn->TRANGTHAI === 'ChoDuyet')
                <x-nut-hanh-dong :action="route('admin.nhaphang.duyet', $pn->MAPNH)" method="PATCH" class="btn btn-primary">Duyệt phiếu</x-nut-hanh-dong>
            @endif
        </div>
    </div>

    @include('partials.thong-bao')

    <div class="ct-wrap">
        @if ($pn->TRANGTHAI === 'DaHuy')
            <div class="ct-card"><div class="ct-note">Phiếu đã huỷ, không cộng vào tồn kho.</div></div>
        @else
            <div class="ct-card">
                <div class="ct-steps">
                    @foreach (array_values($buoc) as $i => $tenBuoc)
                        <div @class(['ct-step', 'done' => $viTri !== false && $i <= $viTri])>{{ $tenBuoc }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="ct-grid">
            <section class="ct-card">
                <div class="ct-card-hd"><h3>Sản phẩm ({{ $pn->chiTiets->count() }})</h3></div>
                <div class="ct-table-wrap">
                    <table class="ct-table">
                        <thead><tr><th>Sản phẩm</th><th class="num">SL</th><th class="num">Đơn giá</th><th class="num">Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach ($pn->chiTiets as $ct)
                                <tr>
                                    <td>
                                        {{ $ct->sanPham?->TENSP ?? $ct->MASP }}
                                        <span class="ct-sub">{{ $ct->MASP }} · tồn hiện tại {{ formatNum($ct->sanPham?->SOLUONGTON ?? 0) }}{{ $ct->GHI_CHU ? ' · '.$ct->GHI_CHU : '' }}</span>
                                    </td>
                                    <td class="num">{{ formatNum($ct->SOLUONG) }}</td>
                                    <td class="num">{{ formatVND($ct->DONGIA_NHAP) }}</td>
                                    <td class="num"><strong>{{ formatVND($ct->THANHTIEN) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ct-sum">
                    <div><span>Tiền hàng</span><strong>{{ formatVND($tienHang) }}</strong></div>
                    <div><span>Chiết khấu {{ rtrim(rtrim(number_format((float) $pn->CHIETKHAU, 2), '0'), '.') }}%</span><strong>−{{ formatVND($tienHang * (float) $pn->CHIETKHAU / 100) }}</strong></div>
                    <div><span>VAT {{ rtrim(rtrim(number_format((float) $pn->THUE_VAT, 2), '0'), '.') }}%</span><strong>+{{ formatVND($tienHang * (1 - (float) $pn->CHIETKHAU / 100) * (float) $pn->THUE_VAT / 100) }}</strong></div>
                    <div class="ct-total"><span>Tổng cộng</span><strong>{{ formatVND($pn->TONGCONG_PNH) }}</strong></div>
                </div>
            </section>

            <section class="ct-card">
                <div class="ct-card-hd"><h3>Thông tin</h3></div>
                <div class="ct-body">
                    <dl class="ct-dl">
                        <dt>Nhà cung cấp</dt><dd>{{ $pn->nhaCungCap?->TENNCC ?? $pn->MANCC }}</dd>
                        @if ($pn->nhaCungCap?->SDT_NCC)<dt>Điện thoại NCC</dt><dd>{{ $pn->nhaCungCap->SDT_NCC }}</dd>@endif
                        <dt>Ngày đặt mua</dt><dd>{{ $pn->NGAY_DATMUA?->format('d/m/Y H:i') ?? '—' }}</dd>
                        <dt>Giao dự kiến</dt><dd>{{ $pn->NGAYGIAO?->format('d/m/Y H:i') ?? '—' }}</dd>
                        <dt>Ngày nhận</dt><dd>{{ $pn->NGAYNHAN?->format('d/m/Y H:i') ?? '—' }}</dd>
                        <dt>Thanh toán NCC</dt><dd>{{ ['ChuaThanhToan' => 'Chưa thanh toán', 'DaThanhToan' => 'Đã thanh toán', 'HoanTien' => 'Hoàn tiền'][$pn->TRANGTHAI_THANHTOAN] ?? $pn->TRANGTHAI_THANHTOAN }}</dd>
                        <dt>Ghi chú</dt><dd>{{ $pn->GHI_CHU ?: '—' }}</dd>
                    </dl>
                </div>
            </section>
        </div>
    </div>
@endsection
