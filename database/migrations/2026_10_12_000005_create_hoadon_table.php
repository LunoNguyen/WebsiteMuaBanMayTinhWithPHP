<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng HOADON.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('HOADON')) {
            return;
        }

        Schema::create('HOADON', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAHD', 10);
            $table->string('MATK', 20)->nullable()->comment('Tài khoản đặt đơn (online)');
            $table->string('MANV', 10)->nullable()->comment('Nhân viên xử lý (tại quầy)');
            $table->string('MAKH', 10)->nullable();
            $table->dateTime('NGAYLAP')->default(ThoiDiem::macDinh());
            $table->enum('TRANGTHAI', ['ChoXacNhan', 'DaXacNhan', 'DangGiao', 'DaGiao', 'HoanThanh', 'DaHuy'])->default('ChoXacNhan');
            $table->decimal('TONGTIEN_TRUOCGIAM', 18, 2)->default(0);
            $table->decimal('TONGTIEN_GIAM', 18, 2)->default(0);
            $table->decimal('TONGTIEN_HD', 18, 2)->default(0)->comment('Thực tế phải trả');
            $table->string('TEN_NGUOINHAN', 100)->nullable();
            $table->string('SDT_NGUOINHAN', 20)->nullable();
            $table->string('DIACHI_GIAOHANG', 300)->nullable();
            $table->enum('PHUONG_THUC_GH', ['TaiQuay', 'GiaoHang'])->default('GiaoHang');
            $table->text('GHI_CHU')->nullable();
            $table->dateTime('NGAY_CAPNHAT')->nullable()->useCurrentOnUpdate();

            $table->primary('MAHD');
            $table->foreign('MAKH', 'fk_hd_khachhang')->references('MAKH')->on('KHACHHANG');
            $table->foreign('MANV', 'fk_hd_nhanvien')->references('MANV')->on('NHANVIEN');
            $table->foreign('MATK', 'fk_hd_taikhoan')->references('MATK')->on('TAIKHOAN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('HOADON');
    }
};
