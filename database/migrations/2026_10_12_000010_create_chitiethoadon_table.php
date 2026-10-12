<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CHITIETHOADON.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('CHITIETHOADON')) {
            return;
        }

        Schema::create('CHITIETHOADON', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAHD', 10);
            $table->string('MASP', 10);
            $table->integer('SOLUONG')->default(1);
            $table->decimal('DONGIA_LUCAT', 18, 2)->comment('Giá tại thời điểm mua');
            $table->decimal('THANHTIEN', 18, 2)->comment('SOLUONG * DONGIA_LUCAT');

            $table->primary(['MAHD', 'MASP']);
            $table->foreign('MAHD', 'fk_cthd_hoadon')->references('MAHD')->on('HOADON');
            $table->foreign('MASP', 'fk_cthd_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CHITIETHOADON');
    }
};
