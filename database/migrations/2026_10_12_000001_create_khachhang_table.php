<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng KHACHHANG.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('KHACHHANG')) {
            return;
        }

        Schema::create('KHACHHANG', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAKH', 10);
            $table->string('TENKH', 100)->nullable();
            $table->string('SDT_KH', 20)->nullable();
            $table->string('DIACHI_KH', 200)->nullable();
            $table->string('EMAIL_KH', 100)->nullable();
            $table->date('NGAYSINH')->nullable();
            $table->boolean('GIOITINH')->nullable()->comment('1=Nam, 0=Nu');

            $table->primary('MAKH');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KHACHHANG');
    }
};
