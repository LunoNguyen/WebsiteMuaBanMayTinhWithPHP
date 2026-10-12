// NEXUS - ô địa chỉ (x-dia-chi): Tỉnh / thành + Phường / xã từ provinces.open-api.vn (qua server) + số nhà, tên đường.
// Ô tỉnh / phường gõ để lọc (không cần dấu), chọn bằng chuột hoặc phím lên / xuống + Enter.
// Ghép ba phần thành một chuỗi vào input ẩn; mở form sửa thì tách chuỗi cũ ra để chọn sẵn.
(function () {
  'use strict';

  var boNho = {};

  function tai(url, khoa) {
    if (boNho[khoa]) return boNho[khoa];
    try {
      var daLuu = sessionStorage.getItem(khoa);
      if (daLuu) return (boNho[khoa] = Promise.resolve(JSON.parse(daLuu)));
    } catch (e) { /* trình duyệt chặn sessionStorage: tải lại */ }

    boNho[khoa] = fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
      .then(function (ds) {
        try { sessionStorage.setItem(khoa, JSON.stringify(ds)); } catch (e) { /* đầy bộ nhớ: bỏ qua */ }
        return ds;
      })
      .catch(function (loi) { delete boNho[khoa]; throw loi; });
    return boNho[khoa];
  }

  function boDau(ten) {
    return String(ten || '').normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[đĐ]/g, 'd').toLowerCase().trim();
  }

  // So tên không phân biệt dấu, hoa thường và tiền tố hành chính: "TP.HCM" khớp "Thành phố Hồ Chí Minh"
  var BI_DANH = { hcm: 'hochiminh', tphcm: 'hochiminh', saigon: 'hochiminh', hn: 'hanoi' };
  function chuan(ten) {
    var s = boDau(ten)
      .replace(/^(thanh pho|tinh|phuong|xa|thi tran|dac khu)\s+/, '')
      .replace(/^(tp|p|x)\.\s*/, '').replace(/^tp\s+/, '')
      .replace(/[^a-z0-9]/g, '');
    return BI_DANH[s] || s;
  }
  function timTheoTen(ds, ten) {
    var k = chuan(ten);
    if (!k) return null;
    for (var i = 0; i < ds.length; i++) if (chuan(ds[i].ten) === k) return ds[i];
    return null;
  }

  // ---------- Ô gõ để tìm (combobox) ----------
  var TOI_DA = 80;
  var demId = 0;

  function taoO(khung, khiChon) {
    var o = khung.querySelector('.dc-input');
    var ds = khung.querySelector('.dc-ds');
    var nhanTrong = o.getAttribute('aria-label');
    var danhSach = [];
    var dangChon = null;
    var hienThi = [];
    var viTri = -1;

    function mo() {
      if (o.disabled) return;
      loc();
      ds.hidden = false;
      o.setAttribute('aria-expanded', 'true');
    }
    function dong() {
      ds.hidden = true;
      o.setAttribute('aria-expanded', 'false');
      o.removeAttribute('aria-activedescendant');
    }

    function loc() {
      var tu = chuan(o.value) && (!dangChon || o.value !== dangChon.ten) ? boDau(o.value) : '';
      var tuGon = tu.replace(/[^a-z0-9]/g, '');
      hienThi = !tu ? danhSach : danhSach.filter(function (m) {
        var t = boDau(m.ten);
        return t.indexOf(tu) !== -1 || t.replace(/[^a-z0-9]/g, '').indexOf(tuGon) !== -1;
      });
      ds.replaceChildren();
      hienThi.slice(0, TOI_DA).forEach(function (m, i) {
        var li = document.createElement('li');
        li.id = 'dc-mt-' + (++demId);
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', dangChon && dangChon.ma === m.ma ? 'true' : 'false');
        li.textContent = m.ten;
        li.addEventListener('mousedown', function (e) { e.preventDefault(); chon(m); });
        ds.appendChild(li);
        if (dangChon && dangChon.ma === m.ma && !tu) viTri = i;
      });
      if (!hienThi.length) {
        var trong = document.createElement('li');
        trong.className = 'dc-trong';
        trong.textContent = 'Không tìm thấy "' + o.value.trim() + '"';
        ds.appendChild(trong);
      } else if (hienThi.length > TOI_DA) {
        var them = document.createElement('li');
        them.className = 'dc-trong';
        them.textContent = 'Gõ thêm để lọc ' + (hienThi.length - TOI_DA) + ' mục còn lại';
        ds.appendChild(them);
      }
      if (tu || viTri >= hienThi.length) viTri = hienThi.length ? 0 : -1;
      danhDau();
    }

    function danhDau() {
      var cac = ds.querySelectorAll('[role=option]');
      cac.forEach(function (li, i) { li.classList.toggle('dc-dang', i === viTri); });
      if (viTri >= 0 && cac[viTri]) {
        o.setAttribute('aria-activedescendant', cac[viTri].id);
        cac[viTri].scrollIntoView({ block: 'nearest' });
      } else {
        o.removeAttribute('aria-activedescendant');
      }
    }

    function chon(m, imLang) {
      dangChon = m;
      o.value = m ? m.ten : '';
      dong();
      if (!imLang) khiChon(m);
    }

    // Rời ô mà chữ gõ chưa phải một mục: khớp đúng một mục thì lấy, không thì trả lại mục đang chọn
    function chot() {
      if (dangChon && o.value === dangChon.ten) return;
      if (!o.value.trim()) { if (dangChon) chon(null); return; }
      var dung = timTheoTen(danhSach, o.value);
      if (dung) { chon(dung); return; }
      if (hienThi.length === 1) { chon(hienThi[0]); return; }
      o.value = dangChon ? dangChon.ten : '';
    }

    o.addEventListener('focus', function () { o.select(); });
    o.addEventListener('click', mo);
    o.addEventListener('input', function () { viTri = 0; mo(); });
    o.addEventListener('blur', function () { chot(); dong(); });
    o.addEventListener('keydown', function (e) {
      var soMuc = Math.min(hienThi.length, TOI_DA);
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (ds.hidden) { mo(); return; }
        if (!soMuc) return;
        viTri = (viTri + (e.key === 'ArrowDown' ? 1 : -1) + soMuc) % soMuc;
        danhDau();
      } else if (e.key === 'Enter') {
        if (!ds.hidden) {
          e.preventDefault();
          if (viTri >= 0 && hienThi[viTri]) chon(hienThi[viTri]);
        }
      } else if (e.key === 'Escape') {
        if (!ds.hidden) { e.preventDefault(); o.value = dangChon ? dangChon.ten : ''; dong(); }
      } else if (e.key === 'Tab' && !ds.hidden && o.value !== (dangChon ? dangChon.ten : '') && viTri >= 0 && hienThi[viTri]) {
        chon(hienThi[viTri]);
      }
    });

    return {
      datDanhSach: function (moi, nhan) {
        danhSach = moi;
        dangChon = null;
        o.value = '';
        o.placeholder = nhan || nhanTrong;
        o.disabled = !moi.length;
        dong();
      },
      dangTai: function (chu) { danhSach = []; dangChon = null; o.value = ''; o.placeholder = chu; o.disabled = true; dong(); },
      chon: function (m) { chon(m, true); },
      giaTri: function () { return dangChon; },
      danhSach: function () { return danhSach; }
    };
  }

  // ---------- Khối địa chỉ ----------
  function khoiTao(khoi) {
    var an = khoi.querySelector('[data-dia-chi-gia-tri]');
    var duong = khoi.querySelector('[data-dia-chi-duong]');
    var ghiChu = khoi.querySelector('[data-dia-chi-ghi-chu]');
    var chonKhung = khoi.querySelector('.dia-chi-chon');
    var urlPhuong = khoi.dataset.urlPhuong;
    var lanTai = 0;

    function baoGhiChu(chu) { ghiChu.textContent = chu || ''; ghiChu.hidden = !chu; }

    function ghep() {
      if (chonKhung.hidden) { an.value = duong.value.trim(); return; }
      var tinh = oTinh.giaTri(), phuong = oPhuong.giaTri();
      an.value = [duong.value.trim(), phuong ? phuong.ten : '', tinh ? tinh.ten : ''].filter(Boolean).join(', ');
    }

    function taiPhuong(tinh) {
      var lan = ++lanTai;
      if (!tinh) { oPhuong.datDanhSach([], 'Chọn tỉnh trước'); return Promise.resolve([]); }
      oPhuong.dangTai('Đang tải phường / xã…');
      return tai(urlPhuong.replace(/\/0\/phuong$/, '/' + tinh.ma + '/phuong'), 'dc:p:' + tinh.ma)
        .then(function (ds) { if (lan === lanTai) oPhuong.datDanhSach(ds, 'Phường / xã'); return ds; })
        .catch(function () { if (lan === lanTai) oPhuong.datDanhSach([], 'Chưa tải được phường / xã'); return []; });
    }

    var oTinh = taoO(khoi.querySelector('[data-dc-combo=tinh]'), function (tinh) {
      baoGhiChu('');
      ghep();
      taiPhuong(tinh).then(function () {
        ghep();
        if (tinh) khoi.querySelector('[data-dc-combo=phuong] .dc-input').focus();
      });
    });
    var oPhuong = taoO(khoi.querySelector('[data-dc-combo=phuong]'), function () { baoGhiChu(''); ghep(); });

    // Tách "Số nhà, Phường X, Tỉnh Y" thành ba phần; phần nào không khớp danh mục thì để lại trong ô số nhà
    function tachChuoi(chuoi) {
      var phan = String(chuoi || '').split(',').map(function (p) { return p.trim(); }).filter(Boolean);
      var dsTinh = oTinh.danhSach();
      baoGhiChu('');
      var viTriTinh = -1, tinh = null;
      for (var i = phan.length - 1; i >= Math.max(0, phan.length - 2) && !tinh; i--) {
        tinh = timTheoTen(dsTinh, phan[i]);
        if (tinh) viTriTinh = i;
      }
      oTinh.chon(tinh);
      if (!tinh) {
        duong.value = phan.join(', ');
        taiPhuong(null);
        if (phan.length) baoGhiChu('Địa chỉ cũ chưa khớp danh sách tỉnh / phường mới, hãy chọn lại.');
        ghep();
        return;
      }
      taiPhuong(tinh).then(function (dsPhuong) {
        var phuong = viTriTinh > 0 ? timTheoTen(dsPhuong, phan[viTriTinh - 1]) : null;
        oPhuong.chon(phuong);
        if (!phuong && viTriTinh > 0) baoGhiChu('Chưa khớp phường / xã của địa chỉ cũ, hãy chọn lại.');
        duong.value = phan.slice(0, phuong ? viTriTinh - 1 : viTriTinh).join(', ');
        ghep();
      });
    }

    // API không dùng được: quay về một ô nhập địa chỉ đầy đủ như trước
    function cheDoNhapTay() {
      chonKhung.hidden = true;
      chonKhung.querySelectorAll('.dc-input').forEach(function (o) { o.required = false; });
      duong.placeholder = 'Địa chỉ đầy đủ';
      duong.value = an.value;
      baoGhiChu('Chưa tải được danh sách tỉnh / phường, nhập địa chỉ đầy đủ.');
    }

    duong.addEventListener('input', ghep);
    khoi.addEventListener('dia-chi:dat', function (e) {
      an.value = e.detail || '';
      if (chonKhung.hidden) duong.value = an.value;
      else tachChuoi(an.value);
    });

    oPhuong.datDanhSach([], 'Chọn tỉnh trước');
    tai(khoi.dataset.urlTinh, 'dc:tinh')
      .then(function (ds) {
        oTinh.datDanhSach(ds, 'Tỉnh / thành phố');
        tachChuoi(an.value);
      })
      .catch(cheDoNhapTay);
  }

  function chay() { document.querySelectorAll('[data-dia-chi]').forEach(khoiTao); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', chay);
  else chay();
})();
