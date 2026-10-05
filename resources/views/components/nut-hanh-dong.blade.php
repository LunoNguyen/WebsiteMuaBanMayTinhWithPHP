@props(['action', 'method' => 'POST', 'confirm' => null])

{{-- Nút đổi dữ liệu: gửi bằng form có CSRF thay vì link GET --}}
<form method="POST" action="{{ $action }}" style="display:inline-flex;margin:0"
      @if ($confirm) onsubmit="return confirm(@js($confirm))" @endif>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <button type="submit" {{ $attributes }}>{{ $slot }}</button>
</form>
