@extends('layouts.shop', ['title' => $tuKhoa !== '' ? 'Tìm "'.$tuKhoa.'"' : 'Sản phẩm'])

@section('content')
    <nav class="s-crumb"><a href="{{ route('home') }}">Trang chủ</a> {!! icon('chevron-right', 14) !!} <span>Sản phẩm</span></nav>

    <div class="s-list">
        <form class="s-card s-filter" method="GET" action="{{ route('sanpham.index') }}" id="locForm">
            @if ($tuKhoa !== '')
                <input type="hidden" name="q" value="{{ $tuKhoa }}">
            @endif
            <input type="hidden" name="sap_xep" value="{{ $sapXep }}">

            <h3>Danh mục</h3>
            @foreach ($danhMuc->where('san_phams_count', '>', 0) as $l)
                <label class="s-check">
                    <input type="checkbox" name="loai[]" value="{{ $l->MALOAI }}" @checked(in_array($l->MALOAI, $loai, true)) onchange="this.form.submit()">
                    {{ $l->TENLOAI }} <em>{{ $l->san_phams_count }}</em>
                </label>
            @endforeach

            <h3>Thương hiệu</h3>
            @foreach ($thuongHieu->where('san_phams_count', '>', 0) as $h)
                <label class="s-check">
                    <input type="checkbox" name="hang[]" value="{{ $h->MANSX }}" @checked(in_array($h->MANSX, $hang, true)) onchange="this.form.submit()">
                    {{ $h->TENNSX }} <em>{{ $h->san_phams_count }}</em>
                </label>
            @endforeach

            <h3>Khoảng giá (₫)</h3>
            <div class="s-range">
                <input class="s-input" type="number" name="gia_tu" min="0" step="1000000" placeholder="Từ" value="{{ $giaTu }}">
                <span>–</span>
                <input class="s-input" type="number" name="gia_den" min="0" step="1000000" placeholder="Đến" value="{{ $giaDen }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm btn-block" style="margin-top:12px">Áp dụng</button>
            @if ($loai || $hang || $giaTu || $giaDen)
                <a href="{{ route('sanpham.index', array_filter(['q' => $tuKhoa])) }}" class="btn btn-ghost btn-sm btn-block" style="margin-top:6px">Xoá bộ lọc</a>
            @endif
        </form>

        <div>
            <div class="s-toolbar">
                <h1>{{ $tuKhoa !== '' ? 'Kết quả cho "'.$tuKhoa.'"' : 'Tất cả sản phẩm' }} <span class="s-hint">({{ $sanPham->total() }})</span></h1>
                <select class="s-select" form="locForm" name="sap_xep" aria-label="Sắp xếp"
                        onchange="document.querySelector('#locForm input[type=hidden][name=sap_xep]').remove(); this.form.submit()">
                    @foreach (\App\Http\Controllers\Shop\SanPhamController::SAP_XEP as $ma => $nhan)
                        <option value="{{ $ma }}" @selected($sapXep === $ma)>{{ $nhan }}</option>
                    @endforeach
                </select>
            </div>

            @if ($sanPham->isEmpty())
                <div class="s-card s-empty">
                    {!! icon('search', 40) !!}
                    <p>Không tìm thấy sản phẩm phù hợp. Thử bỏ bớt bộ lọc hoặc đổi từ khoá.</p>
                </div>
            @else
                <div class="s-grid g4">
                    @foreach ($sanPham as $sp)
                        <x-the-san-pham :sp="$sp" />
                    @endforeach
                </div>

                @if ($sanPham->lastPage() > 1)
                    <nav class="s-pager" aria-label="Phân trang">
                        @if ($sanPham->onFirstPage())
                            <span class="off">‹</span>
                        @else
                            <a href="{{ $sanPham->previousPageUrl() }}" aria-label="Trang trước">‹</a>
                        @endif
                        @foreach ($sanPham->getUrlRange(1, $sanPham->lastPage()) as $so => $url)
                            @if ($so === $sanPham->currentPage())
                                <span class="on">{{ $so }}</span>
                            @else
                                <a href="{{ $url }}">{{ $so }}</a>
                            @endif
                        @endforeach
                        @if ($sanPham->hasMorePages())
                            <a href="{{ $sanPham->nextPageUrl() }}" aria-label="Trang sau">›</a>
                        @else
                            <span class="off">›</span>
                        @endif
                    </nav>
                @endif
            @endif
        </div>
    </div>
@endsection
