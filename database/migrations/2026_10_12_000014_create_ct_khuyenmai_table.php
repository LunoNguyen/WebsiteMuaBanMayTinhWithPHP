<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CT_KHUYENMAI.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('CT_KHUYENMAI')) {
            return;
        }

        Schema::create('CT_KHUYENMAI', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MASP', 10);
            $table->string('MAKM', 10);

            $table->primary(['MASP', 'MAKM']);
            $table->foreign('MAKM', 'fk_ctkm_khuyenmai')->references('MAKM')->on('KHUYENMAI');
            $table->foreign('MASP', 'fk_ctkm_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_KHUYENMAI');
    }
};
