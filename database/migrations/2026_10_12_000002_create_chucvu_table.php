<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CHUCVU.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('CHUCVU')) {
            return;
        }

        Schema::create('CHUCVU', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MACV', 10);
            $table->string('TENCV', 100)->nullable();
            $table->text('MOTA_CV')->nullable();

            $table->primary('MACV');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CHUCVU');
    }
};
