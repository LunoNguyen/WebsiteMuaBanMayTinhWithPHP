<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng TAIKHOAN.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('TAIKHOAN')) {
            return;
        }

        Schema::create('TAIKHOAN', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MATK', 20);
            $table->string('MANV', 10)->nullable();
            $table->string('MAKH', 10)->nullable();
            $table->string('EMAIL_TK', 100);
            $table->string('MATKHAU', 255)->comment('Lưu dạng hash bcrypt');
            $table->enum('LOAI_TAIKHOAN', ['Admin', 'NhanVien', 'KhachHang'])->default('KhachHang');
            $table->enum('TRANGTHAI', ['HoatDong', 'KhoaTamThoi', 'KhoaVinhVien'])->default('HoatDong');
            $table->dateTime('NGAYTAO')->default(ThoiDiem::macDinh());
            $table->dateTime('NGAY_CAPNHAT')->nullable()->useCurrentOnUpdate();

            $table->primary('MATK');
            $table->unique('EMAIL_TK', 'uq_email_tk');
            $table->foreign('MAKH', 'fk_tk_khachhang')->references('MAKH')->on('KHACHHANG');
            $table->foreign('MANV', 'fk_tk_nhanvien')->references('MANV')->on('NHANVIEN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TAIKHOAN');
    }
};
