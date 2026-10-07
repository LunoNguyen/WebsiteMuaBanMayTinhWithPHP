<?php

namespace App\Providers;

use App\Models\LoaiSanPham;
use App\Services\GioHangService;
use App\Services\MinioStorage;
use App\Services\YeuCauDatHang;
use App\Support\MenuQuanTri;
use Illuminate\Foundation\DevCommands;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MinioStorage::class, fn (): MinioStorage => MinioStorage::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // "php artisan dev" (hoặc "composer dev"): một lệnh chạy đủ web, Reverb (realtime) và worker đặt hàng.
        // Bỏ các lệnh mặc định: queue (chỉ nghe hàng "default"), vite (giao diện không build bằng Vite),
        // server (gọi "php" trần, Windows không có trong PATH khi PHP của Laragon chưa được thêm vào).
        if ($this->app->runningInConsole()) {
            $php = PHP_BINARY;
            DevCommands::except('server', 'queue', 'vite');
            DevCommands::register("{$php} artisan serve", 'web');
            DevCommands::register("{$php} artisan reverb:start", 'reverb');
            DevCommands::register("{$php} artisan queue:work --queue=dat-hang,default --tries=1", 'dat-hang');
        }

        // Worker đang nghe hàng đợi "dat-hang" ghi nhịp mỗi vòng lặp; web dựa vào nhịp này để biết
        // nên xếp đơn vào hàng đợi hay xử lý ngay (khi không có worker)
        Queue::looping(function (Looping $suKien): void {
            if (in_array('dat-hang', explode(',', $suKien->queue), true)) {
                app(YeuCauDatHang::class)->ghiNhipWorker();
            }
        });

        // Sidebar chung của khu quản trị: menu theo vai trò kèm số việc đang chờ
        View::composer('partials.quan-tri.sidebar', function (BladeView $view): void {
            $vaiTro = request()->user()->vaiTro();
            $view->with(['vaiTro' => $vaiTro, 'menu' => MenuQuanTri::cho($vaiTro)]);
        });
        View::composer(['partials.shop.header', 'partials.shop.footer'], function (BladeView $view): void {
            $view->with('danhMucNav', once(fn () => LoaiSanPham::query()
                ->whereHas('sanPhams', fn ($q) => $q->where('TRANGTHAI', '!=', 'NgungBan'))
                ->orderBy('MALOAI')
                ->get(['MALOAI', 'TENLOAI'])));

            $taiKhoan = request()->user();
            $view->with('soLuongGio', $taiKhoan?->LOAI_TAIKHOAN === 'KhachHang'
                ? once(fn () => app(GioHangService::class)->soLuong($taiKhoan))
                : 0);
        });
    }
}
