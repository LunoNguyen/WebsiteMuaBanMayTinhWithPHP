{{-- Thông báo kết quả thao tác (flash từ controller: ->with('thong_bao', ...)->with('loai', 'success|info|danger')) --}}
@if (session('thong_bao'))
    @php($loai = session('loai', 'success'))
    <div class="alert alert-{{ $loai }}" data-dismiss>
        {!! icon($loai === 'success' ? 'check' : ($loai === 'info' ? 'bulb' : 'x')) !!} {{ session('thong_bao') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" data-dismiss>
        {!! icon('x') !!} {{ $errors->count() > 1 ? 'Có '.$errors->count().' chỗ chưa hợp lệ, xem các ô được đánh dấu bên dưới.' : $errors->first() }}
    </div>
@endif
