@extends('layouts.admin', ['title' => 'Sản phẩm', 'breadcrumb' => ['Danh mục', 'Sản phẩm']])

@section('content')
    @php
        $mauTrangThai = ['DangBan' => ['var(--green)', 'Đang bán'], 'HetHang' => ['var(--orange)', 'Hết hàng'], 'NgungBan' => ['var(--text-muted)', 'Ngừng bán']];
    @endphp
    <div class="page-header">
        <div class="page-header-left">
            <h1>Sản phẩm</h1>
            <p>
                {{ formatNum($dem[0][2]) }} sản phẩm{{ $filter === 'low_stock' ? ' tồn từ 20 trở xuống' : '' }}
                @if ($filter === 'low_stock')
                    · <a href="{{ url()->current().'?'.http_build_query(request()->except(['filter', 'page'])) }}">bỏ lọc tồn thấp</a>
                @endif
            </p>
        </div>
        <div class="page-header-right">
            <button type="button" class="btn btn-outline" onclick="exportTableCSV('spTable','sanpham')">{!! icon('download', 16) !!} Xuất CSV</button>
            <a href="{{ route('admin.sanpham.create') }}" class="btn btn-primary">{!! icon('plus', 16) !!} Thêm sản phẩm</a>
        </div>
    </div>

    @include('partials.thong-bao')

    <x-qt.dem :muc="$dem" :dang="$trangthai" />

    <div class="card" data-rt-vung="ds-sp" data-rt-khi="sp">
        <form method="GET" class="qt-toolbar">
            @foreach (['trangthai' => $trangthai, 'filter' => $filter] as $ten => $giaTri)
                @if ($giaTri !== '')
                    <input type="hidden" name="{{ $ten }}" value="{{ $giaTri }}">
                @endif
            @endforeach
            <x-qt.tim :value="$search" placeholder="Tìm tên hoặc mã sản phẩm" />
            <x-qt.chon name="maloai" :value="$maloai" :options="['' => 'Mọi loại'] + $loaiList" />
            <x-qt.chon name="mansx" :value="$mansx" :options="['' => 'Mọi nhà sản xuất'] + $nsxList" />
        </form>

        @if (empty($sanpham))
            <div class="qt-empty">
                {!! icon('laptop', 40) !!}
                <h3>Không có sản phẩm nào khớp điều kiện lọc</h3>
                <a href="{{ route('admin.sanpham') }}" class="btn btn-outline">Xoá bộ lọc</a>
            </div>
        @else
            <div class="table-wrapper">
                <table id="spTable">
                    <thead>
                        <tr><th>Sản phẩm</th><th>Loại</th><th>Nhà sản xuất</th><th class="num">Giá bán</th><th class="num">Tồn kho</th><th>Trạng thái</th><th>Ngày thêm</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($sanpham as $sp)
                            @php
                                [$mau, $nhan] = $mauTrangThai[$sp['TRANGTHAI']] ?? ['var(--text-secondary)', $sp['TRANGTHAI']];
                                $ton = (int) $sp['SOLUONGTON'];
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <label class="sp-thumb" title="Bấm để tải ảnh lên">
                                            @if (! empty($sp['ANH_CHINH']))
                                                <img src="{{ $sp['ANH_URL'] }}" alt="" loading="lazy" onerror="this.remove()">
                                            @endif
                                            {!! icon('laptop', 18) !!}
                                            <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-url="{{ route('admin.sanpham.anh', $sp['MASP']) }}" onchange="uploadAnhSP(this)">
                                        </label>
                                        <span>
                                            <a href="{{ route('admin.sanpham.edit', $sp['MASP']) }}" style="color:var(--text-primary);font-weight:600">{{ $sp['TENSP'] }}</a>
                                            <span class="qt-sub">{{ $sp['MASP'] }}{{ $sp['TENNCC'] ? ' · '.$sp['TENNCC'] : '' }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $sp['TENLOAI'] ?? '—' }}</td>
                                <td>{{ $sp['TENNSX'] ?? '—' }}</td>
                                <td class="num"><strong>{{ formatVND($sp['DONGIA_SP']) }}</strong></td>
                                <td class="num" @if ($ton <= 5) style="color:var(--red);font-weight:700" @elseif ($ton <= 20) style="color:var(--orange);font-weight:700" @endif>{{ formatNum($ton) }}</td>
                                <td><span class="badge-tt" style="--c:{{ $mau }}">{{ $nhan }}</span></td>
                                <td>{{ date('d/m/Y', strtotime($sp['NGAYTHEM'])) }}</td>
                                <td>
                                    <div class="qt-actions">
                                        <a href="{{ route('admin.sanpham.edit', $sp['MASP']) }}" class="btn-icon" title="Sửa" aria-label="Sửa {{ $sp['TENSP'] }}">{!! icon('pencil', 15) !!}</a>
                                        <x-nut-hanh-dong :action="route('admin.sanpham.trang-thai', $sp['MASP'])" method="PATCH"
                                            confirm="Thay đổi trạng thái sản phẩm?" class="btn-icon"
                                            :title="$sp['TRANGTHAI'] === 'DangBan' ? 'Ngừng bán' : 'Bật bán'"
                                            :aria-label="$sp['TRANGTHAI'] === 'DangBan' ? 'Ngừng bán' : 'Bật bán'">
                                            {!! $sp['TRANGTHAI'] === 'DangBan' ? icon('pause', 15) : icon('play', 15) !!}
                                        </x-nut-hanh-dong>
                                        <x-nut-hanh-dong :action="route('admin.sanpham.destroy', $sp['MASP'])" method="DELETE"
                                            :confirm="'Xoá sản phẩm '.$sp['TENSP'].'?'" class="btn-icon" title="Xoá" aria-label="Xoá {{ $sp['TENSP'] }}">
                                            {!! icon('trash', 15) !!}
                                        </x-nut-hanh-dong>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-qt.phan-trang :page="$page" :pages="$pages" :total="$total" :per-page="$perPage" don-vi="sản phẩm" />
        @endif
    </div>
@endsection

@push('scripts')
<script>
// Tải ảnh sản phẩm lên MinIO, xong thì thay ảnh trong ô ngay tại chỗ
function uploadAnhSP(input) {
  const file = input.files[0];
  if (!file) return;
  const box = input.closest('.sp-thumb');
  const fd = new FormData();
  fd.append('anh', file);
  box.classList.add('is-loading');
  fetch(input.dataset.url, {
    method: 'POST',
    body: fd,
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
  })
    .then(r => r.json())
    .then(d => {
      if (!d.success) { showToast(d.error || 'Tải ảnh thất bại', 'error'); return; }
      if (d.la_chinh) {
        let img = box.querySelector('img');
        if (!img) { img = document.createElement('img'); img.alt = ''; box.prepend(img); }
        img.src = d.url;
      }
      showToast('Đã tải ảnh lên', 'success');
    })
    .catch(() => showToast('Không kết nối được máy chủ', 'error'))
    .finally(() => { box.classList.remove('is-loading'); input.value = ''; });
}
</script>
@endpush
