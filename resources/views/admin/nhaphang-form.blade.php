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
            <div class="card-header">
                <h3>Sản phẩm nhập</h3>
                <button type="button" class="btn btn-sm btn-outline" data-them-dong>＋ Thêm dòng</button>
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
                                            <option value="{{ $sp->MASP }}" @selected(($d['MASP'] ?? null) === $sp->MASP)>{{ $sp->MASP }} · {{ $sp->TENSP }} (tồn {{ $sp->SOLUONGTON }})</option>
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

  document.querySelector('[data-them-dong]').addEventListener('click', function () {
    var tr = mau.cloneNode(true);
    tr.querySelectorAll('[name]').forEach(function (el) {
      el.name = el.name.replace(/dong\[\d+\]/, 'dong[' + soDong + ']');
      el.classList.remove('is-invalid');
      if (el.tagName === 'SELECT') { el.selectedIndex = 0; } else { el.value = el.name.endsWith('[SOLUONG]') ? 1 : ''; }
    });
    tr.querySelectorAll('.form-error').forEach(function (e) { e.remove(); });
    soDong++;
    body.appendChild(tr);
    tinh();
  });

  body.addEventListener('click', function (e) {
    var nut = e.target.closest('[data-bo-dong]');
    if (!nut) return;
    if (body.querySelectorAll('[data-dong]').length === 1) { showToast('Phiếu cần ít nhất một sản phẩm', 'error'); return; }
    nut.closest('[data-dong]').remove();
    tinh();
  });

  document.getElementById('formPhieu').addEventListener('input', function (e) { if (e.target.matches('[data-tinh]')) tinh(); });
  tinh();
})();
</script>
@endpush
