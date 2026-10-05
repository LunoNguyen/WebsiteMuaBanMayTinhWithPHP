<button type="button" class="cb-fab" id="cbFab" aria-label="Mở trợ lý tư vấn" aria-expanded="false">{!! icon('message', 24) !!}</button>

<section class="cb-box" id="cbBox" aria-label="Trợ lý tư vấn" data-url="{{ route('chatbot') }}">
    <div class="cb-hd">
        <span class="cb-av">{!! icon('bot', 18) !!}</span>
        <div>
            <strong>Trợ lý NEXUS</strong>
            <small>● Đang trực tuyến</small>
        </div>
        <button type="button" id="cbClose" aria-label="Đóng">{!! icon('x', 18) !!}</button>
    </div>
    <div class="cb-body" id="cbBody">
        <div class="cb-msg bot">Xin chào! Mình có thể giúp bạn tìm sản phẩm, xem mã giảm giá, kiểm tra đơn hàng hoặc giải đáp về giao hàng, bảo hành.</div>
    </div>
    <div class="cb-quick" id="cbQuick">
        <button type="button">Laptop dưới 30 triệu</button>
        <button type="button">Mã giảm giá</button>
        <button type="button">Đơn hàng của tôi</button>
        <button type="button">Giao hàng</button>
    </div>
    <form class="cb-form" id="cbForm">
        <input type="text" id="cbInput" maxlength="500" placeholder="Nhập câu hỏi..." autocomplete="off" aria-label="Câu hỏi">
        <button type="submit" aria-label="Gửi">{!! icon('send', 16) !!}</button>
    </form>
</section>
