<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CT_PHIEUNHAPHANG.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('CT_PHIEUNHAPHANG')) {
            return;
        }

        Schema::create('CT_PHIEUNHAPHANG', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAPNH', 10);
            $table->string('MASP', 10);
            $table->integer('SOLUONG')->default(1);
            $table->decimal('DONGIA_NHAP', 18, 2)->comment('Giá nhập tại thời điểm nhập');
            $table->decimal('THANHTIEN', 18, 2)->comment('SOLUONG * DONGIA_NHAP');
            $table->string('GHI_CHU', 200)->nullable();

            $table->primary(['MAPNH', 'MASP']);
            $table->foreign('MAPNH', 'fk_ctpnh_phieu')->references('MAPNH')->on('PHIEUNHAPHANG');
            $table->foreign('MASP', 'fk_ctpnh_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_PHIEUNHAPHANG');
    }
};
