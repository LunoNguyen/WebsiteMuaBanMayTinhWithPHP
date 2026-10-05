// NEXUS Store - script dùng chung của cửa hàng

// Menu tài khoản: bấm để mở, bấm ra ngoài hoặc Esc để đóng
document.querySelectorAll('[data-menu]').forEach(function (menu) {
  var btn = menu.querySelector('[data-menu-btn]');
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    var open = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
});
document.addEventListener('click', function () {
  document.querySelectorAll('[data-menu].open').forEach(function (m) { m.classList.remove('open'); });
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') document.querySelectorAll('[data-menu].open').forEach(function (m) { m.classList.remove('open'); });
});

// Ô số lượng có nút − / +; data-auto-submit thì gửi form ngay khi đổi.
// Gắn lại cho vùng vừa được realtime thay HTML (sự kiện rt:vung-moi).
function ganOSoLuong(goc) {
  goc.querySelectorAll('[data-qty]').forEach(function (box) {
    var input = box.querySelector('input');
    function set(v) {
      var max = parseInt(input.max || '99', 10);
      v = Math.max(1, Math.min(max, v || 1));
      if (String(v) === input.value) return;
      input.value = v;
      if (box.hasAttribute('data-auto-submit')) input.form.submit();
    }
    box.querySelector('[data-minus]').addEventListener('click', function () { set(parseInt(input.value, 10) - 1); });
    box.querySelector('[data-plus]').addEventListener('click', function () { set(parseInt(input.value, 10) + 1); });
    input.addEventListener('change', function () { set(parseInt(input.value, 10)); });
  });
}
ganOSoLuong(document);
document.addEventListener('rt:vung-moi', function (e) { ganOSoLuong(e.detail); });

// Ảnh chi tiết sản phẩm: bấm ảnh nhỏ để đổi ảnh lớn
document.querySelectorAll('[data-thumb]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var main = document.getElementById('anhChinh');
    if (main) main.src = btn.dataset.thumb;
    document.querySelectorAll('[data-thumb]').forEach(function (b) { b.classList.toggle('on', b === btn); });
  });
});

// Nút hiện / ẩn mật khẩu (đăng nhập, đăng ký)
document.querySelectorAll('[data-hien-mk]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var o = document.getElementById(btn.dataset.hienMk);
    var hien = o.type === 'password';
    o.type = hien ? 'text' : 'password';
    btn.setAttribute('aria-label', hien ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    btn.classList.toggle('on', hien);
  });
});

// Chatbot
(function () {
  var fab = document.getElementById('cbFab');
  var box = document.getElementById('cbBox');
  if (!fab || !box) return;
  var body = document.getElementById('cbBody');
  var form = document.getElementById('cbForm');
  var input = document.getElementById('cbInput');
  var dangGui = false;

  function toggle(open) {
    box.classList.toggle('open', open);
    fab.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) input.focus();
  }
  fab.addEventListener('click', function () { toggle(!box.classList.contains('open')); });
  document.getElementById('cbClose').addEventListener('click', function () { toggle(false); });

  function them(lop, text) {
    var el = document.createElement('div');
    el.className = lop;
    el.textContent = text;
    body.appendChild(el);
    body.scrollTop = body.scrollHeight;
    return el;
  }

  function gui(cauHoi) {
    cauHoi = cauHoi.trim();
    if (!cauHoi || dangGui) return;
    dangGui = true;
    them('cb-msg me', cauHoi);
    input.value = '';
    var cho = them('cb-typing', 'Trợ lý đang trả lời...');

    fetch(box.dataset.url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
      },
      body: JSON.stringify({ noi_dung: cauHoi })
    })
      .then(function (r) {
        if (r.status === 429) throw new Error('Bạn gửi hơi nhanh, đợi một chút rồi hỏi tiếp nhé.');
        if (!r.ok) throw new Error('Trợ lý đang bận, bạn thử lại sau nhé.');
        return r.json();
      })
      .then(function (d) {
        cho.remove();
        them('cb-msg bot', d.tra_loi);
        if (d.san_pham && d.san_pham.length) {
          var list = document.createElement('div');
          list.className = 'cb-prod';
          d.san_pham.forEach(function (sp) {
            var a = document.createElement('a');
            a.href = sp.url;
            var ten = document.createElement('span');
            ten.textContent = sp.ten;
            var gia = document.createElement('b');
            gia.textContent = sp.gia;
            a.append(ten, gia);
            list.appendChild(a);
          });
          body.appendChild(list);
          body.scrollTop = body.scrollHeight;
        }
      })
      .catch(function (err) { cho.remove(); them('cb-msg bot', err.message); })
      .finally(function () { dangGui = false; });
  }

  form.addEventListener('submit', function (e) { e.preventDefault(); gui(input.value); });
  document.querySelectorAll('#cbQuick button').forEach(function (b) {
    b.addEventListener('click', function () { gui(b.textContent); });
  });
})();
