<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng LOAISANPHAM.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('LOAISANPHAM')) {
            return;
        }

        Schema::create('LOAISANPHAM', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MALOAI', 10);
            $table->string('TENLOAI', 100)->nullable();

            $table->primary('MALOAI');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LOAISANPHAM');
    }
};
