<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng SANPHAM.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('SANPHAM')) {
            return;
        }

        Schema::create('SANPHAM', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MASP', 10);
            $table->string('MANCC', 10)->nullable()->comment('Nhà cung cấp mặc định');
            $table->string('MALOAI', 10);
            $table->string('MANSX', 10);
            $table->string('TENSP', 200)->nullable();
            $table->string('DONVT', 50)->nullable();
            $table->integer('SOLUONGTON')->default(0);
            $table->decimal('DONGIA_SP', 18, 2)->default(0)->comment('Giá bán hiện tại');
            $table->enum('TRANGTHAI', ['DangBan', 'HetHang', 'NgungBan'])->default('DangBan');
            $table->dateTime('NGAYTHEM')->default(ThoiDiem::macDinh());
            $table->dateTime('NGAY_CAPNHAT')->nullable()->useCurrentOnUpdate();

            $table->primary('MASP');
            $table->foreign('MALOAI', 'fk_sp_loai')->references('MALOAI')->on('LOAISANPHAM');
            $table->foreign('MANCC', 'fk_sp_ncc')->references('MANCC')->on('NHACUNGCAP');
            $table->foreign('MANSX', 'fk_sp_nsx')->references('MANSX')->on('NHASANXUA');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SANPHAM');
    }
};
