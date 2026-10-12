<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng NHASANXUA.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('NHASANXUA')) {
            return;
        }

        Schema::create('NHASANXUA', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MANSX', 10);
            $table->string('TENNSX', 100)->nullable();
            $table->string('QUOCGIA', 100)->nullable();

            $table->primary('MANSX');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NHASANXUA');
    }
};
