@php($laSua = $sp->exists)
@extends('layouts.admin', ['title' => $laSua ? 'Sửa sản phẩm' : 'Thêm sản phẩm', 'breadcrumb' => ['Quản lý', 'Sản phẩm', $laSua ? $sp->MASP : 'Thêm mới']])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>{{ $laSua ? 'Sửa sản phẩm' : 'Thêm sản phẩm' }}</h1>
            <p>{{ $laSua ? $sp->MASP.' · '.$sp->TENSP : 'Mã sản phẩm được tạo tự động khi lưu.' }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('admin.sanpham') }}" class="btn btn-outline">← Danh sách</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <form method="POST" action="{{ $laSua ? route('admin.sanpham.update', $sp->MASP) : route('admin.sanpham.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($laSua) @method('PUT') @endif

        <div class="grid-7-5">
            <div class="card">
                <div class="card-header"><h3>Thông tin bán hàng</h3></div>
                <div class="card-body">
                    <x-truong name="TENSP" label="Tên sản phẩm" :value="$sp->TENSP" required maxlength="200" />
                    <div class="form-row">
                        <x-truong name="MALOAI" label="Loại sản phẩm" :value="$sp->MALOAI" required :options="$loaiList->pluck('TENLOAI', 'MALOAI')" />
                        <x-truong name="MANSX" label="Nhà sản xuất" :value="$sp->MANSX" required :options="$nsxList->pluck('TENNSX', 'MANSX')" />
                    </div>
                    <div class="form-row">
                        <x-truong name="MANCC" label="Nhà cung cấp" :value="$sp->MANCC" :options="$nccList->pluck('TENNCC', 'MANCC')" />
                        <x-truong name="DONVT" label="Đơn vị tính" :value="$sp->DONVT" maxlength="50" placeholder="Cái, Bộ..." />
                    </div>
                    <div class="form-row cols-3">
                        <x-truong name="DONGIA_SP" label="Giá bán (₫)" type="number" :value="$sp->DONGIA_SP !== null ? (int) $sp->DONGIA_SP : null" required min="0" step="1000" />
                        @if ($laSua)
                            <div class="form-group">
                                <label class="form-label">Tồn kho</label>
                                <input class="form-control" value="{{ formatNum($sp->SOLUONGTON) }}" disabled>
                                <div class="form-hint">Thay đổi qua phiếu nhập hoặc đơn hàng.</div>
                            </div>
                        @else
                            <x-truong name="SOLUONGTON" label="Tồn kho ban đầu" type="number" :value="$sp->SOLUONGTON" required min="0" />
                        @endif
                        <x-truong name="TRANGTHAI" label="Trạng thái" :value="$sp->TRANGTHAI" required
                                  :options="['DangBan' => 'Đang bán', 'HetHang' => 'Hết hàng', 'NgungBan' => 'Ngừng bán']" />
                    </div>
                    @if ($laSua)
                        <x-truong name="GHI_CHU_GIA" label="Ghi chú khi đổi giá" maxlength="200" hint="Chỉ lưu khi giá bán thay đổi, vào lịch sử giá." />
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Cấu hình</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <x-truong name="CPU" label="CPU" :value="$sp->moTa?->CPU" maxlength="100" />
                        <x-truong name="RAM" label="RAM" :value="$sp->moTa?->RAM" maxlength="100" />
                    </div>
                    <div class="form-row">
                        <x-truong name="ROM" label="Ổ cứng" :value="$sp->moTa?->ROM" maxlength="100" />
                        <x-truong name="VGA" label="Card đồ hoạ" :value="$sp->moTa?->VGA" maxlength="100" />
                    </div>
                    <div class="form-row">
                        <x-truong name="MANHINH" label="Màn hình" :value="$sp->moTa?->MANHINH" maxlength="100" />
                        <x-truong name="PIN" label="Pin" :value="$sp->moTa?->PIN" maxlength="100" />
                    </div>
                    <x-truong name="KHAC" label="Thông tin khác" type="textarea" :value="$sp->moTa?->KHAC" rows="3" />
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:20px">
            <div class="card-header">
                <h3>Ảnh sản phẩm @if ($anhList->isNotEmpty())<span style="font-weight:400;color:var(--text-muted)">({{ $anhList->count() }})</span>@endif</h3>
            </div>
            <div class="card-body">
                <div class="sp-gallery">
                    @foreach ($anhList as $anh)
                        <figure @class(['sp-gallery-item', 'is-main' => $anh->LA_ANH_CHINH])>
                            <div class="sp-gallery-img">
                                {!! icon('laptop', 28) !!}
                                <img src="{{ $anh->url }}" alt="" loading="lazy" onerror="this.remove()">
                                @if ($anh->LA_ANH_CHINH)<span class="sp-gallery-tag">Ảnh chính</span>@endif
                            </div>
                            <figcaption>
                                @unless ($anh->LA_ANH_CHINH)
                                    <button type="submit" form="anhChinh{{ $anh->MAANH }}" class="btn btn-sm btn-outline">Đặt làm ảnh chính</button>
                                @endunless
                                <button type="submit" form="xoaAnh{{ $anh->MAANH }}" class="btn-icon" title="Xoá ảnh" aria-label="Xoá ảnh">{!! icon('trash', 15) !!}</button>
                            </figcaption>
                        </figure>
                    @endforeach

                    <label class="sp-gallery-add">
                        <input type="file" name="anh[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-chon-anh>
                        {!! icon('plus', 22) !!}
                        <strong>Chọn ảnh</strong>
                        <span>JPG, PNG, WEBP, GIF · tối đa {{ config('minio.max_kb') / 1024 }} MB/ảnh, 10 ảnh/lần</span>
                    </label>
                </div>
                <div class="sp-gallery sp-gallery-new" data-xem-truoc hidden></div>
                <p class="form-hint" data-ghi-chu-anh hidden>Ảnh mới được tải lên MinIO khi bấm "{{ $laSua ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}".{{ $anhList->isEmpty() ? ' Ảnh đầu tiên sẽ là ảnh chính.' : '' }}</p>
                @error('anh') <div class="form-error">{{ $message }}</div> @enderror
                @foreach ($errors->get('anh.*') as $loiAnh)
                    <div class="form-error">{{ $loiAnh[0] }}</div>
                @endforeach
            </div>
        </div>

        <div class="card" style="margin-top:20px">
            <div class="form-actions" style="border-top:none">
                <a href="{{ route('admin.sanpham') }}" class="btn btn-outline">Huỷ</a>
                <button type="submit" class="btn btn-primary">{{ $laSua ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}</button>
            </div>
        </div>
    </form>

    {{-- Form ẩn cho nút trong khối ảnh (không lồng form vào form chính) --}}
    @foreach ($anhList as $anh)
        <form id="anhChinh{{ $anh->MAANH }}" method="POST" action="{{ route('admin.sanpham.anh.chinh', [$sp->MASP, $anh->MAANH]) }}" hidden>
            @csrf @method('PATCH')
        </form>
        <form id="xoaAnh{{ $anh->MAANH }}" method="POST" action="{{ route('admin.sanpham.anh.xoa', [$sp->MASP, $anh->MAANH]) }}" hidden
              onsubmit="return confirm('Xoá ảnh này khỏi sản phẩm?')">
            @csrf @method('DELETE')
        </form>
    @endforeach

    @if ($lichSuGia->isNotEmpty())
        <div class="card" style="margin-top:20px">
            <div class="card-header"><h3>Lịch sử giá</h3></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Ngày</th><th>Giá cũ</th><th>Giá mới</th><th>Người đổi</th><th>Ghi chú</th></tr></thead>
                    <tbody>
                        @foreach ($lichSuGia as $ls)
                            <tr>
                                <td>{{ $ls->NGAY_CAPNHAT?->format('d/m/Y H:i') }}</td>
                                <td>{{ $ls->DONGIA_CU !== null ? formatVND($ls->DONGIA_CU) : '—' }}</td>
                                <td><strong>{{ formatVND($ls->DONGIA_MOI) }}</strong></td>
                                <td>{{ $ls->MANV_CAPNHAT ?? '—' }}</td>
                                <td>{{ $ls->GHI_CHU ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
// Xem trước ảnh vừa chọn; ảnh chỉ thật sự tải lên khi lưu form
document.querySelector('[data-chon-anh]').addEventListener('change', function () {
  var khung = document.querySelector('[data-xem-truoc]');
  khung.replaceChildren();
  Array.from(this.files).forEach(function (file) {
    var fig = document.createElement('figure');
    fig.className = 'sp-gallery-item';
    var o = document.createElement('div');
    o.className = 'sp-gallery-img';
    var img = document.createElement('img');
    img.alt = '';
    img.src = URL.createObjectURL(file);
    o.appendChild(img);
    var cap = document.createElement('figcaption');
    cap.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(1) + ' MB';
    fig.append(o, cap);
    khung.appendChild(fig);
  });
  khung.hidden = this.files.length === 0;
  document.querySelector('[data-ghi-chu-anh]').hidden = this.files.length === 0;
});
</script>
@endpush
