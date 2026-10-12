<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng PHIEN_CHATBOT.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('PHIEN_CHATBOT')) {
            return;
        }

        Schema::create('PHIEN_CHATBOT', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MAPHIEN', true);
            $table->string('MAKH', 10)->nullable()->comment('NULL nếu khách chưa đăng nhập');
            $table->string('MATK', 20)->nullable()->comment('Tài khoản đăng nhập (nếu có)');
            $table->dateTime('THOIGIAN_BD')->default(ThoiDiem::macDinh());
            $table->dateTime('THOIGIAN_KT')->nullable();
            $table->enum('TRANGTHAI', ['DangChat', 'DaKetThuc'])->default('DangChat');

            $table->foreign('MAKH', 'fk_pcb_khachhang')->references('MAKH')->on('KHACHHANG');
            $table->foreign('MATK', 'fk_pcb_taikhoan')->references('MATK')->on('TAIKHOAN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEN_CHATBOT');
    }
};
