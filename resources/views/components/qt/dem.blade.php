@props(['muc', 'ten' => 'trangthai', 'dang' => ''])

{{-- Hàng đếm theo trạng thái; bấm một mục là lọc danh sách (giữ các điều kiện lọc khác, về trang 1).
     $muc: danh sách [giá trị, nhãn, số]; giá trị '' là "Tất cả". --}}
<nav class="qt-count" aria-label="Lọc theo trạng thái">
    @foreach ($muc as [$giaTri, $nhan, $so])
        @php($query = array_filter([...request()->except(['page', $ten]), $ten => $giaTri], fn ($v) => $v !== '' && $v !== null))
        <a href="{{ url()->current().($query ? '?'.http_build_query($query) : '') }}" @if ((string) $dang === (string) $giaTri) aria-current="page" @endif>
            {{ $nhan }} <b>{{ formatNum($so) }}</b>
        </a>
    @endforeach
</nav>
