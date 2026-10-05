@extends('layouts.admin', ['title' => 'Tạo đơn tại quầy', 'breadcrumb' => ['Quản lý', 'Đơn hàng', 'Tạo mới']])

@php($giaoHang = old('PHUONG_THUC_GH', 'TaiQuay'))

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Tạo đơn tại quầy</h1>
            <p>Tồn kho bị trừ ngay khi tạo đơn. Mỗi đơn dùng tối đa một mã, mỗi khách dùng mỗi mã một lần.</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.donhang') }}" class="btn btn-outline">← Danh sách</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ route('admin.donhang.store') }}" id="formDon">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3>Sản phẩm</h3>
                <button type="button" class="btn btn-sm btn-outline" data-them-dong>＋ Thêm dòng</button>
            </div>
            @error('san_pham') <div class="form-error" style="padding:0 20px">{{ $message }}</div> @enderror
            <div class="table-wrapper">
                <table class="line-table">
                    <thead><tr><th style="min-width:300px">Sản phẩm</th><th style="width:120px">Số lượng</th><th style="width:150px">Đơn giá</th><th style="width:150px">Thành tiền</th><th></th></tr></thead>
                    <tbody id="dongDon">
                        @foreach ($dong as $i => $d)
                            <tr data-dong>
                                <td>
                                    <select name="san_pham[{{ $i }}][MASP]" class="form-control @error("san_pham.$i.MASP") is-invalid @enderror" required>
                                        <option value="" data-gia="0" data-ton="0">— Chọn sản phẩm —</option>
                                        @foreach ($sanPhamList as $sp)
                                            <option value="{{ $sp->MASP }}" data-gia="{{ (float) $sp->DONGIA_SP }}" data-ton="{{ $sp->SOLUONGTON }}" @selected(($d['MASP'] ?? null) === $sp->MASP)>
                                                {{ $sp->TENSP }} (còn {{ $sp->SOLUONGTON }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("san_pham.$i.MASP") <div class="form-error">{{ $message }}</div> @enderror
                                </td>
                                <td><input type="number" name="san_pham[{{ $i }}][SOLUONG]" value="{{ $d['SOLUONG'] ?? 1 }}" min="1" class="form-control" required></td>
                                <td data-gia style="white-space:nowrap">—</td>
                                <td data-thanh-tien style="font-weight:600;white-space:nowrap">—</td>
                                <td><button type="button" class="btn-icon" title="Bỏ dòng" data-bo-dong>{!! icon('trash', 15) !!}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body" style="display:grid;justify-content:end">
                <div style="min-width:300px">
                    <div class="sum-row total"><span>Tạm tính</span><strong id="tamTinh">0 ₫</strong></div>
                    <div class="form-hint" style="text-align:right">Giảm giá (nếu có mã) được tính khi lưu.</div>
                </div>
            </div>
        </div>

        <div class="grid-2" style="margin-top:20px">
            <div class="card">
                <div class="card-header"><h3>Khách và nhận hàng</h3></div>
                <div class="card-body">
                    <x-truong name="MAKH" label="Khách hàng" data-khach
                              :options="$khachHangList->mapWithKeys(fn ($kh) => [$kh->MAKH => $kh->TENKH.($kh->SDT_KH ? ' · '.$kh->SDT_KH : '')])"
                              hint="Để trống nếu là khách lẻ. Chọn khách thì điền sẵn tên, SĐT, địa chỉ." />
                    <div class="form-row">
                        <x-truong name="TEN_NGUOINHAN" label="Tên người nhận" required maxlength="100" />
                        <x-truong name="SDT_NGUOINHAN" label="Số điện thoại" required inputmode="tel" />
                    </div>
                    <x-truong name="PHUONG_THUC_GH" label="Hình thức nhận hàng" :value="$giaoHang" required data-giao
                              :options="['TaiQuay' => 'Nhận tại cửa hàng', 'GiaoHang' => 'Giao tận nơi']" />
                    <div data-dia-chi @if ($giaoHang === 'TaiQuay') hidden @endif>
                        <x-truong name="DIACHI_GIAOHANG" label="Địa chỉ giao hàng" maxlength="300" />
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Thanh toán</h3></div>
                <div class="card-body">
                    <x-truong name="PHUONG_THUC" label="Phương thức" value="COD" required :options="$phuongThuc" />
                    <x-truong name="MA_CODE" label="Mã giảm giá" maxlength="50" style="text-transform:uppercase"
                              :hint="$maDangChay->isEmpty() ? 'Hiện không có mã nào còn hiệu lực.' : 'Mã đang chạy: '.$maDangChay->pluck('MA_CODE')->implode(', ')" />
                    <x-truong name="GHI_CHU" label="Ghi chú" type="textarea" rows="2" maxlength="500" />
                </div>
                <div class="form-actions">
                    <a href="{{ route('admin.donhang') }}" class="btn btn-outline">Huỷ</a>
                    <button type="submit" class="btn btn-primary">Tạo đơn</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
  var khach = @json($khachHangList->keyBy('MAKH')->map(fn ($kh) => ['ten' => $kh->TENKH, 'sdt' => $kh->SDT_KH, 'diachi' => $kh->DIACHI_KH]));
  var body = document.getElementById('dongDon');
  var mau = body.querySelector('[data-dong]').cloneNode(true);
  var vnd = new Intl.NumberFormat('vi-VN');
  var soDong = body.querySelectorAll('[data-dong]').length;

  function tinh() {
    var tong = 0;
    body.querySelectorAll('[data-dong]').forEach(function (tr) {
      var opt = tr.querySelector('select').selectedOptions[0];
      var sl = tr.querySelector('[name$="[SOLUONG]"]');
      var gia = parseFloat(opt.dataset.gia) || 0;
      var ton = parseInt(opt.dataset.ton, 10) || 0;
      if (ton) { sl.max = ton; }
      tr.querySelector('[data-gia]').textContent = gia ? vnd.format(gia) + ' ₫' : '—';
      tr.querySelector('[data-thanh-tien]').textContent = gia ? vnd.format(gia * (parseInt(sl.value, 10) || 0)) + ' ₫' : '—';
      tong += gia * (parseInt(sl.value, 10) || 0);
    });
    document.getElementById('tamTinh').textContent = vnd.format(tong) + ' ₫';
  }

  document.querySelector('[data-them-dong]').addEventListener('click', function () {
    var tr = mau.cloneNode(true);
    tr.querySelectorAll('[name]').forEach(function (el) {
      el.name = el.name.replace(/san_pham\[\d+\]/, 'san_pham[' + soDong + ']');
      el.classList.remove('is-invalid');
      if (el.tagName === 'SELECT') { el.selectedIndex = 0; } else { el.value = 1; }
    });
    tr.querySelectorAll('.form-error').forEach(function (e) { e.remove(); });
    soDong++;
    body.appendChild(tr);
    tinh();
  });

  body.addEventListener('click', function (e) {
    var nut = e.target.closest('[data-bo-dong]');
    if (!nut) return;
    if (body.querySelectorAll('[data-dong]').length === 1) { showToast('Đơn cần ít nhất một sản phẩm', 'error'); return; }
    nut.closest('[data-dong]').remove();
    tinh();
  });
  body.addEventListener('input', tinh);
  body.addEventListener('change', tinh);

  document.querySelector('[data-khach]').addEventListener('change', function () {
    var kh = khach[this.value];
    if (!kh) return;
    document.querySelector('[name="TEN_NGUOINHAN"]').value = kh.ten || '';
    document.querySelector('[name="SDT_NGUOINHAN"]').value = kh.sdt || '';
    document.querySelector('[name="DIACHI_GIAOHANG"]').value = kh.diachi || '';
  });
  document.querySelector('[data-giao]').addEventListener('change', function () {
    document.querySelector('[data-dia-chi]').hidden = this.value !== 'GiaoHang';
  });
  tinh();
})();
</script>
@endpush
