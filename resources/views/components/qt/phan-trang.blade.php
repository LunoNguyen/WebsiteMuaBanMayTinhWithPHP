@props(['page', 'pages', 'total', 'perPage', 'donVi' => 'mục'])

{{-- Chân card danh sách: "Hiển thị a–b trên N" + phân trang ‹ 1 2 3 › (giữ các điều kiện lọc trên URL) --}}
@php
    $lienKet = fn (int $so): string => url()->current().'?'.http_build_query([...request()->except('page'), 'page' => $so]);
    $tu = $total === 0 ? 0 : ($page - 1) * $perPage + 1;
@endphp
<div class="qt-foot">
    <span>Hiển thị {{ formatNum($tu) }}–{{ formatNum(min($page * $perPage, $total)) }} trên {{ formatNum($total) }} {{ $donVi }}</span>
    @if ($pages > 1)
        <nav class="qt-pager" aria-label="Phân trang">
            @if ($page > 1)
                <a href="{{ $lienKet($page - 1) }}" aria-label="Trang trước">‹</a>
            @else
                <span class="off" aria-hidden="true">‹</span>
            @endif
            @for ($so = max(1, $page - 2); $so <= min($pages, $page + 2); $so++)
                @if ($so === $page)
                    <span aria-current="page">{{ $so }}</span>
                @else
                    <a href="{{ $lienKet($so) }}">{{ $so }}</a>
                @endif
            @endfor
            @if ($page < $pages)
                <a href="{{ $lienKet($page + 1) }}" aria-label="Trang sau">›</a>
            @else
                <span class="off" aria-hidden="true">›</span>
            @endif
        </nav>
    @endif
</div>
