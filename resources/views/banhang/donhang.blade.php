@extends('layouts.admin', ['title' => 'Đơn hàng', 'breadcrumb' => ['Bán hàng', 'Đơn hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Đơn hàng</h1>
            <p>{{ formatNum(array_sum($statMap)) }} đơn{{ $choLau > 0 ? ' · '.$choLau.' đơn đang chờ xác nhận quá 2 giờ' : '' }}</p>
        </div>
    </div>

    @include('partials.thong-bao')

    @include('partials.quan-tri.ds-don', [
        'routeXem' => 'banhang.donhang.show',
        'nutTiep' => fn (array $dh): ?array => isset($actionLabel[$dh['TRANGTHAI']])
            ? [$actionLabel[$dh['TRANGTHAI']], route('banhang.donhang.buoc-tiep-theo', $dh['MAHD'])]
            : null,
    ])
@endsection
