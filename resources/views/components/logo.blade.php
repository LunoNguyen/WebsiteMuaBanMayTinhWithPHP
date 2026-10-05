@props(['height' => 32])

{{-- Logo đầy đủ: một bản cho nền sáng, một bản cho nền tối; CSS trong theme.css chọn bản đang dùng --}}
<img class="logo-img for-light" src="{{ asset('assets/img/logo.png') }}" alt="NEXUS Computer Store" style="height: {{ (int) $height }}px">
<img class="logo-img for-dark" src="{{ asset('assets/img/logo-dark.png') }}" alt="NEXUS Computer Store" style="height: {{ (int) $height }}px">
