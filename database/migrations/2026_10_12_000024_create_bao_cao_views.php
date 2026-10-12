<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Các view tổng hợp (doanh thu theo tháng, hoá đơn chi tiết, sản phẩm chi tiết, tồn kho thấp).
     * Xoá rồi tạo lại nên chạy lại trên database cũ cũng không sao; SQL viết chung cho MySQL và SQLite.
     */
    public function up(): void
    {
        $sqlite = DB::getDriverName() === 'sqlite';
        $nam = $sqlite ? "CAST(strftime('%Y', NGAYLAP) AS INTEGER)" : 'YEAR(NGAYLAP)';
        $thang = $sqlite ? "CAST(strftime('%m', NGAYLAP) AS INTEGER)" : 'MONTH(NGAYLAP)';

        $this->taoView('V_DOANHTHU_THANG', "
            SELECT {$nam} AS NAM, {$thang} AS THANG, COUNT(MAHD) AS SO_DON, SUM(TONGTIEN_HD) AS TONG_DOANHTHU,
                   SUM(TONGTIEN_GIAM) AS TONG_GIAM_GIA, AVG(TONGTIEN_HD) AS GIA_TRI_TRUNG_BINH
            FROM HOADON
            WHERE TRANGTHAI IN ('DaGiao', 'HoanThanh')
            GROUP BY {$nam}, {$thang}
            ORDER BY NAM DESC, THANG DESC
        ");

        $this->taoView('V_HOADON_CHITIET', '
            SELECT hd.MAHD, hd.NGAYLAP, hd.TRANGTHAI, hd.TONGTIEN_TRUOCGIAM, hd.TONGTIEN_GIAM, hd.TONGTIEN_HD,
                   hd.TEN_NGUOINHAN, hd.SDT_NGUOINHAN, hd.DIACHI_GIAOHANG, hd.PHUONG_THUC_GH,
                   kh.MAKH, kh.TENKH, kh.SDT_KH, kh.EMAIL_KH, nv.MANV, nv.TENNV,
                   tt.PHUONG_THUC AS PT_THANHTOAN, tt.TRANGTHAI AS TT_THANHTOAN, tt.NGAY_THANHTOAN
            FROM HOADON hd
            LEFT JOIN KHACHHANG kh ON hd.MAKH = kh.MAKH
            LEFT JOIN NHANVIEN nv ON hd.MANV = nv.MANV
            LEFT JOIN THANHTOAN tt ON hd.MAHD = tt.MAHD
        ');

        $this->taoView('V_SANPHAM_CHITIET', '
            SELECT sp.MASP, sp.TENSP, sp.DONVT, sp.SOLUONGTON, sp.DONGIA_SP, sp.TRANGTHAI, sp.NGAYTHEM,
                   lsp.MALOAI, lsp.TENLOAI, nsx.MANSX, nsx.TENNSX, ncc.MANCC, ncc.TENNCC,
                   mt.RAM, mt.CPU, mt.ROM, mt.MANHINH, mt.VGA, mt.PIN, mt.KHAC, dsa.URL_ANH AS ANH_CHINH
            FROM SANPHAM sp
            LEFT JOIN LOAISANPHAM lsp ON sp.MALOAI = lsp.MALOAI
            LEFT JOIN NHASANXUA nsx ON sp.MANSX = nsx.MANSX
            LEFT JOIN NHACUNGCAP ncc ON sp.MANCC = ncc.MANCC
            LEFT JOIN MOTA mt ON sp.MASP = mt.MASP
            LEFT JOIN DANHSACHANH dsa ON sp.MASP = dsa.MASP AND dsa.LA_ANH_CHINH = 1
        ');

        $this->taoView('V_TONKHO_CANHBAO', '
            SELECT sp.MASP, sp.TENSP, sp.SOLUONGTON, sp.DONGIA_SP, sp.TRANGTHAI, lsp.TENLOAI, nsx.TENNSX
            FROM SANPHAM sp
            LEFT JOIN LOAISANPHAM lsp ON sp.MALOAI = lsp.MALOAI
            LEFT JOIN NHASANXUA nsx ON sp.MANSX = nsx.MANSX
            WHERE sp.SOLUONGTON <= 10
            ORDER BY sp.SOLUONGTON
        ');
    }

    public function down(): void
    {
        foreach (['V_DOANHTHU_THANG', 'V_HOADON_CHITIET', 'V_SANPHAM_CHITIET', 'V_TONKHO_CANHBAO'] as $ten) {
            DB::statement("DROP VIEW IF EXISTS {$ten}");
        }
    }

    private function taoView(string $ten, string $sql): void
    {
        DB::statement("DROP VIEW IF EXISTS {$ten}");
        DB::statement("CREATE VIEW {$ten} AS {$sql}");
    }
};
