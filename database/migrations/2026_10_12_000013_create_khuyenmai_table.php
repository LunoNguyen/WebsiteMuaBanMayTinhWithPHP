<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng KHUYENMAI.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('KHUYENMAI')) {
            return;
        }

        Schema::create('KHUYENMAI', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAKM', 10);
            $table->string('TENKM', 200)->nullable();
            $table->string('MA_CODE', 50)->nullable()->comment('Mã voucher khách nhập khi thanh toán');
            $table->enum('LOAI_KM', ['PhanTram', 'SoTienCoDinh'])->default('PhanTram');
            $table->decimal('GIATRI_KM', 10, 2)->default(0)->comment('Số % hoặc số tiền giảm');
            $table->decimal('SOTIENTOIDA_KM', 18, 2)->nullable()->comment('Giảm tối đa (áp dụng với % KM)');
            $table->decimal('SOTIENTOITHIEU_NHANKM', 18, 2)->nullable()->default(0)->comment('Giá trị đơn tối thiểu');
            $table->integer('SOLUONG_MA')->nullable()->comment('Tổng mã phát hành (NULL=không giới hạn)');
            $table->integer('DA_SUDUNG')->default(0);
            $table->dateTime('NGAYBD')->nullable();
            $table->dateTime('NGAYKT')->nullable();
            $table->enum('TRANGTHAI', ['HoatDong', 'TamDung', 'HetHan'])->default('HoatDong');

            $table->primary('MAKM');
            $table->unique('MA_CODE', 'uq_km_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KHUYENMAI');
    }
};
