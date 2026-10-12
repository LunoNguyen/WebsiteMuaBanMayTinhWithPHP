<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng LICHSUGIA.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('LICHSUGIA')) {
            return;
        }

        Schema::create('LICHSUGIA', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MALSG', true);
            $table->string('MASP', 10);
            $table->decimal('DONGIA_MOI', 18, 2);
            $table->decimal('DONGIA_CU', 18, 2)->nullable();
            $table->dateTime('NGAY_CAPNHAT')->default(ThoiDiem::macDinh());
            $table->string('MANV_CAPNHAT', 10)->nullable()->comment('Nhân viên thực hiện cập nhật');
            $table->string('GHI_CHU', 200)->nullable();

            $table->foreign('MANV_CAPNHAT', 'fk_lsg_nhanvien')->references('MANV')->on('NHANVIEN');
            $table->foreign('MASP', 'fk_lsg_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LICHSUGIA');
    }
};
