@extends('layouts.admin', ['title' => 'Xuất kho', 'breadcrumb' => ['Kho', 'Xuất kho']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Đơn cần xuất kho</h1>
            <p>{{ formatNum($statMap['DaXacNhan'] ?? 0) }} đơn đã xác nhận đang chờ soạn hàng và bàn giao vận chuyển</p>
        </div>
    </div>

    @include('partials.thong-bao')

    @include('partials.quan-tri.ds-don', [
        'routeXem' => 'kho.donhang.show',
        'nutTiep' => fn (array $dh): ?array => $dh['TRANGTHAI'] === 'DaXacNhan'
            ? ['Xuất kho', route('kho.donhang.xuat-kho', $dh['MAHD'])]
            : null,
        'rong' => 'Không có đơn nào cần xuất kho',
    ])
@endsection
