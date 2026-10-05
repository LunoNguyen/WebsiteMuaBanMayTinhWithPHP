@php($laSua = $km->exists)
@extends('layouts.admin', ['title' => $laSua ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi', 'breadcrumb' => ['Quản lý', 'Khuyến mãi', $laSua ? $km->MA_CODE : 'Tạo mới']])

@php($loai = old('LOAI_KM', $km->LOAI_KM))

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>{{ $laSua ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi' }}</h1>
            <p>{{ $laSua ? $km->MAKM.' · đã dùng '.formatNum($km->DA_SUDUNG).($km->SOLUONG_MA ? '/'.formatNum($km->SOLUONG_MA) : '').' lượt' : 'Mỗi khách chỉ dùng mỗi mã một lần, mỗi đơn chỉ một mã.' }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.khuyenmai') }}" class="btn btn-outline">← Danh sách</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.khuyenmai.update', $km->MAKM) : route('admin.khuyenmai.store') }}">
        @csrf
        @if ($laSua) @method('PUT') @endif

        <div class="grid-7-5">
            <div class="card">
                <div class="card-header"><h3>Mức giảm và điều kiện</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <x-truong name="TENKM" label="Tên chương trình" :value="$km->TENKM" required maxlength="200" />
                        <x-truong name="MA_CODE" label="Mã code" :value="$km->MA_CODE" required maxlength="50" style="text-transform:uppercase"
                                  hint="Chữ không dấu, số, - hoặc _. Khách nhập mã này ở giỏ hàng." />
                    </div>
                    <div class="form-row">
                        <x-truong name="LOAI_KM" label="Loại giảm" :value="$km->LOAI_KM" required data-loai-km
                                  :options="['PhanTram' => 'Theo phần trăm (%)', 'SoTienCoDinh' => 'Số tiền cố định (₫)']" />
                        <x-truong name="GIATRI_KM" label="Giá trị giảm" type="number" :value="$km->GIATRI_KM !== null ? (float) $km->GIATRI_KM : null" required min="1" step="any"
                                  hint="Phần trăm (1–100) hoặc số tiền." />
                    </div>
                    <div class="form-row">
                        <div data-toi-da @if ($loai === 'SoTienCoDinh') hidden @endif>
                            <x-truong name="SOTIENTOIDA_KM" label="Giảm tối đa (₫)" type="number" :value="$km->SOTIENTOIDA_KM !== null ? (int) $km->SOTIENTOIDA_KM : null"
                                      min="1000" step="1000" hint="Bắt buộc với mã giảm theo %." />
                        </div>
                        <x-truong name="SOTIENTOITHIEU_NHANKM" label="Đơn tối thiểu (₫)" type="number" :value="(int) $km->SOTIENTOITHIEU_NHANKM" required min="0" step="1000"
                                  hint="Tính trên các sản phẩm được áp dụng. 0 là không yêu cầu." />
                    </div>
                    <div class="form-row cols-3">
                        <x-truong name="NGAYBD" label="Bắt đầu" type="datetime-local" :value="$km->NGAYBD" required />
                        <x-truong name="NGAYKT" label="Kết thúc" type="datetime-local" :value="$km->NGAYKT" required />
                        <x-truong name="SOLUONG_MA" label="Tổng số lượt" type="number" :value="$km->SOLUONG_MA" min="1" hint="Để trống là không giới hạn." />
                    </div>
                    <x-truong name="TRANGTHAI" label="Trạng thái" :value="$km->TRANGTHAI" required
                              :options="['HoatDong' => 'Hoạt động', 'TamDung' => 'Tạm dừng', 'HetHan' => 'Hết hạn']" />
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Sản phẩm áp dụng</h3></div>
                <div class="card-body">
                    <p class="form-hint" style="margin:0 0 10px">Không chọn sản phẩm nào thì mã áp dụng cho cả đơn. Chọn thì chỉ tính giảm trên các sản phẩm đó.</p>
                    <input type="search" class="form-control" placeholder="Lọc theo tên..." data-loc-sp style="margin-bottom:8px">
                    <div class="check-list">
                        @foreach ($sanPhamList as $sp)
                            <label class="form-check" data-ten="{{ mb_strtolower($sp->TENSP) }}">
                                <input type="checkbox" name="san_pham[]" value="{{ $sp->MASP }}" @checked(in_array($sp->MASP, $daChon, true))>
                                <span>{{ $sp->TENSP }} <span style="color:var(--text-muted)">· {{ formatVND($sp->DONGIA_SP) }}</span></span>
                            </label>
                        @endforeach
                    </div>
                    @error('san_pham') <div class="form-error">{{ $message }}</div> @enderror
                    @error('san_pham.*') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:20px">
            <div class="form-actions" style="border-top:none">
                <a href="{{ route('admin.khuyenmai') }}" class="btn btn-outline">Huỷ</a>
                <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu thay đổi' : 'Tạo khuyến mãi' }}</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.querySelector('[data-loai-km]').addEventListener('change', function () {
  document.querySelector('[data-toi-da]').hidden = this.value === 'SoTienCoDinh';
});
document.querySelector('[data-loc-sp]').addEventListener('input', function () {
  var q = this.value.trim().toLowerCase();
  document.querySelectorAll('.check-list [data-ten]').forEach(function (el) { el.hidden = q && el.dataset.ten.indexOf(q) === -1; });
});
</script>
@endpush
