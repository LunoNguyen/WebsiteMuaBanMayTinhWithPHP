<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng MOTA.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('MOTA')) {
            return;
        }

        Schema::create('MOTA', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAMT', 10);
            $table->string('MASP', 10);
            $table->string('RAM', 100)->nullable();
            $table->string('CPU', 100)->nullable();
            $table->string('ROM', 100)->nullable();
            $table->string('MANHINH', 100)->nullable();
            $table->string('VGA', 100)->nullable();
            $table->string('PIN', 100)->nullable();
            $table->text('KHAC')->nullable();

            $table->primary('MAMT');
            $table->foreign('MASP', 'fk_mota_sanpham')->references('MASP')->on('SANPHAM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MOTA');
    }
};
