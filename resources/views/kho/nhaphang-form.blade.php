@extends('layouts.kho', ['title' => 'Tạo phiếu nhập kho'])

@push('styles')
@include('partials.kho.style-trang')
<style>
/* Chuẩn font Inter toàn trang */
body, input, select, textarea, button, table {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}
.k-form-wrap {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 1200px;
  width: 100%;
}
.k-card {
  background: var(--wc);
  border: 1px solid var(--wb);
  border-radius: var(--wr);
  padding: 18px 20px;
}
.k-card-h {
  font-size: 14px;
  font-weight: 700;
  color: var(--wt);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.k-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.k-fg {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.k-fg label {
  font-size: 12px;
  font-weight: 600;
  color: var(--wm);
}
.k-fg .req {
  color: var(--red);
}
.k-input, .k-select, .k-textarea {
  background: var(--wc2);
  border: 1px solid var(--wb);
  border-radius: 8px;
  padding: 9px 12px;
  font-size: 13px;
  color: var(--wt);
  outline: none;
  transition: border-color .15s;
}
.k-input:focus, .k-select:focus, .k-textarea:focus {
  border-color: var(--blue);
}
.k-tbl-wrap {
  overflow-x: auto;
  border: 1px solid var(--wb);
  border-radius: 8px;
}
.k-tbl {
  width: 100%;
  border-collapse: collapse;
}
.k-tbl th {
  padding: 10px 12px;
  font-size: 11px;
  font-weight: 700;
  color: var(--wm);
  background: var(--wc2);
  border-bottom: 1px solid var(--wb);
  text-align: left;
  white-space: nowrap;
}
.k-tbl td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--wb);
  vertical-align: middle;
  background: var(--wc);
}
.k-tbl tr:last-child td {
  border-bottom: none;
}
.k-sum-box {
  margin-left: auto;
  width: 320px;
  padding: 14px 16px;
  background: var(--wc2);
  border: 1px solid var(--wb);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 13px;
}
.k-sum-r {
  display: flex;
  justify-content: space-between;
  color: var(--wm);
}
.k-sum-r strong {
  color: var(--wt);
}
.k-sum-r.total {
  border-top: 1px solid var(--wb);
  padding-top: 10px;
  font-size: 14.5px;
  font-weight: 800;
  color: var(--green);
}
.k-sum-r.total strong {
  color: var(--green);
}
.btn-del {
  background: none;
  border: none;
  color: var(--red);
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background .15s;
}
.btn-del:hover {
  background: rgba(200, 30, 30, .1);
}
.date-hint {
  font-size: 10.5px;
  color: var(--wm);
  margin-top: 2px;
}
/* Panel nhập thông tin mới (NCC / SP) */
.new-panel {
  margin-top: 8px;
  padding: 12px 14px;
  background: rgba(58, 86, 228, .07);
  border: 1px dashed var(--blue);
  border-radius: 8px;
  display: none;
  flex-direction: column;
  gap: 10px;
  animation: fadeIn .18s ease;
}
.new-panel.show { display: flex; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-4px) } to { opacity:1; transform:none } }
.new-panel-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--blue);
  text-transform: uppercase;
  letter-spacing: .04em;
}
.new-panel .k-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.sp-new-panel {
  margin-top: 8px;
  padding: 10px 12px;
  background: rgba(58, 86, 228, .07);
  border: 1px dashed var(--blue);
  border-radius: 8px;
  display: none;
  gap: 8px;
  flex-direction: column;
  font-size: 12px;
  animation: fadeIn .18s ease;
}
.sp-new-panel.show { display: flex; }
.sp-new-panel .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.sp-new-panel label { font-size: 11px; font-weight: 600; color: var(--blue); margin-bottom: 2px; display:block; }
@media (max-width: 900px) {
  .k-grid-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="wsh">
@include('partials.kho.sidebar')

<div class="wmn">
  <!-- TopBar chuẩn giao diện Kho -->
  <div class="kho-topbar">
    <button type="button" class="w-menu-btn" onclick="toggleKhoSidebar()" aria-label="Mở menu">{!! icon('menu', 18) !!}</button>
    <span style="display:flex;color:var(--wm)">{!! icon('package', 18) !!}</span>
    <h2>Tạo phiếu nhập kho</h2>
    <div class="kto-user">
      <x-theme-toggle />
      <div style="text-align:right">
        <div style="font-size:12px;font-weight:700;color:var(--wt)">{{ auth()->user()->tenHienThi() }}</div>
        <div style="font-size:10px;color:var(--wm)">Nhân viên Kho &bull; {{ auth()->user()->MANV ?? '—' }}</div>
      </div>
      <div class="kto-av">{{ mb_strtoupper(mb_substr(auth()->user()->tenHienThi(), 0, 2)) }}</div>
    </div>
  </div>

  <!-- Content chính -->
  <div class="wct">
    <div class="k-form-wrap">
      <div class="wph">
        <div>
          <h1>Lập phiếu nhập kho mới</h1>
          <p>Tạo phiếu nhập hàng từ nhà cung cấp &middot; Tồn kho tự động tăng khi kho bấm "Hoàn tất nhập kho"</p>
        </div>
        <a href="{{ route('kho.nhaphang') }}" class="wbn wb-out">← Quay lại danh sách phiếu</a>
      </div>

      @if (isset($errors) && $errors->any())
        <div style="padding:12px 16px;border-radius:8px;background:rgba(200,30,30,.12);border:1px solid rgba(200,30,30,.3);color:var(--red);font-size:13px;font-weight:600">
          ⚠ Có lỗi xảy ra: {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('kho.nhaphang.store') }}" id="formPhieuKho">
        @csrf

        <!-- 1. Thông tin chung phiếu nhập -->
        <div class="k-card">
          <div class="k-card-h">1. Thông tin chung phiếu nhập</div>
          <div class="k-grid-3">
            {{-- ===== NHÀ CUNG CẤP (Select có sẵn + tuỳ chọn tạo mới) ===== --}}
            <div class="k-fg">
              <label>Nhà cung cấp <span class="req">*</span></label>
              <?php $currMancc = old('MANCC', $pn->MANCC); ?>
              <select name="MANCC" id="selectMancc" class="k-select" required>
                <option value="">— Chọn nhà cung cấp —</option>
                @foreach ($nccList as $ncc)
                  <option value="{{ $ncc->MANCC }}" @selected($currMancc === $ncc->MANCC)>{{ $ncc->TENNCC }}</option>
                @endforeach
                <option value="__new__" @selected($currMancc === '__new__')>＋ Thêm nhà cung cấp mới (tự nhập tay)...</option>
              </select>

              {{-- Panel nhập thông tin NCC mới nếu chọn __new__ --}}
              <div class="new-panel {{ $currMancc === '__new__' ? 'show' : '' }}" id="nccNewPanel">
                <div class="new-panel-title">📦 Thông tin nhà cung cấp mới</div>
                <div class="k-grid-2">
                  <div class="k-fg">
                    <label>Tên nhà cung cấp <span class="req">*</span></label>
                    <input type="text" name="ncc_moi_ten" id="nccMoiTen" class="k-input" placeholder="VD: Công ty Công nghệ ABC" value="{{ old('ncc_moi_ten') }}" style="font-size:12px">
                  </div>
                  <div class="k-fg">
                    <label>Số điện thoại</label>
                    <input type="text" name="ncc_moi_sdt" class="k-input" placeholder="0xxx..." value="{{ old('ncc_moi_sdt') }}" style="font-size:12px">
                  </div>
                </div>
                <div class="k-grid-2">
                  <div class="k-fg">
                    <label>Địa chỉ</label>
                    <input type="text" name="ncc_moi_diachi" class="k-input" placeholder="Địa chỉ NCC" value="{{ old('ncc_moi_diachi') }}" style="font-size:12px">
                  </div>
                  <div class="k-fg">
                    <label>Email</label>
                    <input type="email" name="ncc_moi_email" class="k-input" placeholder="email@ncc.com" value="{{ old('ncc_moi_email') }}" style="font-size:12px">
                  </div>
                </div>
                <div style="font-size:11px;color:var(--wm)">⚑ NCC sẽ được tạo tự động khi bạn lưu phiếu.</div>
              </div>
            </div>

            <div class="k-fg">
              <label>Ngày đặt mua <span class="req">*</span></label>
              <input type="datetime-local" name="NGAY_DATMUA" id="ngayDatMua" class="k-input" required value="{{ old('NGAY_DATMUA', now()->format('Y-m-d\TH:i')) }}">
              <span class="date-hint">Thời điểm tạo đơn nhập</span>
            </div>
            <div class="k-fg">
              <label>Ngày giao dự kiến</label>
              <input type="datetime-local" name="NGAYGIAO" id="ngayGiao" class="k-input" value="{{ old('NGAYGIAO') }}">
              <span class="date-hint">Không được trước ngày đặt mua</span>
            </div>
          </div>

          <div class="k-grid-3" style="margin-top:16px">
            <div class="k-fg">
              <label>Chiết khấu (%)</label>
              <input type="number" name="CHIETKHAU" class="k-input" min="0" max="100" step="0.01" value="{{ old('CHIETKHAU', 0) }}" data-tinh>
            </div>
            <div class="k-fg">
              <label>Thuế VAT (%)</label>
              <input type="number" name="THUE_VAT" class="k-input" min="0" max="100" step="0.01" value="{{ old('THUE_VAT', 10) }}" data-tinh>
            </div>
            <div class="k-fg">
              <label>Thanh toán nhà cung cấp <span class="req">*</span></label>
              <select name="TRANGTHAI_THANHTOAN" class="k-select" required>
                <option value="ChuaThanhToan" @selected(old('TRANGTHAI_THANHTOAN', 'ChuaThanhToan') === 'ChuaThanhToan')>Chưa thanh toán</option>
                <option value="DaThanhToan" @selected(old('TRANGTHAI_THANHTOAN') === 'DaThanhToan')>Đã thanh toán</option>
                <option value="HoanTien" @selected(old('TRANGTHAI_THANHTOAN') === 'HoanTien')>Hoàn tiền</option>
              </select>
            </div>
          </div>

          <div class="k-fg" style="margin-top:16px">
            <label>Ghi chú phiếu nhập</label>
            <textarea name="GHI_CHU" rows="2" class="k-textarea" placeholder="Ghi chú nội bộ lô hàng, điều kiện giao nhận...">{{ old('GHI_CHU') }}</textarea>
          </div>
        </div>

        <!-- 2. Danh sách mặt hàng nhập kho -->
        <div class="k-card" style="margin-top:16px">
          <div class="k-card-h">
            <span>2. Danh sách mặt hàng nhập kho</span>
            <div style="display:flex;gap:8px">
              <button type="button" class="wbn wb-pri" id="btnMoModalKho">{!! icon('package', 15) !!} ＋ Chọn nhiều mặt hàng cùng lúc</button>
              <button type="button" class="wbn wb-out" id="btnThemDongKho">＋ Thêm 1 dòng</button>
            </div>
          </div>

          <div class="k-tbl-wrap">
            <table class="k-tbl">
              <thead>
                <tr>
                  <th style="min-width:320px">Sản phẩm</th>
                  <th style="width:110px">Số lượng</th>
                  <th style="width:170px">Đơn giá nhập (₫)</th>
                  <th style="width:150px">Thành tiền</th>
                  <th>Ghi chú</th>
                  <th style="width:40px;text-align:center"></th>
                </tr>
              </thead>
              <tbody id="dongPhieuKho">
                <?php
                  $dongList = old('dong', !empty($dong) ? $dong : [['MASP' => '', 'SOLUONG' => 1, 'DONGIA_NHAP' => '', 'GHI_CHU' => '']]);
                ?>
                @foreach ($dongList as $i => $d)
                  <?php
                    $mVal = $d['MASP'] ?? '';
                    $isNewSp = ($mVal === '__new__');
                  ?>
                  <tr data-dong>
                    <td>
                      {{-- Select chọn sản phẩm có sẵn hoặc tự nhập mới --}}
                      <select name="dong[{{ $i }}][MASP]" class="k-select sp-select" required style="width:100%">
                        <option value="">— Chọn sản phẩm —</option>
                        @foreach ($sanPhamList as $sp)
                          <option value="{{ $sp->MASP }}"
                            data-gia="{{ (float)$sp->DONGIA_SP }}"
                            data-mancc="{{ $sp->MANCC }}"
                            @selected($mVal === $sp->MASP)>
                            {{ $sp->MASP }} · {{ $sp->TENSP }} (Tồn: {{ $sp->SOLUONGTON }})
                          </option>
                        @endforeach
                        <option value="__new__" @selected($isNewSp)>＋ Thêm sản phẩm mới (tự nhập tay)...</option>
                      </select>

                      {{-- Panel nhập thêm thông tin SP mới nếu chọn __new__ --}}
                      <div class="sp-new-panel {{ $isNewSp ? 'show' : '' }}">
                        <div class="new-panel-title">🖥 Thông tin sản phẩm mới</div>
                        <div class="k-fg">
                          <label>Tên sản phẩm <span class="req">*</span></label>
                          <input type="text" name="dong[{{ $i }}][TENSP_MOI]" class="k-input inp-sp-ten" value="{{ $d['TENSP_MOI'] ?? '' }}" placeholder="VD: Laptop Dell Inspiron 15..." style="font-size:12px">
                        </div>
                        <div class="row2">
                          <div class="k-fg">
                            <label>Loại sản phẩm</label>
                            <select name="dong[{{ $i }}][MALOAI_MOI]" class="k-select sel-loai-moi" style="font-size:12px">
                              <option value="">— Mặc định —</option>
                              @foreach ($loaiList as $loai)
                                <option value="{{ $loai->MALOAI }}" @selected(($d['MALOAI_MOI'] ?? '') === $loai->MALOAI)>{{ $loai->TENLOAI }}</option>
                              @endforeach
                              <option value="__new__" @selected(($d['MALOAI_MOI'] ?? '') === '__new__')>＋ Tự nhập loại mới...</option>
                            </select>
                            <input type="text" name="dong[{{ $i }}][TENLOAI_TU_NHAP]" class="k-input inp-loai-moi" value="{{ $d['TENLOAI_TU_NHAP'] ?? '' }}" placeholder="Nhập tên loại sản phẩm mới..." style="font-size:12px;margin-top:4px;{{ ($d['MALOAI_MOI'] ?? '') === '__new__' ? '' : 'display:none;' }}">
                          </div>
                          <div class="k-fg">
                            <label>Nhà sản xuất</label>
                            <select name="dong[{{ $i }}][MANSX_MOI]" class="k-select sel-nsx-moi" style="font-size:12px">
                              <option value="">— Mặc định —</option>
                              @foreach ($nsxList as $nsx)
                                <option value="{{ $nsx->MANSX }}" @selected(($d['MANSX_MOI'] ?? '') === $nsx->MANSX)>{{ $nsx->TENNSX }}</option>
                              @endforeach
                              <option value="__new__" @selected(($d['MANSX_MOI'] ?? '') === '__new__')>＋ Tự nhập NSX mới...</option>
                            </select>
                            <input type="text" name="dong[{{ $i }}][TENNSX_TU_NHAP]" class="k-input inp-nsx-moi" value="{{ $d['TENNSX_TU_NHAP'] ?? '' }}" placeholder="Nhập tên nhà sản xuất mới..." style="font-size:12px;margin-top:4px;{{ ($d['MANSX_MOI'] ?? '') === '__new__' ? '' : 'display:none;' }}">
                          </div>
                        </div>
                        <div class="k-fg">
                          <label>Giá bán dự kiến (₫)</label>
                          <input type="number" name="dong[{{ $i }}][DONGIA_BAN_MOI]" class="k-input inp-sp-giaban" value="{{ $d['DONGIA_BAN_MOI'] ?? '' }}" min="0" step="1000" placeholder="0" style="font-size:12px">
                        </div>
                        <div style="font-size:11px;color:var(--wm)">⚑ Tự động lưu vào danh mục SP khi tạo phiếu nhập (tồn ban đầu = 0).</div>
                      </div>
                    </td>
                    <td>
                      <input type="number" name="dong[{{ $i }}][SOLUONG]" value="{{ $d['SOLUONG'] ?? 1 }}" min="1" class="k-input" required style="width:100%" data-tinh>
                    </td>
                    <td>
                      <input type="number" name="dong[{{ $i }}][DONGIA_NHAP]" value="{{ $d['DONGIA_NHAP'] ?? '' }}" min="0" step="1000" class="k-input inp-dongia-nhap" placeholder="0" required style="width:100%" data-tinh>
                      <div class="dongia-hint" style="font-size:11px;color:var(--blue);font-weight:600;margin-top:3px;min-height:16px"></div>
                    </td>
                    <td data-thanh-tien style="font-weight:700;color:var(--wt);white-space:nowrap">—</td>
                    <td>
                      <input type="text" name="dong[{{ $i }}][GHI_CHU]" value="{{ $d['GHI_CHU'] ?? '' }}" maxlength="200" class="k-input" placeholder="Ghi chú dòng" style="width:100%">
                    </td>
                    <td style="text-align:center">
                      <button type="button" class="btn-del" data-bo-dong title="Bỏ dòng">{!! icon('trash', 15) !!}</button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div style="margin-top:16px;display:flex;justify-content:flex-end">
            <div class="k-sum-box">
              <div class="k-sum-r"><span>Tiền hàng:</span><strong id="tienHangKho">0 ₫</strong></div>
              <div class="k-sum-r"><span>Sau chiết khấu:</span><strong id="sauCKKho">0 ₫</strong></div>
              <div class="k-sum-r total"><span>Tổng cộng (VAT):</span><strong id="tongCongKho">0 ₫</strong></div>
            </div>
          </div>

          <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px">
            <a href="{{ route('kho.nhaphang') }}" class="wbn wb-out">Huỷ</a>
            <button type="submit" class="wbn wb-suc" style="padding:10px 20px;font-size:13px;border-radius:8px">
              {!! icon('check', 15) !!} Tạo phiếu nhập kho
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<!-- Modal Chọn nhiều mặt hàng cùng lúc cho Kho -->
<div id="modalChonNhieuKho" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.65);align-items:center;justify-content:center;padding:15px">
  <div style="background:var(--wc);border:1px solid var(--wb);width:960px;max-width:96vw;max-height:90vh;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;box-shadow:0 16px 40px rgba(0,0,0,0.5)">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--wb)">
      <h3 style="margin:0;font-size:15px;color:var(--wt);display:flex;align-items:center;gap:8px">{!! icon('package', 18) !!} Chọn nhiều mặt hàng nhập kho</h3>
      <button type="button" id="btnDongModalKho" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--wm);line-height:1">&times;</button>
    </div>
    <div style="padding:12px 20px;background:var(--wc2);border-bottom:1px solid var(--wb);display:flex;gap:12px;flex-wrap:wrap;align-items:center">
      <div style="flex:1;min-width:240px">
        <input type="text" id="timKiemSpKho" placeholder="Tìm kiếm theo mã SKU, tên sản phẩm..." class="k-input" style="width:100%">
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <label style="font-size:12px;white-space:nowrap;margin:0;color:var(--wm)">SL nhập chung:</label>
        <input type="number" id="slMacDinhKho" value="10" min="1" class="k-input" style="width:80px">
      </div>
      <div style="display:flex;gap:6px">
        <button type="button" class="wbn wb-out" id="btnChonTatCaKho" style="padding:6px 11px;font-size:12px">Chọn tất cả</button>
        <button type="button" class="wbn wb-out" id="btnBoChonTatCaKho" style="padding:6px 11px;font-size:12px">Bỏ chọn</button>
      </div>
    </div>
    <div style="flex:1;overflow-y:auto;padding:0">
      <table class="k-tbl" style="margin:0">
        <thead>
          <tr style="position:sticky;top:0;background:var(--wc);z-index:2;border-bottom:2px solid var(--wb)">
            <th style="width:40px;text-align:center"><input type="checkbox" id="chkHeaderKho"></th>
            <th style="width:100px">Mã SP</th>
            <th>Tên sản phẩm</th>
            <th style="width:120px">Loại SP</th>
            <th style="width:80px;text-align:center">Tồn kho</th>
            <th style="width:120px;text-align:right">Giá bán</th>
            <th style="width:100px">SL nhập</th>
            <th style="width:140px">Đơn giá nhập (₫)</th>
          </tr>
        </thead>
        <tbody id="danhSachSpKho">
          @foreach ($sanPhamList as $sp)
            @php($giaGoiY = (float)$sp->DONGIA_SP > 0 ? round((float)$sp->DONGIA_SP * 0.8) : 0)
            <tr class="dong-sp-kho" data-masp="{{ $sp->MASP }}" data-tensp="{{ mb_strtolower($sp->TENSP) }}" data-mancc="{{ $sp->MANCC }}">
              <td style="text-align:center">
                <input type="checkbox" class="chk-sp-kho" value="{{ $sp->MASP }}">
              </td>
              <td><span style="font-family:monospace;font-weight:700;color:var(--blue)">{{ $sp->MASP }}</span></td>
              <td style="font-weight:600;color:var(--wt)">
                {{ $sp->TENSP }}
                <span class="badge-da-co" style="display:none;font-size:11px;font-weight:600;padding:2px 7px;border-radius:6px;background:rgba(58,86,228,.15);color:var(--blue);margin-left:6px">✓ Đã có trong phiếu</span>
              </td>
              <td style="font-size:12px;color:var(--wm)">{{ $sp->loaiSanPham?->TENLOAI ?? '—' }}</td>
              <td style="text-align:center">
                <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;background:{{ $sp->SOLUONGTON <= 10 ? 'rgba(200,30,30,.15)' : 'rgba(58,86,228,.15)' }};color:{{ $sp->SOLUONGTON <= 10 ? 'var(--red)' : 'var(--blue)' }}">
                  {{ $sp->SOLUONGTON }}
                </span>
              </td>
              <td style="text-align:right;font-size:12.5px;color:var(--wm)">{{ number_format($sp->DONGIA_SP, 0, ',', '.') }} ₫</td>
              <td>
                <input type="number" class="k-input inp-sl-kho" min="1" value="10" style="padding:4px 6px;font-size:12px;width:100%">
              </td>
              <td>
                <input type="number" class="k-input inp-gia-kho" min="0" step="1000" value="{{ $giaGoiY }}" style="padding:4px 6px;font-size:12px;width:100%">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--wb);background:var(--wc);display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:13px;color:var(--wm)">Đã chọn: <strong id="demChonKho" style="color:var(--blue)">0</strong> sản phẩm</span>
      <div style="display:flex;gap:10px">
        <button type="button" class="wbn wb-out" id="btnHuyModalKho">Đóng</button>
        <button type="button" class="wbn wb-pri" id="btnXacNhanKho">{!! icon('check', 14) !!} Thêm vào phiếu nhập</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var body = document.getElementById('dongPhieuKho');
  var vnd = new Intl.NumberFormat('vi-VN');
  var soDong = body.querySelectorAll('[data-dong]').length;
  var mauDong = body.querySelector('[data-dong]').cloneNode(true);

  // ====== RÀNG BUỘC LỊCH ======
  var ngayDatMua = document.getElementById('ngayDatMua');
  var ngayGiao   = document.getElementById('ngayGiao');

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
      if (ngayDatMua && ngayDatMua.value && ngayGiao.value && ngayGiao.value < ngayDatMua.value) {
        alert('Ngày giao dự kiến không được trước ngày đặt mua!');
        ngayGiao.value = ngayDatMua.value;
      }
    });
  }

  // ====== NHÀ CUNG CẤP: HIỆN PANEL TỰ NHẬP NẾU CHỌN __new__ ======
  var selectMancc = document.getElementById('selectMancc');
  var nccNewPanel = document.getElementById('nccNewPanel');

  function capNhatPanelNcc() {
    if (!selectMancc || !nccNewPanel) return;
    if (selectMancc.value === '__new__') {
      nccNewPanel.classList.add('show');
      var tenInp = document.getElementById('nccMoiTen');
      if (tenInp) setTimeout(function() { tenInp.focus(); }, 100);
    } else {
      nccNewPanel.classList.remove('show');
    }
  }
  if (selectMancc) {
    selectMancc.addEventListener('change', capNhatPanelNcc);
    capNhatPanelNcc();
  }

  // ====== HIỂN THỊ GỢI Ý TIỀN TỆ ĐƠN GIÁ NHẬP ======
  function capNhatGoiYGia(tr) {
    var giaInp = tr.querySelector('.inp-dongia-nhap');
    var hint = tr.querySelector('.dongia-hint');
    if (!giaInp || !hint) return;
    var v = parseFloat(giaInp.value) || 0;
    hint.textContent = v > 0 ? (vnd.format(v) + ' ₫') : '';
  }

  // ====== XỬ LÝ DÒNG SẢN PHẨM: ĐỔI SELECT SP ======
  function initDongSp(tr) {
    var selectSp = tr.querySelector('.sp-select');
    var panelSp  = tr.querySelector('.sp-new-panel');
    var giaInp   = tr.querySelector('.inp-dongia-nhap');

    function xuLyDoiSp() {
      if (!selectSp) return;

      // Ngăn chọn trùng lặp sản phẩm đã có ở dòng khác
      if (selectSp.value && selectSp.value !== '__new__') {
        var maspChon = selectSp.value;
        var trung = false;
        body.querySelectorAll('[data-dong]').forEach(function(otherTr) {
          if (otherTr !== tr) {
            var otherSel = otherTr.querySelector('.sp-select');
            if (otherSel && otherSel.value === maspChon) {
              trung = true;
            }
          }
        });
        if (trung) {
          alert('Sản phẩm ' + maspChon + ' đã có trong phiếu nhập! Vui lòng chỉnh số lượng ở dòng đã có.');
          selectSp.value = '';
          if (panelSp) panelSp.classList.remove('show');
          capNhatGoiYGia(tr);
          tinh();
          return;
        }
      }

      if (selectSp.value === '__new__') {
        if (panelSp) panelSp.classList.add('show');
        var tenInp = tr.querySelector('.inp-sp-ten');
        if (tenInp) setTimeout(function() { tenInp.focus(); }, 100);
      } else {
        if (panelSp) panelSp.classList.remove('show');
        var opt = selectSp.selectedOptions[0];
        if (opt && opt.dataset.gia) {
          var giaBan = parseFloat(opt.dataset.gia) || 0;
          if (giaBan > 0 && (!giaInp.value || giaInp.value === '0')) {
            giaInp.value = Math.round(giaBan * 0.8);
          }
        }
      }
      capNhatGoiYGia(tr);
      tinh();
    }

    if (selectSp) {
      selectSp.addEventListener('change', xuLyDoiSp);
      if (selectSp.value === '__new__' && panelSp) {
        panelSp.classList.add('show');
      }
    }

    if (giaInp) {
      giaInp.addEventListener('input', function() {
        capNhatGoiYGia(tr);
      });
    }

    var selLoai = tr.querySelector('.sel-loai-moi');
    var inpLoai = tr.querySelector('.inp-loai-moi');
    if (selLoai && inpLoai) {
      selLoai.addEventListener('change', function () {
        if (this.value === '__new__') {
          inpLoai.style.display = '';
          setTimeout(function() { inpLoai.focus(); }, 60);
        } else {
          inpLoai.style.display = 'none';
        }
      });
      if (selLoai.value === '__new__') inpLoai.style.display = '';
    }

    var selNsx = tr.querySelector('.sel-nsx-moi');
    var inpNsx = tr.querySelector('.inp-nsx-moi');
    if (selNsx && inpNsx) {
      selNsx.addEventListener('change', function () {
        if (this.value === '__new__') {
          inpNsx.style.display = '';
          setTimeout(function() { inpNsx.focus(); }, 60);
        } else {
          inpNsx.style.display = 'none';
        }
      });
      if (selNsx.value === '__new__') inpNsx.style.display = '';
    }

    capNhatGoiYGia(tr);
  }

  // Init các dòng có sẵn
  body.querySelectorAll('[data-dong]').forEach(initDongSp);

  // ====== TÍNH TỔNG ======
  function tinh() {
    var tien = 0;
    body.querySelectorAll('[data-dong]').forEach(function (tr) {
      var sl  = parseFloat(tr.querySelector('[name$="[SOLUONG]"]').value) || 0;
      var gia = parseFloat(tr.querySelector('[name$="[DONGIA_NHAP]"]').value) || 0;
      tr.querySelector('[data-thanh-tien]').textContent = vnd.format(sl * gia) + ' ₫';
      tien += sl * gia;
      capNhatGoiYGia(tr);
    });
    var ck  = parseFloat(document.querySelector('[name="CHIETKHAU"]').value) || 0;
    var vat = parseFloat(document.querySelector('[name="THUE_VAT"]').value) || 0;
    var sau = tien * (1 - ck / 100);
    document.getElementById('tienHangKho').textContent  = vnd.format(tien) + ' ₫';
    document.getElementById('sauCKKho').textContent     = vnd.format(Math.round(sau)) + ' ₫';
    document.getElementById('tongCongKho').textContent  = vnd.format(Math.round(sau * (1 + vat / 100))) + ' ₫';
  }

  // ====== THÊM 1 DÒNG MỚI ======
  function themMotDong(masp, soluong, dongia) {
    var tr = mauDong.cloneNode(true);
    tr.querySelectorAll('[name]').forEach(function (el) {
      el.name = el.name.replace(/dong\[\d+\]/, 'dong[' + soDong + ']');
      if (el.tagName === 'SELECT') {
        if (el.classList.contains('sp-select')) {
          el.value = masp || '';
        } else {
          el.value = '';
        }
      } else if (el.name.endsWith('[SOLUONG]')) {
        el.value = soluong || 1;
      } else if (el.name.endsWith('[DONGIA_NHAP]')) {
        el.value = (dongia !== undefined && dongia !== null) ? dongia : '';
      } else {
        el.value = '';
      }
    });

    var panel = tr.querySelector('.sp-new-panel');
    if (panel) panel.classList.remove('show');

    var inpLoai = tr.querySelector('.inp-loai-moi');
    if (inpLoai) { inpLoai.style.display = 'none'; inpLoai.value = ''; }
    var inpNsx = tr.querySelector('.inp-nsx-moi');
    if (inpNsx) { inpNsx.style.display = 'none'; inpNsx.value = ''; }

    soDong++;
    body.appendChild(tr);
    initDongSp(tr);
    tinh();
  }

  document.getElementById('btnThemDongKho').addEventListener('click', function () {
    themMotDong('', 1, '');
    var cuoi = body.querySelector('[data-dong]:last-child .sp-select');
    if (cuoi) cuoi.focus();
  });

  body.addEventListener('click', function (e) {
    var nut = e.target.closest('[data-bo-dong]');
    if (!nut) return;
    if (body.querySelectorAll('[data-dong]').length === 1) {
      alert('Phiếu nhập cần ít nhất một sản phẩm!');
      return;
    }
    nut.closest('[data-dong]').remove();
    tinh();
  });

  document.getElementById('formPhieuKho').addEventListener('input', function (e) {
    if (e.target.matches('[data-tinh]')) tinh();
  });

  // ====== VALIDATE KHI SUBMIT FORM ======
  document.getElementById('formPhieuKho').addEventListener('submit', function (e) {
    // 1. Kiểm tra NCC
    if (!selectMancc.value) {
      e.preventDefault();
      alert('Vui lòng chọn nhà cung cấp!');
      selectMancc.focus();
      return false;
    }
    if (selectMancc.value === '__new__') {
      var nccTen = document.getElementById('nccMoiTen').value.trim();
      if (!nccTen) {
        e.preventDefault();
        alert('Vui lòng nhập tên nhà cung cấp mới!');
        document.getElementById('nccMoiTen').focus();
        return false;
      }
    }

    // 2. Kiểm tra các dòng sản phẩm
    var loiSp = false;
    var loiLoai = false;
    var loiNsx = false;
    body.querySelectorAll('[data-dong]').forEach(function(tr) {
      var sel = tr.querySelector('.sp-select');
      if (sel && sel.value === '__new__') {
        var tenSp = tr.querySelector('.inp-sp-ten').value.trim();
        if (!tenSp) {
          loiSp = true;
          tr.querySelector('.inp-sp-ten').focus();
        }
        var selL = tr.querySelector('.sel-loai-moi');
        var inpL = tr.querySelector('.inp-loai-moi');
        if (selL && selL.value === '__new__' && inpL && !inpL.value.trim()) {
          loiLoai = true;
          inpL.focus();
        }
        var selN = tr.querySelector('.sel-nsx-moi');
        var inpN = tr.querySelector('.inp-nsx-moi');
        if (selN && selN.value === '__new__' && inpN && !inpN.value.trim()) {
          loiNsx = true;
          inpN.focus();
        }
      }
    });
    if (loiSp) {
      e.preventDefault();
      alert('Vui lòng nhập tên cho sản phẩm mới!');
      return false;
    }
    if (loiLoai) {
      e.preventDefault();
      alert('Vui lòng nhập tên loại sản phẩm mới!');
      return false;
    }
    if (loiNsx) {
      e.preventDefault();
      alert('Vui lòng nhập tên nhà sản xuất mới!');
      return false;
    }

    // 3. Kiểm tra ngày
    if (ngayDatMua && ngayGiao && ngayDatMua.value && ngayGiao.value) {
      if (ngayGiao.value < ngayDatMua.value) {
        e.preventDefault();
        alert('Ngày giao dự kiến không được trước ngày đặt mua!');
        ngayGiao.focus();
        return false;
      }
    }
  });

  // ====== MODAL CHỌN NHIỀU MẶT HÀNG ======
  var modal      = document.getElementById('modalChonNhieuKho');
  var btnMo      = document.getElementById('btnMoModalKho');
  var btnDong    = document.getElementById('btnDongModalKho');
  var btnHuy     = document.getElementById('btnHuyModalKho');
  var btnXacNhan = document.getElementById('btnXacNhanKho');
  var timKiem    = document.getElementById('timKiemSpKho');
  var slChung    = document.getElementById('slMacDinhKho');
  var chkHead    = document.getElementById('chkHeaderKho');
  var demEl      = document.getElementById('demChonKho');

  function capNhatDem() {
    demEl.textContent = modal.querySelectorAll('.chk-sp-kho:checked').length;
  }

  function moModal() {
    modal.style.display = 'flex';

    // Thu thập các MASP đã chọn ở bảng ngoài
    var daCo = {};
    body.querySelectorAll('[data-dong]').forEach(function(tr) {
      var sel = tr.querySelector('.sp-select');
      if (sel && sel.value && sel.value !== '__new__') {
        daCo[sel.value] = true;
      }
    });

    modal.querySelectorAll('.dong-sp-kho').forEach(function(tr) {
      var m = tr.dataset.masp;
      var chk = tr.querySelector('.chk-sp-kho');
      var slInp = tr.querySelector('.inp-sl-kho');
      var giaInp = tr.querySelector('.inp-gia-kho');
      var badge = tr.querySelector('.badge-da-co');

      if (daCo[m]) {
        // Đã có trong phiếu ở ngoài: không được tích chọn thêm, được chỉnh SL ở ngoài
        if (chk) {
          chk.checked = false;
          chk.disabled = true;
        }
        if (slInp) slInp.disabled = true;
        if (giaInp) giaInp.disabled = true;
        tr.style.opacity = '0.5';
        tr.title = 'Mặt hàng này đã có trong phiếu nhập. Bạn có thể chỉnh số lượng trực tiếp tại bảng ngoài.';
        if (badge) badge.style.display = 'inline-block';
      } else {
        // Chưa có trong phiếu: cho phép chọn bình thường
        if (chk) {
          chk.checked = false;
          chk.disabled = false;
        }
        if (slInp) slInp.disabled = false;
        if (giaInp) giaInp.disabled = false;
        tr.style.opacity = '1';
        tr.title = '';
        if (badge) badge.style.display = 'none';
      }
    });

    chkHead.checked = false;
    timKiem.value = '';
    locModal();
    capNhatDem();
    setTimeout(function() { timKiem.focus(); }, 80);
  }

  function dongModal() { modal.style.display = 'none'; }

  function locModal() {
    var kw = (timKiem.value || '').trim().toLowerCase();
    modal.querySelectorAll('.dong-sp-kho').forEach(function(tr) {
      var m = tr.dataset.masp.toLowerCase();
      var t = tr.dataset.tensp.toLowerCase();
      tr.style.display = (!kw || m.indexOf(kw) !== -1 || t.indexOf(kw) !== -1) ? '' : 'none';
    });
  }

  btnMo.addEventListener('click', moModal);
  btnDong.addEventListener('click', dongModal);
  btnHuy.addEventListener('click', dongModal);
  modal.addEventListener('click', function(e) { if (e.target === modal) dongModal(); });
  timKiem.addEventListener('input', locModal);

  slChung.addEventListener('input', function() {
    var v = parseInt(this.value, 10) || 1;
    modal.querySelectorAll('.inp-sl-kho').forEach(function(inp) { inp.value = v; });
  });

  chkHead.addEventListener('change', function() {
    var c = this.checked;
    modal.querySelectorAll('.dong-sp-kho').forEach(function(tr) {
      if (tr.style.display !== 'none') {
        var chk = tr.querySelector('.chk-sp-kho');
        if (chk && !chk.disabled) chk.checked = c;
      }
    });
    capNhatDem();
  });

  document.getElementById('btnChonTatCaKho').addEventListener('click', function() {
    modal.querySelectorAll('.dong-sp-kho').forEach(function(tr) {
      if (tr.style.display !== 'none') {
        var chk = tr.querySelector('.chk-sp-kho');
        if (chk && !chk.disabled) chk.checked = true;
      }
    });
    chkHead.checked = true;
    capNhatDem();
  });

  document.getElementById('btnBoChonTatCaKho').addEventListener('click', function() {
    modal.querySelectorAll('.chk-sp-kho').forEach(function(chk) { chk.checked = false; });
    chkHead.checked = false;
    capNhatDem();
  });

  modal.addEventListener('change', function(e) {
    if (e.target.matches('.chk-sp-kho')) capNhatDem();
  });

  btnXacNhan.addEventListener('click', function() {
    var items = [];
    modal.querySelectorAll('.chk-sp-kho:checked').forEach(function(chk) {
      if (chk.disabled) return;
      var tr = chk.closest('.dong-sp-kho');
      items.push({
        masp: chk.value,
        soluong: parseInt(tr.querySelector('.inp-sl-kho').value, 10) || 1,
        dongia: parseFloat(tr.querySelector('.inp-gia-kho').value) || 0
      });
    });

    if (items.length === 0) {
      alert('Vui lòng chọn ít nhất một mặt hàng mới (chưa có trong phiếu)!');
      return;
    }

    // Nếu dòng đầu tiên đang trống (chưa chọn SP nào), điền vào dòng đầu tiên
    var dongDau = body.querySelector('[data-dong]');
    var selDau  = dongDau ? dongDau.querySelector('.sp-select') : null;
    var batDau  = 0;

    if (selDau && !selDau.value && items.length > 0) {
      var it0 = items[0];
      selDau.value = it0.masp;
      dongDau.querySelector('[name$="[SOLUONG]"]').value = it0.soluong;
      dongDau.querySelector('[name$="[DONGIA_NHAP]"]').value = it0.dongia;
      capNhatGoiYGia(dongDau);
      batDau = 1;
    }

    // Thêm các dòng còn lại
    for (var i = batDau; i < items.length; i++) {
      themMotDong(items[i].masp, items[i].soluong, items[i].dongia);
    }

    tinh();
    dongModal();
  });

  tinh();
})();
</script>
@endpush
