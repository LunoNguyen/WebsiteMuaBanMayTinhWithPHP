<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bảng có cột NGAY_CAPNHAT tự đổi khi sửa dòng (MySQL: ON UPDATE CURRENT_TIMESTAMP).
     *
     * @var list<string>
     */
    private const BANG = ['TAIKHOAN', 'HOADON', 'SANPHAM', 'GIOHANG'];

    /**
     * SQLite không có ON UPDATE CURRENT_TIMESTAMP: dùng trigger cập nhật NGAY_CAPNHAT theo giờ máy,
     * trừ khi câu UPDATE đã tự đặt giá trị cho cột này. MySQL thì không cần làm gì.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        foreach (self::BANG as $bang) {
            DB::statement("DROP TRIGGER IF EXISTS TRG_{$bang}_NGAY_CAPNHAT");
            DB::statement("
                CREATE TRIGGER TRG_{$bang}_NGAY_CAPNHAT AFTER UPDATE ON {$bang}
                FOR EACH ROW WHEN NEW.NGAY_CAPNHAT IS OLD.NGAY_CAPNHAT
                BEGIN
                    UPDATE {$bang} SET NGAY_CAPNHAT = datetime('now', 'localtime') WHERE rowid = NEW.rowid;
                END
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        foreach (self::BANG as $bang) {
            DB::statement("DROP TRIGGER IF EXISTS TRG_{$bang}_NGAY_CAPNHAT");
        }
    }
};
