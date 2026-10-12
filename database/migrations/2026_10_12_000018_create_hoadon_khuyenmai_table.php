<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng HOADON_KHUYENMAI.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('HOADON_KHUYENMAI')) {
            return;
        }

        Schema::create('HOADON_KHUYENMAI', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAHD', 10);
            $table->string('MAKM', 10);
            $table->decimal('SOTIEN_GIAM', 18, 2)->default(0)->comment('Số tiền giảm thực tế');

            $table->primary(['MAHD', 'MAKM']);
            $table->foreign('MAHD', 'fk_hdkm_hoadon')->references('MAHD')->on('HOADON');
            $table->foreign('MAKM', 'fk_hdkm_khuyenmai')->references('MAKM')->on('KHUYENMAI');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('HOADON_KHUYENMAI');
    }
};
