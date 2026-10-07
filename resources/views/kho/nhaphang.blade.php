@extends('layouts.admin', ['title' => 'Nhập hàng', 'breadcrumb' => ['Kho', 'Nhập hàng']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Nhập hàng</h1>
            <p>Kiểm đếm phiếu nhập từ nhà cung cấp và cộng vào tồn kho{{ $tonThap ? ' · '.formatNum($tonThap).' sản phẩm đang bán còn dưới 10' : '' }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('kho.nhaphang.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Tạo phiếu nhập</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" :dang="$trangthai" />

    <div class="qt-split">
        <div class="card">
            <form method="GET" class="qt-toolbar">
                @if ($trangthai !== '')
                    <input type="hidden" name="trangthai" value="{{ $trangthai }}">
                @endif
                <x-qt.tim :value="$search" placeholder="Tìm mã phiếu hoặc nhà cung cấp" />
                <x-qt.chon name="mancc" :value="$mancc" :options="['' => 'Mọi nhà cung cấp'] + $nccList" />
                <x-qt.chon name="khoang" :value="$khoang" icon="calendar" :options="\App\Http\Controllers\Controller::KHOANG_NGAY" />
            </form>

            @if (empty($phieunhap))
                <div class="qt-empty">
                    {!! icon('inbox', 40) !!}
                    <h3>Không có phiếu nhập nào khớp điều kiện lọc</h3>
                    <a href="{{ route('kho.nhaphang') }}" class="btn btn-outline">Xoá bộ lọc</a>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Mã phiếu</th><th>Nhà cung cấp</th><th class="num">Số lượng</th><th class="num">Tổng tiền</th><th>Ngày tạo</th><th>Trạng thái</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($phieunhap as $pnh)
                                @php
                                    [$mau, $nhan] = $stMap[$pnh['TRANGTHAI']] ?? ['var(--text-secondary)', $pnh['TRANGTHAI']];
                                @endphp
                                <tr @class(['qt-chon' => $pnh['MAPNH'] === $selectedMapnh])>
                                    <td><a href="{{ url()->current().'?'.http_build_query([...request()->except('pn'), 'pn' => $pnh['MAPNH']]) }}" class="qt-ma">{{ $pnh['MAPNH'] }}</a></td>
                                    <td>{{ $pnh['TENNCC'] ?? '—' }}<span class="qt-sub">{{ $pnh['TENNV'] ?? '' }}</span></td>
                                    <td class="num">{{ formatNum($pnh['tong_sl'] ?? 0) }}<span class="qt-sub">{{ $pnh['so_sku'] ?? 0 }} mã</span></td>
                                    <td class="num"><strong>{{ formatVND($pnh['TONGCONG_PNH'] ?? 0) }}</strong></td>
                                    <td>{{ $pnh['NGAYTAO'] ? date('d/m/Y', strtotime($pnh['NGAYTAO'])) : '—' }}</td>
                                    <td><span class="badge-tt" style="--c:{{ $mau }}">{{ $nhan }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="phiếu" />
            @endif
        </div>

        <aside class="card qt-split-panel">
            @if ($detail)
                @php
                    [$mauCt, $nhanCt] = $stMap[$detail['TRANGTHAI']] ?? ['var(--text-secondary)', $detail['TRANGTHAI']];
                    $choKiemDem = in_array($detail['TRANGTHAI'], ['ChoDuyet', 'DaDuyet', 'DaNhan'], true);
                @endphp
                <div class="card-header">
                    <div>
                        <h3>Phiếu {{ $detail['MAPNH'] }}</h3>
                        <span class="qt-sub">{{ $detail['TENNCC'] ?? '—' }} · {{ $detail['NGAYTAO'] ? date('d/m/Y H:i', strtotime($detail['NGAYTAO'])) : '—' }}</span>
                    </div>
                    <span class="badge-tt" style="--c:{{ $mauCt }}">{{ $nhanCt }}</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Sản phẩm</th><th class="num">SL</th><th class="num">Tồn hiện tại</th></tr></thead>
                        <tbody>
                            @forelse ($ctpnList as $ct)
                                <tr>
                                    <td>{{ $ct['TENSP'] }}<span class="qt-sub">{{ $ct['MASP'] }} · {{ formatVND($ct['DONGIA_NHAP'] ?? 0) }}</span></td>
                                    <td class="num"><strong>{{ formatNum($ct['SOLUONG'] ?? 0) }}</strong></td>
                                    <td class="num">{{ formatNum($ct['SOLUONGTON'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="qt-sub" style="text-align:center;padding:24px">Phiếu chưa có sản phẩm</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="qt-split-foot">
                    <div class="qt-split-tong"><span>Tổng giá trị</span><strong>{{ formatVND($detail['TONGCONG_PNH'] ?? 0) }}</strong></div>
                    @if ($choKiemDem)
                        <button type="button" class="btn btn-primary" data-hoan-tat="{{ route('kho.nhaphang.hoan-tat', $detail['MAPNH']) }}" data-ma="{{ $detail['MAPNH'] }}">
                            {!! icon('check', 16) !!} Hoàn tất nhập kho
                        </button>
                    @endif
                </div>
            @else
                <div class="qt-empty">
                    {!! icon('clipboard', 36) !!}
                    <h3>Chọn một phiếu để xem chi tiết</h3>
                </div>
            @endif
        </aside>
    </div>
@endsection

@push('scripts')
<script>
document.querySelector('[data-hoan-tat]')?.addEventListener('click', function () {
  var nut = this;
  if (!confirm('Hoàn tất nhập kho phiếu ' + nut.dataset.ma + '?\nSố lượng trong phiếu sẽ được cộng vào tồn kho.')) return;
  nut.disabled = true;
  fetch(nut.dataset.hoanTat, {method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (d.success) { showToast('Đã nhập kho phiếu ' + nut.dataset.ma + '.', 'success'); setTimeout(function () { location.reload(); }, 1200); }
      else { showToast(d.error || 'Không hoàn tất được phiếu nhập.', 'error'); nut.disabled = false; }
    })
    .catch(function () { showToast('Không kết nối được máy chủ.', 'error'); nut.disabled = false; });
});
</script>
@endpush
