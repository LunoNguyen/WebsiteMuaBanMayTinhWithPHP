<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng NHACUNGCAP.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('NHACUNGCAP')) {
            return;
        }

        Schema::create('NHACUNGCAP', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MANCC', 10);
            $table->string('TENNCC', 100)->nullable();
            $table->string('DIACHI_NCC', 200)->nullable();
            $table->string('SDT_NCC', 20)->nullable();
            $table->string('EMAIL_NCC', 100)->nullable();

            $table->primary('MANCC');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NHACUNGCAP');
    }
};
