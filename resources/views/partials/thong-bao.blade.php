{{-- Thông báo kết quả thao tác (flash từ controller: ->with('thong_bao', ...)->with('loai', 'success|info|danger')) --}}
@if (session('thong_bao'))
    @php($loai = session('loai', 'success'))
    <div class="alert alert-{{ $loai }}" data-dismiss>
        {!! icon($loai === 'success' ? 'check' : ($loai === 'info' ? 'bulb' : 'x')) !!} {{ session('thong_bao') }}
    </div>
@endif
