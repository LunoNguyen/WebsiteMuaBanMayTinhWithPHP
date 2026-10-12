<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng NHANVIEN.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('NHANVIEN')) {
            return;
        }

        Schema::create('NHANVIEN', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MANV', 10);
            $table->string('MACV', 10);
            $table->string('TENNV', 100)->nullable();
            $table->string('SDT_NV', 20)->nullable();
            $table->string('DIACHI_NV', 200)->nullable();
            $table->string('EMAIL_NV', 100)->nullable();
            $table->date('NGAYVAOLAM')->nullable();
            $table->boolean('TRANGTHAI')->default(1)->comment('1=DangLamViec, 0=NghiViec');

            $table->primary('MANV');
            $table->foreign('MACV', 'fk_nv_chucvu')->references('MACV')->on('CHUCVU');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NHANVIEN');
    }
};
