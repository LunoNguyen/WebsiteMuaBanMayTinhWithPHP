<?php

use App\Support\ThoiDiem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng PHIEUNHAPHANG.
     * Database cũ (dựng từ qlbanmt.sql) đã có bảng này thì bỏ qua, chỉ ghi nhận migration đã chạy.
     */
    public function up(): void
    {
        if (Schema::hasTable('PHIEUNHAPHANG')) {
            return;
        }

        Schema::create('PHIEUNHAPHANG', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('MAPNH', 10);
            $table->string('MANCC', 10);
            $table->string('MANV', 10)->nullable()->comment('Nhân viên kho tạo / phụ trách phiếu');
            $table->dateTime('NGAY_DATMUA')->nullable()->comment('Ngày tạo phiếu đặt mua');
            $table->dateTime('NGAYGIAO')->nullable()->comment('Ngày NCC giao hàng');
            $table->dateTime('NGAYNHAN')->nullable()->comment('Ngày kho nhận hàng');
            $table->enum('TRANGTHAI', ['ChoDuyet', 'DaDuyet', 'DaNhan', 'HoanThanh', 'DaHuy'])->default('ChoDuyet');
            $table->enum('TRANGTHAI_THANHTOAN', ['ChuaThanhToan', 'DaThanhToan', 'HoanTien'])->default('ChuaThanhToan');
            $table->decimal('THUE_VAT', 5, 2)->nullable()->default(10)->comment('Phần trăm VAT');
            $table->decimal('CHIETKHAU', 5, 2)->nullable()->default(0)->comment('Phần trăm chiết khấu');
            $table->decimal('TONGCONG_PNH', 18, 2)->nullable()->comment('Tổng tiền sau VAT và chiết khấu');
            $table->text('GHI_CHU')->nullable();
            $table->dateTime('NGAYTAO')->default(ThoiDiem::macDinh());

            $table->primary('MAPNH');
            $table->foreign('MANCC', 'fk_pnh_nhacungcap')->references('MANCC')->on('NHACUNGCAP');
            $table->foreign('MANV', 'fk_pnh_nhanvien')->references('MANV')->on('NHANVIEN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUNHAPHANG');
    }
};
