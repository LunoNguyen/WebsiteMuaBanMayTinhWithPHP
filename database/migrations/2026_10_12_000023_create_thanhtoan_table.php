<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng THANHTOAN.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('THANHTOAN')) {
            return;
        }

        Schema::create('THANHTOAN', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->integer('MATHANHTOAN', true);
            $table->string('MAHD', 10);
            $table->enum('PHUONG_THUC', ['QR', 'COD', 'ChuyenKhoan', 'TienMat'])->default('COD');
            $table->enum('TRANGTHAI', ['ChoThanhToan', 'DaThanhToan', 'ThatBai', 'DaHoanTien'])->default('ChoThanhToan');
            $table->decimal('SOTIEN', 18, 2);
            $table->dateTime('NGAY_THANHTOAN')->nullable();
            $table->string('MA_GIAODICH', 100)->nullable()->comment('Transaction ID từ cổng thanh toán');
            $table->string('NGAN_HANG', 100)->nullable()->comment('Ngân hàng (nếu QR / chuyển khoản)');
            $table->string('NOI_DUNG_CK', 200)->nullable()->comment('Nội dung chuyển khoản');
            $table->string('GHI_CHU', 300)->nullable();

            $table->foreign('MAHD', 'fk_tt_hoadon')->references('MAHD')->on('HOADON');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('THANHTOAN');
    }
};
