<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CT_GIOHANG.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('CT_GIOHANG')) {
            return;
        }

        Schema::create('CT_GIOHANG', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MACTGH', true);
            $table->integer('MAGIOHANG');
            $table->string('MASP', 10);
            $table->integer('SOLUONG')->default(1);
            $table->decimal('DONGIA_LUCAT', 18, 2)->nullable()->comment('Giá tại thời điểm thêm vào giỏ');
            $table->dateTime('NGAYTHEM')->default(ThoiDiem::macDinh());

            $table->unique(['MAGIOHANG', 'MASP'], 'uq_ctgh');
            $table->foreign('MAGIOHANG', 'fk_ctgh_giohang')->references('MAGIOHANG')->on('GIOHANG');
            $table->foreign('MASP', 'fk_ctgh_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_GIOHANG');
    }
};
