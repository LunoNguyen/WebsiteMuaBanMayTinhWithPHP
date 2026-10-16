<?php

use App\Models\SanPham;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi cách tính tồn kho: trước đây trừ tồn ngay khi đặt hàng, nay chỉ trừ khi kho xuất đơn.
     * Các đơn chưa xuất kho (chờ xác nhận, đã xác nhận) đã bị trừ theo cách cũ, nên cộng trả lại cho đúng tồn thực tế.
     * Database mới (chưa có đơn) thì không làm gì.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $dangGiu = DB::table('CHITIETHOADON as ct')
                ->join('HOADON as hd', 'hd.MAHD', '=', 'ct.MAHD')
                ->whereIn('hd.TRANGTHAI', SanPham::GIU_HANG)
                ->groupBy('ct.MASP')
                ->selectRaw('ct.MASP as ma, SUM(ct.SOLUONG) as so')
                ->pluck('so', 'ma');

            foreach ($dangGiu as $maSanPham => $soLuong) {
                DB::table('SANPHAM')->where('MASP', $maSanPham)->increment('SOLUONGTON', (int) $soLuong);
                DB::table('SANPHAM')->where('MASP', $maSanPham)->where('TRANGTHAI', 'HetHang')->where('SOLUONGTON', '>', 0)
                    ->update(['TRANGTHAI' => 'DangBan']);
            }
        });
    }

    /**
     * Quay lại cách cũ: trừ lại phần đang giữ khỏi tồn kho.
     */
    public function down(): void
    {
        $dangGiu = DB::table('CHITIETHOADON as ct')
            ->join('HOADON as hd', 'hd.MAHD', '=', 'ct.MAHD')
            ->whereIn('hd.TRANGTHAI', SanPham::GIU_HANG)
            ->groupBy('ct.MASP')
            ->selectRaw('ct.MASP as ma, SUM(ct.SOLUONG) as so')
            ->pluck('so', 'ma');

        foreach ($dangGiu as $maSanPham => $soLuong) {
            DB::table('SANPHAM')->where('MASP', $maSanPham)->decrement('SOLUONGTON', (int) $soLuong);
        }
    }
};
