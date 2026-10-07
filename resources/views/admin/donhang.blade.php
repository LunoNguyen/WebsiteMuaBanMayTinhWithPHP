@extends('layouts.admin', ['title' => 'Đơn hàng', 'breadcrumb' => ['Bán hàng', 'Đơn hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý đơn hàng</h1>
            <p>{{ formatNum(array_sum($statMap)) }} đơn{{ $choLau > 0 ? ' · '.$choLau.' đơn đang chờ xác nhận quá 2 giờ' : '' }}</p>
        </div>
        <div class="page-header-right">
            <button type="button" class="btn btn-outline" onclick="exportTableCSV('bangDon', 'donhang_{{ date('Ymd') }}')">{!! icon('download', 16) !!} Xuất CSV</button>
            <a href="{{ route('admin.donhang.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Tạo đơn tại quầy</a>
        </div>
    </div>

    @include('partials.thong-bao')

    @include('partials.quan-tri.ds-don', [
        'routeXem' => 'admin.donhang.show',
        'nutTiep' => fn (array $dh): ?array => isset($actionLabel[$dh['TRANGTHAI']])
            ? [$actionLabel[$dh['TRANGTHAI']], route('admin.donhang.buoc-tiep-theo', $dh['MAHD'])]
            : null,
    ])
@endsection
