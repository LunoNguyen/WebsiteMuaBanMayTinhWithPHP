<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng GIOHANG.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('GIOHANG')) {
            return;
        }

        Schema::create('GIOHANG', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MAGIOHANG', true);
            $table->string('MATK', 20);
            $table->dateTime('NGAYTAO')->default(ThoiDiem::macDinh());
            $table->dateTime('NGAY_CAPNHAT')->nullable()->useCurrentOnUpdate();
            $table->enum('TRANGTHAI', ['DangMua', 'DaThanhToan', 'DaHuy'])->default('DangMua');

            $table->foreign('MATK', 'fk_gh_taikhoan')->references('MATK')->on('TAIKHOAN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GIOHANG');
    }
};
