<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng DANHSACHANH.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('DANHSACHANH')) {
            return;
        }

        Schema::create('DANHSACHANH', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MAANH', true);
            $table->string('MASP', 10);
            $table->string('URL_ANH', 500)->nullable();
            $table->boolean('LA_ANH_CHINH')->default(0)->comment('1=Ảnh đại diện');
            $table->integer('THU_TU')->default(0);

            $table->foreign('MASP', 'fk_anh_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DANHSACHANH');
    }
};
