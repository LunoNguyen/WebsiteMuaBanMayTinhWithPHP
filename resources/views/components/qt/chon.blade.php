@props(['name', 'options', 'value' => '', 'icon' => null])

{{-- Select tự dựng thay <select> gốc: nút + danh sách xổ xuống (admin.js lo mở / đóng / chọn).
     Chọn xong thì gửi luôn form bao quanh. $options: [giá trị => nhãn]; giá trị '' là mặc định. --}}
@php($nhanDangChon = $options[$value] ?? reset($options))
<div class="qt-select" data-qt-select>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    <button type="button" aria-haspopup="listbox" aria-expanded="false">
        @if ($icon){!! icon($icon, 16) !!}@endif
        <span data-qt-nhan>{{ $nhanDangChon }}</span>
        {!! icon('chevron', 16) !!}
    </button>
    <div class="qt-pop" role="listbox">
        @foreach ($options as $giaTri => $nhan)
            <button type="button" role="option" data-value="{{ $giaTri }}" aria-selected="{{ (string) $giaTri === (string) $value ? 'true' : 'false' }}">{{ $nhan }}</button>
        @endforeach
    </div>
</div>
