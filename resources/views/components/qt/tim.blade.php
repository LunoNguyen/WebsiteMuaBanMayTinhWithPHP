@props(['name' => 'q', 'value' => '', 'placeholder' => 'Tìm kiếm...'])

{{-- Ô tìm trong thanh lọc; Enter là gửi form bao quanh --}}
<label class="qt-search">
    {!! icon('search', 16) !!}
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" {{ $attributes }}>
</label>
