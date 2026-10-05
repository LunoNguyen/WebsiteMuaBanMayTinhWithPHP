// NEXUS - realtime qua Laravel Reverb.
// Hai cách cập nhật trang khi có sự kiện:
//  1. Phần tử có data-sp="SP001": sửa ngay giá / tồn kho / nút mua từ dữ liệu sự kiện.
//  2. Vùng có data-rt-vung="ten" data-rt-khi="don don:HD001 sp sp:SP001": tải lại trang ngầm và thay đúng vùng đó
//     bằng HTML mới do server dựng (không phải viết lại giao diện bằng JavaScript).
(function () {
  'use strict';

  var cfg = window.NEXUS_REALTIME;
  // Bản IIFE của laravel-echo gán module vào biến toàn cục Echo; lớp Echo nằm ở .default
  var EchoClass = window.Echo && (window.Echo.default || window.Echo);
  if (!cfg || !cfg.key || typeof EchoClass !== 'function' || !window.Pusher) return;
  var csrf = document.querySelector('meta[name=csrf-token]');

  var echo;
  try {
    echo = new EchoClass({
      broadcaster: 'reverb',
      key: cfg.key,
      wsHost: cfg.host,
      wsPort: cfg.port,
      wssPort: cfg.port,
      forceTLS: cfg.tls,
      enabledTransports: ['ws', 'wss'],
      auth: { headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '' } }
    });
  } catch (e) {
    return;
  }
  window.NexusEcho = echo;

  // ---------- Thông báo nhỏ góc màn hình ----------
  var khayThongBao;
  function thongBao(tieuDe, noiDung, link) {
    if (!khayThongBao) {
      khayThongBao = document.createElement('div');
      khayThongBao.className = 'rt-toasts';
      khayThongBao.setAttribute('role', 'status');
      khayThongBao.setAttribute('aria-live', 'polite');
      document.body.appendChild(khayThongBao);
    }
    var el = document.createElement(link ? 'a' : 'div');
    el.className = 'rt-toast';
    if (link) el.href = link;
    var b = document.createElement('strong');
    b.textContent = tieuDe;
    var s = document.createElement('span');
    s.textContent = noiDung;
    el.append(b, s);
    khayThongBao.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });
    setTimeout(function () {
      el.classList.remove('show');
      setTimeout(function () { el.remove(); }, 250);
    }, 6000);
  }

  // ---------- 1. Cập nhật thẻ sản phẩm tại chỗ ----------
  function capNhatSanPham(d) {
    document.querySelectorAll('[data-sp="' + d.ma + '"]').forEach(function (khoi) {
      khoi.querySelectorAll('[data-sp-gia]').forEach(function (el) { el.textContent = d.gia_text; });
      khoi.querySelectorAll('[data-sp-ton]').forEach(function (el) {
        el.textContent = d.ton_text;
        el.classList.toggle('out', !d.con_hang);
      });
      khoi.querySelectorAll('[data-sp-mua]').forEach(function (el) { el.hidden = !d.con_hang; });
      khoi.querySelectorAll('[data-sp-het]').forEach(function (el) { el.hidden = d.con_hang; });
      khoi.querySelectorAll('input[data-sp-so-luong]').forEach(function (el) {
        el.max = Math.max(1, Math.min(99, d.ton));
        if (parseInt(el.value, 10) > d.ton && d.ton > 0) el.value = d.ton;
      });
      khoi.classList.remove('rt-nhay');
      void khoi.offsetWidth;
      khoi.classList.add('rt-nhay');
    });
  }

  // ---------- 2. Thay vùng bằng HTML mới từ server ----------
  var choThay = new Set();
  var hen = null;

  function danhDauVung(dauHieu) {
    document.querySelectorAll('[data-rt-vung]').forEach(function (vung) {
      var khi = (vung.getAttribute('data-rt-khi') || '').split(/\s+/);
      if (dauHieu.some(function (t) { return khi.indexOf(t) !== -1; })) {
        choThay.add(vung.getAttribute('data-rt-vung'));
      }
    });
    if (choThay.size && !hen) hen = setTimeout(taiVung, 700);
  }

  function taiVung() {
    hen = null;
    var ten = Array.from(choThay);
    choThay.clear();

    fetch(location.href, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Nexus-Realtime': '1' } })
      .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
      .then(function (html) {
        var moi = new DOMParser().parseFromString(html, 'text/html');
        ten.forEach(function (t) {
          var cu = document.querySelector('[data-rt-vung="' + t + '"]');
          var thay = moi.querySelector('[data-rt-vung="' + t + '"]');
          if (!cu || !thay) return;
          // Người dùng đang gõ trong vùng này: để lát nữa mới thay, tránh mất chữ đang nhập
          if (cu.contains(document.activeElement) && document.activeElement.matches('input, textarea, select')) {
            choThay.add(t);
            return;
          }
          var nut = document.importNode(thay, true);
          cu.replaceWith(nut);
          nut.classList.add('rt-nhay');
          document.dispatchEvent(new CustomEvent('rt:vung-moi', { detail: nut }));
        });
        if (choThay.size && !hen) hen = setTimeout(taiVung, 3000);
      })
      .catch(function () { /* mạng chập chờn: lần sự kiện sau sẽ thử lại */ });
  }

  // Báo cho script riêng của từng trang (vd. trang chờ xử lý đơn) mỗi khi có sự kiện
  function baoTrang(ten, duLieu) {
    document.dispatchEvent(new CustomEvent('rt:su-kien', { detail: { ten: ten, du_lieu: duLieu } }));
  }

  // ---------- Kênh ----------
  echo.channel('cua-hang').listen('.san-pham.cap-nhat', function (d) {
    capNhatSanPham(d);
    danhDauVung(['sp', 'sp:' + d.ma]);
  });

  if (cfg.nhanVien) {
    echo.private('quan-tri')
      .listen('.don-hang.thay-doi', function (d) {
        if (d.moi) thongBao('Đơn mới ' + d.ma, (d.nguoi_nhan || 'Khách') + ' · ' + d.tong_text);
        danhDauVung(['don', 'don:' + d.ma]);
      })
      .listen('.san-pham.cap-nhat', function (d) {
        danhDauVung(['sp', 'sp:' + d.ma]);
      });
  }

  if (cfg.kenhDon) {
    echo.private(cfg.kenhDon).listen('.don-hang.thay-doi', function (d) {
      baoTrang('don-hang.thay-doi', d);
      if (!d.moi) thongBao('Đơn ' + d.ma, 'Trạng thái: ' + d.nhan);
      danhDauVung(['don', 'don:' + d.ma]);
    });
  }
})();
