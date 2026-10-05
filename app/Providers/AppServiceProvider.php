<?php

namespace App\Providers;

use App\Models\HoaDon;
use App\Models\PhieuNhapHang;
use App\Services\MinioStorage;
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
        // Số đếm trên sidebar của từng khu vực
        View::composer('partials.admin.sidebar', function (BladeView $view): void {
            $view->with('soDonChoXacNhan', HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->count());
        });

        View::composer('partials.kho.sidebar', function (BladeView $view): void {
            $view->with([
                'soPhieuChoKiemDem' => PhieuNhapHang::query()->whereIn('TRANGTHAI', ['ChoDuyet', 'DaDuyet'])->count(),
                'soDonChoXuatKho' => HoaDon::query()->where('TRANGTHAI', 'DaXacNhan')->count(),
            ]);
        });

        View::composer('partials.banhang.sidebar', function (BladeView $view): void {
            $view->with('soDonChoXacNhan', HoaDon::query()->where('TRANGTHAI', 'ChoXacNhan')->count());
        });
    }
}
