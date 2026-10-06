@extends('layouts.shop', ['title' => 'Đang xử lý đơn hàng'])

@push('head')
    {{-- Không có JavaScript thì tự hỏi lại kết quả sau vài giây --}}
    <noscript><meta http-equiv="refresh" content="4;url={{ route('thanhtoan.ket-qua', $maYeuCau) }}"></noscript>
@endpush

@section('content')
    <section class="s-card s-cho" role="status" aria-live="polite">
        <span class="s-cho-vong" aria-hidden="true"></span>
        <h1>Đang xử lý đơn hàng</h1>
        <p>Cửa hàng đang giữ hàng và kiểm tra mã giảm giá cho bạn. Trang sẽ tự chuyển khi xong, thường chỉ vài giây.</p>
        <p class="s-hint" data-cho-lau hidden>Đơn vẫn đang được xử lý, bạn không cần đặt lại. Nếu trang chưa tự chuyển, bấm “Kiểm tra lại”.</p>
        <a href="{{ route('thanhtoan.ket-qua', $maYeuCau) }}" class="btn btn-outline" data-cho-lau hidden>Kiểm tra lại</a>
    </section>
@endsection

@push('scripts')
<script>
(function () {
  var urlTrangThai = @json(route('thanhtoan.trang-thai', $maYeuCau));
  var urlKetQua = @json(route('thanhtoan.ket-qua', $maYeuCau));
  var daChuyen = false;
  var batDau = Date.now();

  function chuyen() {
    if (daChuyen) return;
    daChuyen = true;
    location.replace(urlKetQua);
  }

  function hoi() {
    if (daChuyen) return;
    fetch(urlTrangThai, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (d && d.trang_thai !== 'cho') { chuyen(); return; }
        if (Date.now() - batDau > 10000) {
          document.querySelectorAll('[data-cho-lau]').forEach(function (el) { el.hidden = false; });
        }
        setTimeout(hoi, 1500);
      })
      .catch(function () { setTimeout(hoi, 3000); });
  }

  // Có realtime thì chuyển ngay khi đơn của mình vừa được tạo; không có thì vẫn hỏi định kỳ ở trên
  document.addEventListener('rt:su-kien', function (e) {
    if (e.detail.ten === 'don-hang.thay-doi' && e.detail.du_lieu.moi) hoi();
  });

  setTimeout(hoi, 600);
})();
</script>
@endpush
