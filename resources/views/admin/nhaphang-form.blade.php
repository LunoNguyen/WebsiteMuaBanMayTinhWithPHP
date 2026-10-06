@php($laSua = $pn->exists)
@extends('layouts.admin', ['title' => $laSua ? 'Sửa phiếu nhập' : 'Tạo phiếu nhập', 'breadcrumb' => ['Quản lý', 'Nhập hàng', $laSua ? $pn->MAPNH : 'Tạo mới']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>{{ $laSua ? 'Sửa phiếu '.$pn->MAPNH : 'Tạo phiếu nhập' }}</h1>
            <p>Tồn kho chỉ được cộng khi kho bấm "Hoàn tất nhập kho".</p>
        </div>
        <div class="page-header-right">
            <a href="{{ $laSua ? route('admin.nhaphang.show', $pn->MAPNH) : route('admin.nhaphang') }}" class="btn btn-outline">← Quay lại</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.nhaphang.update', $pn->MAPNH) : route('admin.nhaphang.store') }}" id="formPhieu">
        @csrf
        @if ($laSua) @method('PUT') @endif

        <div class="card">
            <div class="card-header"><h3>Thông tin phiếu</h3></div>
            <div class="card-body">
                <div class="form-row cols-3">
                    <x-truong name="MANCC" label="Nhà cung cấp" :value="$pn->MANCC" required :options="$nccList->pluck('TENNCC', 'MANCC')" />
                    <x-truong name="NGAY_DATMUA" label="Ngày đặt mua" type="datetime-local" :value="$pn->NGAY_DATMUA" required />
                    <x-truong name="NGAYGIAO" label="Ngày giao dự kiến" type="datetime-local" :value="$pn->NGAYGIAO" />
                </div>
                <div class="form-row cols-3">
                    <x-truong name="CHIETKHAU" label="Chiết khấu (%)" type="number" :value="(float) $pn->CHIETKHAU" required min="0" max="100" step="0.01" data-tinh />
                    <x-truong name="THUE_VAT" label="Thuế VAT (%)" type="number" :value="(float) $pn->THUE_VAT" required min="0" max="100" step="0.01" data-tinh />
                    <x-truong name="TRANGTHAI_THANHTOAN" label="Thanh toán NCC" :value="$pn->TRANGTHAI_THANHTOAN" required
                              :options="['ChuaThanhToan' => 'Chưa thanh toán', 'DaThanhToan' => 'Đã thanh toán', 'HoanTien' => 'Hoàn tiền']" />
                </div>
                <x-truong name="GHI_CHU" label="Ghi chú" type="textarea" :value="$pn->GHI_CHU" rows="2" />
            </div>
        </div>

        <div class="card" style="margin-top:20px">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                <h3>Sản phẩm nhập</h3>
                <div style="display:flex;gap:8px">
                    <button type="button" class="btn btn-sm btn-primary" id="btnMoModalChonNhieu">{!! icon('package', 15) !!} ＋ Chọn nhiều mặt hàng</button>
                    <button type="button" class="btn btn-sm btn-outline" data-them-dong>＋ Thêm 1 dòng</button>
                </div>
            </div>
            @error('dong') <div class="form-error" style="padding:0 20px">{{ $message }}</div> @enderror
            <div class="table-wrapper">
                <table class="line-table">
                    <thead><tr><th style="min-width:260px">Sản phẩm</th><th style="width:110px">Số lượng</th><th style="width:170px">Đơn giá nhập (₫)</th><th style="width:150px">Thành tiền</th><th>Ghi chú</th><th></th></tr></thead>
                    <tbody id="dongPhieu">
                        @foreach ($dong as $i => $d)
                            <tr data-dong>
                                <td>
                                    <select name="dong[{{ $i }}][MASP]" class="form-control @error("dong.$i.MASP") is-invalid @enderror" required>
                                        <option value="">— Chọn sản phẩm —</option>
                                        @foreach ($sanPhamList as $sp)
                                            <option value="{{ $sp->MASP }}" data-gia="{{ (float)$sp->DONGIA_SP }}" @selected(($d['MASP'] ?? null) === $sp->MASP)>{{ $sp->MASP }} · {{ $sp->TENSP }} (tồn {{ $sp->SOLUONGTON }})</option>
                                        @endforeach
                                    </select>
                                    @error("dong.$i.MASP") <div class="form-error">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <input type="number" name="dong[{{ $i }}][SOLUONG]" value="{{ $d['SOLUONG'] ?? 1 }}" min="1" class="form-control @error("dong.$i.SOLUONG") is-invalid @enderror" required data-tinh>
                                </td>
                                <td>
                                    <input type="number" name="dong[{{ $i }}][DONGIA_NHAP]" value="{{ isset($d['DONGIA_NHAP']) ? (float) $d['DONGIA_NHAP'] : '' }}" min="0" step="1000" class="form-control @error("dong.$i.DONGIA_NHAP") is-invalid @enderror" required data-tinh>
                                </td>
                                <td data-thanh-tien style="font-weight:600;white-space:nowrap">—</td>
                                <td><input type="text" name="dong[{{ $i }}][GHI_CHU]" value="{{ $d['GHI_CHU'] ?? '' }}" maxlength="200" class="form-control"></td>
                                <td><button type="button" class="btn-icon" title="Bỏ dòng" data-bo-dong>{!! icon('trash', 15) !!}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body" style="display:grid;justify-content:end">
                <div style="min-width:300px">
                    <div class="sum-row"><span>Tiền hàng</span><strong id="tienHang">0 ₫</strong></div>
                    <div class="sum-row"><span>Sau chiết khấu</span><strong id="sauCK">0 ₫</strong></div>
                    <div class="sum-row total"><span>Tổng cộng (gồm VAT)</span><strong id="tongCong">0 ₫</strong></div>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ $laSua ? route('admin.nhaphang.show', $pn->MAPNH) : route('admin.nhaphang') }}" class="btn btn-outline">Huỷ</a>
                <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu phiếu' : 'Tạo phiếu nhập' }}</button>
            </div>
        </div>
    </form>

    <!-- Modal Chọn nhiều mặt hàng cùng lúc -->
    <div id="modalChonNhieu" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.65);align-items:center;justify-content:center;padding:15px">
        <div class="card" style="width:960px;max-width:96vw;max-height:90vh;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;box-shadow:0 16px 40px rgba(0,0,0,0.5)">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border)">
                <h3 style="margin:0;font-size:16px;display:flex;align-items:center;gap:8px">{!! icon('package', 18) !!} Chọn nhiều mặt hàng nhập kho</h3>
                <button type="button" id="btnDongModalChonNhieu" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-secondary);line-height:1">&times;</button>
            </div>
            <div style="padding:12px 20px;background:var(--bg-main);border-bottom:1px solid var(--border);display:flex;gap:12px;flex-wrap:wrap;align-items:center">
                <div style="flex:1;min-width:240px">
                    <input type="text" id="timKiemSpModal" placeholder="Tìm kiếm theo mã SP, tên sản phẩm..." class="form-control" style="width:100%">
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <label style="font-size:12px;white-space:nowrap;margin:0;color:var(--text-secondary)">SL nhập chung:</label>
                    <input type="number" id="slMacDinhModal" value="10" min="1" class="form-control" style="width:80px">
                </div>
                <div style="display:flex;gap:6px">
                    <button type="button" class="btn btn-sm btn-outline" id="btnChonTatCa">Chọn tất cả</button>
                    <button type="button" class="btn btn-sm btn-outline" id="btnBoChonTatCa">Bỏ chọn</button>
                </div>
            </div>
            <div style="flex:1;overflow-y:auto;padding:0">
                <table class="table" style="width:100%;margin:0;border-collapse:collapse;font-size:13px">
                    <thead>
                        <tr style="position:sticky;top:0;background:var(--bg-card);z-index:2;border-bottom:2px solid var(--border)">
                            <th style="width:40px;text-align:center;padding:8px"><input type="checkbox" id="chkChonAllHeader"></th>
                            <th style="width:100px;padding:8px">Mã SP</th>
                            <th style="padding:8px">Tên sản phẩm</th>
                            <th style="width:120px;padding:8px">Loại SP</th>
                            <th style="width:80px;text-align:center;padding:8px">Tồn kho</th>
                            <th style="width:120px;text-align:right;padding:8px">Giá bán</th>
                            <th style="width:100px;padding:8px">SL nhập</th>
                            <th style="width:140px;padding:8px">Đơn giá nhập (₫)</th>
                        </tr>
                    </thead>
                    <tbody id="danhSachSpModal">
                        @foreach ($sanPhamList as $sp)
                            @php($giaGoiY = (float)$sp->DONGIA_SP > 0 ? round((float)$sp->DONGIA_SP * 0.8) : 0)
                            <tr class="dong-sp-modal" data-masp="{{ $sp->MASP }}" data-tensp="{{ mb_strtolower($sp->TENSP) }}" data-mancc="{{ $sp->MANCC }}">
                                <td style="text-align:center;padding:8px">
                                    <input type="checkbox" class="chk-chon-sp" value="{{ $sp->MASP }}">
                                </td>
                                <td style="padding:8px"><code style="font-weight:700;color:var(--primary)">{{ $sp->MASP }}</code></td>
                                <td style="font-weight:500;padding:8px">{{ $sp->TENSP }}</td>
                                <td style="font-size:12px;color:var(--text-secondary);padding:8px">{{ $sp->loaiSanPham?->TENLOAI ?? '—' }}</td>
                                <td style="text-align:center;padding:8px">
                                    <span class="badge {{ $sp->SOLUONGTON <= 10 ? 'badge-danger' : 'badge-neutral' }}">{{ $sp->SOLUONGTON }}</span>
                                </td>
                                <td style="text-align:right;padding:8px">{{ number_format($sp->DONGIA_SP, 0, ',', '.') }} ₫</td>
                                <td style="padding:8px">
                                    <input type="number" class="form-control modal-input-sl" min="1" value="10" style="padding:4px 6px;font-size:12px">
                                </td>
                                <td style="padding:8px">
                                    <input type="number" class="form-control modal-input-gia" min="0" step="1000" value="{{ $giaGoiY }}" style="padding:4px 6px;font-size:12px">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);background:var(--bg-card);display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;color:var(--text-secondary)" id="lblSoLuongDaChon">Đã chọn: <strong id="soLuongDaChonCount">0</strong> sản phẩm</span>
                <div style="display:flex;gap:10px">
                    <button type="button" class="btn btn-outline" id="btnHuyModalChonNhieu">Đóng</button>
                    <button type="button" class="btn btn-primary" id="btnXacNhanThemNhieu">{!! icon('check', 14) !!} Thêm vào phiếu nhập</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
  var body = document.getElementById('dongPhieu');
  var mau = body.querySelector('[data-dong]').cloneNode(true);
  var vnd = new Intl.NumberFormat('vi-VN');
  var soDong = body.querySelectorAll('[data-dong]').length;

  function tinh() {
    var tien = 0;
    body.querySelectorAll('[data-dong]').forEach(function (tr) {
      var sl = parseFloat(tr.querySelector('[name$="[SOLUONG]"]').value) || 0;
      var gia = parseFloat(tr.querySelector('[name$="[DONGIA_NHAP]"]').value) || 0;
      tr.querySelector('[data-thanh-tien]').textContent = vnd.format(sl * gia) + ' ₫';
      tien += sl * gia;
    });
    var ck = parseFloat(document.querySelector('[name="CHIETKHAU"]').value) || 0;
    var vat = parseFloat(document.querySelector('[name="THUE_VAT"]').value) || 0;
    var sau = tien * (1 - ck / 100);
    document.getElementById('tienHang').textContent = vnd.format(tien) + ' ₫';
    document.getElementById('sauCK').textContent = vnd.format(Math.round(sau)) + ' ₫';
    document.getElementById('tongCong').textContent = vnd.format(Math.round(sau * (1 + vat / 100))) + ' ₫';
  }

  function themMotDong(masp, soluong, dongia) {
    var tr = mau.cloneNode(true);
    tr.querySelectorAll('[name]').forEach(function (el) {
      el.name = el.name.replace(/dong\[\d+\]/, 'dong[' + soDong + ']');
      el.classList.remove('is-invalid');
      if (el.tagName === 'SELECT') {
        el.value = masp || '';
      } else if (el.name.endsWith('[SOLUONG]')) {
        el.value = soluong || 1;
      } else if (el.name.endsWith('[DONGIA_NHAP]')) {
        el.value = dongia !== undefined && dongia !== null ? dongia : '';
      } else {
        el.value = '';
      }
    });
    tr.querySelectorAll('.form-error').forEach(function (e) { e.remove(); });
    soDong++;
    body.appendChild(tr);
  }

  document.querySelector('[data-them-dong]').addEventListener('click', function () {
    themMotDong('', 1, '');
    tinh();
  });

  body.addEventListener('click', function (e) {
    var nut = e.target.closest('[data-bo-dong]');
    if (!nut) return;
    if (body.querySelectorAll('[data-dong]').length === 1) {
      if (typeof showToast === 'function') showToast('Phiếu cần ít nhất một sản phẩm', 'error');
      else alert('Phiếu cần ít nhất một sản phẩm');
      return;
    }
    nut.closest('[data-dong]').remove();
    tinh();
  });

  document.getElementById('formPhieu').addEventListener('input', function (e) {
    if (e.target.matches('[data-tinh]')) tinh();
  });

  // Ràng buộc lịch: Ngày giao dự kiến không được trước Ngày đặt mua
  var ngayDatMua = document.querySelector('[name="NGAY_DATMUA"]');
  var ngayGiao = document.querySelector('[name="NGAYGIAO"]');

  function capNhatRangBuocNgay() {
    if (ngayDatMua && ngayGiao && ngayDatMua.value) {
      ngayGiao.min = ngayDatMua.value;
      if (ngayGiao.value && ngayGiao.value < ngayDatMua.value) {
        ngayGiao.value = ngayDatMua.value;
      }
    }
  }

  if (ngayDatMua) {
    ngayDatMua.addEventListener('input', capNhatRangBuocNgay);
    ngayDatMua.addEventListener('change', capNhatRangBuocNgay);
    capNhatRangBuocNgay();
  }

  if (ngayGiao) {
    ngayGiao.addEventListener('change', function () {
      if (ngayDatMua && ngayDatMua.value && ngayGiao.value) {
        if (ngayGiao.value < ngayDatMua.value) {
          alert('Ngày giao dự kiến không được trước ngày đặt mua!');
          ngayGiao.value = ngayDatMua.value;
        }
      }
    });
  }

  document.getElementById('formPhieu').addEventListener('submit', function (e) {
    if (ngayDatMua && ngayGiao && ngayDatMua.value && ngayGiao.value) {
      if (ngayGiao.value < ngayDatMua.value) {
        e.preventDefault();
        alert('Ngày giao dự kiến không được trước ngày đặt mua!');
        ngayGiao.focus();
        return false;
      }
    }
  });

  // Tự động gợi ý đơn giá khi chọn sản phẩm trong dropdown
  body.addEventListener('change', function (e) {
    if (e.target.matches('select[name$="[MASP]"]')) {
      var opt = e.target.selectedOptions[0];
      var tr = e.target.closest('[data-dong]');
      var giaInput = tr.querySelector('[name$="[DONGIA_NHAP]"]');
      if (opt && opt.dataset.gia && (!giaInput.value || giaInput.value === '0')) {
        var giaBan = parseFloat(opt.dataset.gia) || 0;
        giaInput.value = Math.round(giaBan * 0.8);
      }
      tinh();
    }
  });

  // Modal Chọn nhiều mặt hàng
  var modal = document.getElementById('modalChonNhieu');
  var btnMoModal = document.getElementById('btnMoModalChonNhieu');
  var btnDongModal = document.getElementById('btnDongModalChonNhieu');
  var btnHuyModal = document.getElementById('btnHuyModalChonNhieu');
  var btnXacNhan = document.getElementById('btnXacNhanThemNhieu');
  var timKiemInput = document.getElementById('timKiemSpModal');
  var slMacDinhInput = document.getElementById('slMacDinhModal');
  var chkAllHeader = document.getElementById('chkChonAllHeader');
  var soLuongDaChonCount = document.getElementById('soLuongDaChonCount');

  function capNhatDem() {
    var cnt = modal.querySelectorAll('.chk-chon-sp:checked').length;
    soLuongDaChonCount.textContent = cnt;
  }

  function moModal() {
    modal.style.display = 'flex';
    // Đánh dấu những sản phẩm đã có trên phiếu
    var maspDaCo = [];
    body.querySelectorAll('select[name$="[MASP]"]').forEach(function (s) {
      if (s.value) maspDaCo.push(s.value);
    });

    modal.querySelectorAll('.dong-sp-modal').forEach(function (tr) {
      var m = tr.dataset.masp;
      var chk = tr.querySelector('.chk-chon-sp');
      if (maspDaCo.indexOf(m) !== -1) {
        tr.style.opacity = '0.5';
        chk.disabled = true;
        chk.checked = false;
        tr.title = 'Sản phẩm đã có trên phiếu';
      } else {
        tr.style.opacity = '1';
        chk.disabled = false;
        chk.checked = false;
        tr.title = '';
      }
    });

    chkAllHeader.checked = false;
    timKiemInput.value = '';
    locDanhSachModal();
    capNhatDem();
    setTimeout(function () { timKiemInput.focus(); }, 80);
  }

  function dongModal() {
    modal.style.display = 'none';
  }

  function locDanhSachModal() {
    var kw = (timKiemInput.value || '').trim().toLowerCase();
    modal.querySelectorAll('.dong-sp-modal').forEach(function (tr) {
      var m = tr.dataset.masp.toLowerCase();
      var t = tr.dataset.tensp.toLowerCase();
      var khop = !kw || m.indexOf(kw) !== -1 || t.indexOf(kw) !== -1;
      tr.style.display = khop ? '' : 'none';
    });
  }

  btnMoModal.addEventListener('click', moModal);
  btnDongModal.addEventListener('click', dongModal);
  btnHuyModal.addEventListener('click', dongModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) dongModal(); });

  timKiemInput.addEventListener('input', locDanhSachModal);

  slMacDinhInput.addEventListener('input', function () {
    var v = parseInt(this.value, 10) || 1;
    modal.querySelectorAll('.modal-input-sl').forEach(function (inp) {
      inp.value = v;
    });
  });

  chkAllHeader.addEventListener('change', function () {
    var c = this.checked;
    modal.querySelectorAll('.dong-sp-modal').forEach(function (tr) {
      if (tr.style.display !== 'none') {
        var chk = tr.querySelector('.chk-chon-sp');
        if (!chk.disabled) chk.checked = c;
      }
    });
    capNhatDem();
  });

  document.getElementById('btnChonTatCa').addEventListener('click', function () {
    modal.querySelectorAll('.dong-sp-modal').forEach(function (tr) {
      if (tr.style.display !== 'none') {
        var chk = tr.querySelector('.chk-chon-sp');
        if (!chk.disabled) chk.checked = true;
      }
    });
    chkAllHeader.checked = true;
    capNhatDem();
  });

  document.getElementById('btnBoChonTatCa').addEventListener('click', function () {
    modal.querySelectorAll('.chk-chon-sp').forEach(function (chk) { chk.checked = false; });
    chkAllHeader.checked = false;
    capNhatDem();
  });

  modal.addEventListener('change', function (e) {
    if (e.target.matches('.chk-chon-sp')) capNhatDem();
  });

  btnXacNhan.addEventListener('click', function () {
    var daChon = [];
    modal.querySelectorAll('.chk-chon-sp:checked').forEach(function (chk) {
      var tr = chk.closest('.dong-sp-modal');
      daChon.push({
        masp: chk.value,
        soluong: parseInt(tr.querySelector('.modal-input-sl').value, 10) || 1,
        dongia: parseFloat(tr.querySelector('.modal-input-gia').value) || 0
      });
    });

    if (daChon.length === 0) {
      alert('Vui lòng chọn ít nhất một mặt hàng để thêm!');
      return;
    }

    // Nếu dòng đầu tiên đang trống (chưa chọn sản phẩm nào), tận dụng dòng đầu tiên
    var dongDau = body.querySelector('[data-dong]');
    var dongDauSelect = dongDau ? dongDau.querySelector('select[name$="[MASP]"]') : null;
    var viTriBatDau = 0;

    if (dongDauSelect && !dongDauSelect.value && daChon.length > 0) {
      var item0 = daChon[0];
      dongDauSelect.value = item0.masp;
      dongDau.querySelector('[name$="[SOLUONG]"]').value = item0.soluong;
      dongDau.querySelector('[name$="[DONGIA_NHAP]"]').value = item0.dongia;
      viTriBatDau = 1;
    }

    // Thêm các sản phẩm còn lại
    for (var k = viTriBatDau; k < daChon.length; k++) {
      themMotDong(daChon[k].masp, daChon[k].soluong, daChon[k].dongia);
    }

    tinh();
    dongModal();
    if (typeof showToast === 'function') {
      showToast('Đã thêm thành công ' + daChon.length + ' mặt hàng vào phiếu nhập!');
    }
  });

  tinh();
})();
</script>
@endpush
