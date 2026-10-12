<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng LICHSU_CHATBOT.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('LICHSU_CHATBOT')) {
            return;
        }

        Schema::create('LICHSU_CHATBOT', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MATIN', true);
            $table->integer('MAPHIEN');
            $table->enum('NGUOI_GUI', ['KhachHang', 'Bot']);
            $table->text('NOI_DUNG');
            $table->dateTime('THOIGIAN')->default(ThoiDiem::macDinh());

            $table->foreign('MAPHIEN', 'fk_lscb_phien')->references('MAPHIEN')->on('PHIEN_CHATBOT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LICHSU_CHATBOT');
    }
};
