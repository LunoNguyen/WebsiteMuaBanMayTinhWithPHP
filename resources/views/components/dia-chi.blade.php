@props([
    'name',
    'label' => 'Địa chỉ',
    'value' => null,
    'required' => false,
    'maxlength' => 200,
    'kieu' => 'quan-tri',
    'id' => null,
])

{{--
    Ô địa chỉ: gõ để tìm rồi chọn Tỉnh / thành phố và Phường / xã (provinces.open-api.vn, qua route diachi.*) + ô số nhà, tên đường.
    Ba phần được ghép thành một chuỗi "Số nhà, Phường ..., Tỉnh ..." vào input ẩn $name, nên cột địa chỉ trong CSDL giữ nguyên.
    Đổi giá trị từ script khác: phần tử [data-dia-chi].dispatchEvent(new CustomEvent('dia-chi:dat', {detail: 'chuỗi địa chỉ'})).
    $kieu: 'quan-tri' (form-control) hoặc 'shop' (s-input).
--}}
@php
    $id ??= 'dc_'.str_replace(['[', ']', '.'], '_', $name);
    $giaTri = old($name, $value);
    $loi = $errors->first($name);
    $laShop = $kieu === 'shop';
    $lopO = $laShop ? 's-input' : 'form-control';
@endphp
<div {{ $attributes->class([$laShop ? 's-field' : 'form-group', 'dia-chi']) }} data-dia-chi
     data-url-tinh="{{ route('diachi.tinh') }}" data-url-phuong="{{ route('diachi.phuong', ['maTinh' => 0]) }}"
     data-max="{{ $maxlength }}">
    <label @class(['form-label' => ! $laShop]) for="{{ $id }}_tinh">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>
    <input type="hidden" name="{{ $name }}" value="{{ $giaTri }}" data-dia-chi-gia-tri>
    <div class="dia-chi-chon">
        @foreach (['tinh' => 'Tỉnh / thành phố', 'phuong' => 'Phường / xã'] as $cap => $nhan)
            <div class="dc-combo" data-dc-combo="{{ $cap }}">
                <input type="text" id="{{ $id }}_{{ $cap }}" @class([$lopO, 'dc-input', 'is-invalid' => $loi])
                       role="combobox" aria-expanded="false" aria-autocomplete="list" aria-controls="{{ $id }}_{{ $cap }}_ds"
                       aria-label="{{ $nhan }}" placeholder="{{ $cap === 'tinh' ? 'Đang tải…' : $nhan }}" autocomplete="off" spellcheck="false"
                       disabled @required($required)>
                <span class="dc-mui" aria-hidden="true">{!! icon('chevron', 16) !!}</span>
                <ul class="dc-ds" id="{{ $id }}_{{ $cap }}_ds" role="listbox" aria-label="{{ $nhan }}" hidden></ul>
            </div>
        @endforeach
    </div>
    <input type="text" @class([$lopO, 'is-invalid' => $loi]) data-dia-chi-duong aria-label="Số nhà, tên đường"
           placeholder="Số nhà, tên đường" maxlength="{{ $maxlength }}" autocomplete="address-line1" @required($required)>
    <div @class([$laShop ? 's-hint' : 'form-hint']) data-dia-chi-ghi-chu hidden></div>
    @if ($loi)
        <div class="{{ $laShop ? 's-err' : 'form-error' }}">{{ $loi }}</div>
    @endif
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/js/dia-chi.js') }}?v={{ filemtime(public_path('assets/js/dia-chi.js')) }}" defer></script>
    @endpush
@endonce
