{{--
    Thân trang danh sách đơn dùng chung (Admin, Bán hàng, Kho): hàng đếm trạng thái + card [thanh lọc, bảng, phân trang].
    Biến: $dem (list [giá trị, nhãn, số]), $trangthai, $search, $ptgh, $tttt, $khoang, $coLoc,
          $donhang, $page, $pages, $total, $perPage, $routeXem, $nutTiep (xem partials/quan-tri/bang-don).
--}}
<div data-rt-vung="dem-don" data-rt-khi="don">
    <x-qt.dem :muc="$dem" :dang="$trangthai" />
</div>

<div class="card" data-rt-vung="ds-don" data-rt-khi="don">
    <form method="GET" class="qt-toolbar">
        @if ($trangthai !== '')
            <input type="hidden" name="trangthai" value="{{ $trangthai }}">
        @endif
        <x-qt.tim :value="$search" placeholder="Tìm mã đơn, tên hoặc SĐT khách" />
        <x-qt.chon name="ptgh" :value="$ptgh" :options="['' => 'Mọi hình thức nhận', 'GiaoHang' => 'Giao hàng', 'TaiQuay' => 'Tại quầy']" />
        <x-qt.chon name="tttt" :value="$tttt" :options="['' => 'Mọi thanh toán', 'ChoThanhToan' => 'Chờ thanh toán', 'DaThanhToan' => 'Đã thanh toán']" />
        <x-qt.chon name="khoang" :value="$khoang" icon="calendar" :options="\App\Http\Controllers\Controller::KHOANG_NGAY" />
    </form>

    @include('partials.quan-tri.bang-don')

    @if ($total > 0)
        <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="đơn" />
    @endif
</div>
