@extends('layouts.admin', ['title' => 'Quản lý Sản phẩm', 'breadcrumb' => ['Quản lý', 'Sản phẩm']])

@section('content')

      <!-- Header -->
      <div class="page-header">
        <div class="page-header-left">
          <h1>Quản lý Sản phẩm</h1>
          <p>Tổng cộng <strong style="color:var(--blue-light)">{{ formatNum($total) }}</strong> sản phẩm{{ $search ? ' khớp "'.e($search).'"' : '' }}</p>
        </div>
        <div class="page-header-right">
          <a href="{{ url('admin/sanpham-them') }}" class="btn btn-primary">＋ Thêm sản phẩm</a>
          <button class="btn btn-outline" onclick="exportTableCSV('spTable','sanpham')">Xuất CSV</button>
        </div>
      </div>

      @include('partials.thong-bao')

      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET" action="{{ route('admin.sanpham') }}" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
          <div class="search-box" style="min-width:280px">
            <span class="si">{!! icon('search') !!}</span>
            <input type="text" name="q" value="{{ $search }}" placeholder="Tìm tên hoặc mã sản phẩm..." />
          </div>
          <select name="maloai" class="form-control" style="width:180px">
            <option value="">Tất cả loại SP</option>
            @foreach ($loaiList as $l)
            <option value="{{ $l['MALOAI'] }}" {{ $maloai === $l['MALOAI'] ? 'selected' : '' }}>
              {{ $l['TENLOAI'] }}
            </option>
            @endforeach
          </select>
          <select name="mansx" class="form-control" style="width:160px">
            <option value="">Tất cả NSX</option>
            @foreach ($nsxList as $n)
            <option value="{{ $n['MANSX'] }}" {{ $mansx === $n['MANSX'] ? 'selected' : '' }}>
              {{ $n['TENNSX'] }}
            </option>
            @endforeach
          </select>
          <select name="trangthai" class="form-control" style="width:150px">
            <option value="">Tất cả trạng thái</option>
            <option value="DangBan" {{ $trangthai==='DangBan' ? 'selected' : '' }}>Đang Bán</option>
            <option value="HetHang" {{ $trangthai==='HetHang' ? 'selected' : '' }}>Hết Hàng</option>
            <option value="NgungBan" {{ $trangthai==='NgungBan' ? 'selected' : '' }}>Ngừng Bán</option>
          </select>
          <button type="submit" class="btn btn-primary">Lọc</button>
          <a href="{{ route('admin.sanpham') }}" class="btn btn-outline">↩ Reset</a>
        </form>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="table-wrapper">
          <table id="spTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Sản phẩm</th>
                <th>Loại</th>
                <th>NSX</th>
                <th>Giá bán</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Ngày thêm</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($sanpham as $i => $sp)
              <tr>
                <td style="color:var(--text-muted);font-size:12px">{{ $offset + $i + 1 }}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <label class="sp-thumb" title="Bấm để tải ảnh lên">
                      @if (!empty($sp['ANH_CHINH']))
                        <img src="{{ $sp['ANH_URL'] }}" alt="" loading="lazy" onerror="this.remove()">
                      @endif
                      {!! icon('laptop', 18) !!}
                      <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-url="{{ route('admin.sanpham.anh', $sp['MASP']) }}" onchange="uploadAnhSP(this)">
                    </label>
                    <div>
                      <div style="font-weight:600;font-size:13px;color:var(--text-primary)">{{ $sp['TENSP'] }}</div>
                      <div style="font-size:11px;color:var(--text-muted)">
                        <code style="background:var(--bg-main);padding:1px 5px;border-radius:3px">{{ $sp['MASP'] }}</code>
                        &bull; {{ $sp['DONVT'] ?? 'Cái' }}
                        @if ($sp['MANCC']) &bull; {{ $sp['TENNCC'] }} @endif
                      </div>
                    </div>
                  </div>
                </td>
                <td style="font-size:13px">
                  <span style="background:rgba(58,86,228,0.1);color:var(--blue-light);padding:2px 8px;border-radius:6px;font-size:12px">
                    {{ $sp['TENLOAI'] ?? '—' }}
                  </span>
                </td>
                <td style="font-size:13px;color:var(--text-secondary)">{{ $sp['TENNSX'] ?? '—' }}</td>
                <td>
                  <div style="font-weight:700;font-size:13px;color:var(--text-primary)">{{ formatVND($sp['DONGIA_SP']) }}</div>
                </td>
                <td>
                  @php $stock = intval($sp['SOLUONGTON']); $sc = $stock <= 5 ? 'stock-danger' : ($stock <= 20 ? 'stock-warning' : 'stock-ok'); @endphp
                  <span class="stock-badge {{ $sc }}">{{ formatNum($stock) }}</span>
                </td>
                <td>{!! statusBadge($sp['TRANGTHAI'], 'sanpham') !!}</td>
                <td style="font-size:12px;color:var(--text-secondary)">{{ date('d/m/Y', strtotime($sp['NGAYTHEM'])) }}</td>
                <td>
                  <div style="display:flex;gap:6px;align-items:center">
                    <a href="{{ url('admin/sanpham-sua') }}?masp={{ $sp['MASP'] }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                    <x-nut-hanh-dong :action="route('admin.sanpham.trang-thai', $sp['MASP'])" method="PATCH"
                        confirm="Thay đổi trạng thái sản phẩm?" class="btn-icon"
                        :title="$sp['TRANGTHAI'] === 'DangBan' ? 'Ngừng bán' : 'Bật bán'"
                        :aria-label="$sp['TRANGTHAI'] === 'DangBan' ? 'Ngừng bán' : 'Bật bán'">
                        {!! $sp['TRANGTHAI'] === 'DangBan' ? icon('pause', 15) : icon('play', 15) !!}
                    </x-nut-hanh-dong>
                    <x-nut-hanh-dong :action="route('admin.sanpham.destroy', $sp['MASP'])" method="DELETE"
                        :confirm="'Xóa sản phẩm '.$sp['TENSP'].'?'" class="btn-icon" title="Xóa"
                        style="border-color:color-mix(in srgb,var(--red) 30%,transparent)">
                        {!! icon('trash', 15) !!}
                    </x-nut-hanh-dong>
                  </div>
                </td>
              </tr>
              @endforeach
              @if (empty($sanpham))
              <tr><td colspan="9">
                <div class="empty-state">
                  <div class="empty-icon">{!! icon('laptop') !!}</div>
                  <p>Không tìm thấy sản phẩm nào</p>
                </div>
              </td></tr>
              @endif
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        @if ($pages > 1)
        <div class="pagination">
          @if ($page > 1)
            <a href="?{{ http_build_query(array_merge(request()->query(), ['page' => $page-1])) }}" class="page-link">‹</a>
          @endif
          @for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++)
            <a href="?{{ http_build_query(array_merge(request()->query(), ['page' => $p])) }}"
               class="page-link {{ $p === $page ? 'active' : '' }}">{{ $p }}</a>
          @endfor
          @if ($page < $pages)
            <a href="?{{ http_build_query(array_merge(request()->query(), ['page' => $page+1])) }}" class="page-link">›</a>
          @endif
          <span style="font-size:12px;color:var(--text-muted);margin-left:8px">
            Trang {{ $page }}/{{ $pages }} — {{ formatNum($total) }} sản phẩm
          </span>
        </div>
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
