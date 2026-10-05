@props([
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'required' => false,
    'hint' => null,
    'options' => null,
    'placeholder' => null,
])

{{-- Một ô trong form quản trị: nhãn, ô nhập (text / số / ngày / select / textarea), gợi ý và lỗi validate --}}
@php
    $id = 'f_'.str_replace(['[', ']', '.'], '_', $name);
    $giaTri = old($name, $value instanceof \DateTimeInterface ? $value->format($type === 'datetime-local' ? 'Y-m-d\TH:i' : 'Y-m-d') : $value);
    $loi = $errors->first($name);
@endphp
<div class="form-group">
    <label class="form-label" for="{{ $id }}">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>
    @if ($options !== null)
        <select id="{{ $id }}" name="{{ $name }}" @class(['form-control', 'is-invalid' => $loi]) @required($required) {{ $attributes }}>
            @if (! $required)
                <option value="">— Không chọn —</option>
            @endif
            @foreach ($options as $ma => $nhan)
                <option value="{{ $ma }}" @selected((string) $giaTri === (string) $ma)>{{ $nhan }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" @class(['form-control', 'is-invalid' => $loi]) placeholder="{{ $placeholder }}" @required($required) {{ $attributes }}>{{ $giaTri }}</textarea>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $giaTri }}" @class(['form-control', 'is-invalid' => $loi])
               placeholder="{{ $placeholder }}" @required($required) {{ $attributes }}>
    @endif
    @if ($loi)
        <div class="form-error">{{ $loi }}</div>
    @elseif ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif
</div>
