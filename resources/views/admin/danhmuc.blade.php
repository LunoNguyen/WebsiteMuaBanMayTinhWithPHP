@extends('layouts.admin', ['title' => 'Danh mục', 'breadcrumb' => ['Cài đặt', 'Danh mục', $cauHinh['ten']]])

@section('content')
    <div class="page-header">
        <div class="page-header-left">
            <h1>Danh mục</h1>
            <p>Loại sản phẩm, hãng, nhà cung cấp và chức vụ dùng trong các form quản lý.</p>
        </div>
    </div>

    <nav class="seg-tabs" aria-label="Chọn danh mục">
        @foreach ($tatCa as $ma => $cfg)
            <a href="{{ route('admin.danhmuc', ['bang' => $ma]) }}" @class(['active' => $ma === $bang])>{{ $cfg['ten'] }}</a>
        @endforeach
    </nav>

    @include('partials.thong-bao')

    <div class="grid-5-7">
        <form method="POST" class="card"
              action="{{ $dangSua ? route('admin.danhmuc.update', [$bang, $dangSua->{$cauHinh['khoa']}]) : route('admin.danhmuc.store', $bang) }}">
            @csrf
            @if ($dangSua) @method('PUT') @endif
            <div class="card-header">
                <h3>{{ $dangSua ? 'Sửa '.$dangSua->{$cauHinh['khoa']} : 'Thêm '.mb_strtolower($cauHinh['ten']) }}</h3>
            </div>
            <div class="card-body">
                @foreach ($cauHinh['truong'] as $cot => $truong)
                    <x-truong :name="$cot" :label="$truong['nhan']" :value="$dangSua?->{$cot}"
                              :required="in_array('required', $truong['luat'], true)"
                              :type="$cot === 'MOTA_CV' ? 'textarea' : (str_starts_with($cot, 'EMAIL') ? 'email' : 'text')" />
                @endforeach
            </div>
            <div class="form-actions">
                @if ($dangSua)
                    <a href="{{ route('admin.danhmuc', ['bang' => $bang]) }}" class="btn btn-outline">Huỷ sửa</a>
                @endif
                <button type="submit" class="btn btn-primary">{{ $dangSua ? 'Lưu' : 'Thêm' }}</button>
            </div>
        </form>

        <div class="card">
            <div class="card-header"><h3>{{ $cauHinh['ten'] }} ({{ $dong->count() }})</h3></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            @foreach ($cauHinh['truong'] as $truong)
                                <th>{{ $truong['nhan'] }}</th>
                            @endforeach
                            <th>Đang dùng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dong as $d)
                            @php($ma = $d->{$cauHinh['khoa']})
                            <tr @if ($dangSua && $dangSua->{$cauHinh['khoa']} === $ma) style="background:var(--blue-glow)" @endif>
                                <td><code>{{ $ma }}</code></td>
                                @foreach ($cauHinh['truong'] as $cot => $truong)
                                    <td>{{ $d->{$cot} ?? '—' }}</td>
                                @endforeach
                                <td>{{ formatNum($d->so_dung) }}</td>
                                <td>
                                    <div style="display:flex;gap:6px">
                                        <a href="{{ route('admin.danhmuc', ['bang' => $bang, 'sua' => $ma]) }}" class="btn-icon" title="Sửa">{!! icon('pencil', 15) !!}</a>
                                        @if ((int) $d->so_dung === 0)
                                            <x-nut-hanh-dong :action="route('admin.danhmuc.destroy', [$bang, $ma])" method="DELETE" class="btn-icon" title="Xoá"
                                                             :confirm="'Xoá '.$ma.'?'">{!! icon('trash', 15) !!}</x-nut-hanh-dong>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($cauHinh['truong']) + 3 }}"><div class="empty-state"><p>Chưa có dữ liệu</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
