<?php

namespace Database\Seeders;

use App\Models\KhachHang;
use Illuminate\Database\Seeder;

class KhachHangSeeder extends Seeder
{
    /**
     * Dữ liệu bảng KHACHHANG (lấy từ database hiện có). insertOrIgnore: chạy lại không bị trùng.
     */
    public function run(): void
    {
        KhachHang::query()->insertOrIgnore([
            ['MAKH' => 'KH001', 'TENKH' => 'Nguyễn Văn A', 'SDT_KH' => '0909123456', 'DIACHI_KH' => '123 Lê Lợi, Q.1, TP.HCM', 'EMAIL_KH' => 'nguyenvana@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH0011', 'TENKH' => 'Lê', 'SDT_KH' => '12345678', 'DIACHI_KH' => null, 'EMAIL_KH' => 'nvl@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH0012', 'TENKH' => 'Nguyễn Ngân Lượng', 'SDT_KH' => '0398745621', 'DIACHI_KH' => null, 'EMAIL_KH' => 'nnl@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH002', 'TENKH' => 'Trần Thị B', 'SDT_KH' => '0918765432', 'DIACHI_KH' => '456 Nguyễn Trãi, Q.5, TP.HCM', 'EMAIL_KH' => 'tranthib@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH003', 'TENKH' => 'Lê Minh C', 'SDT_KH' => '0988111222', 'DIACHI_KH' => '789 Cách Mạng Tháng 8, Q.3, TP.HCM', 'EMAIL_KH' => 'leminhc@yahoo.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH004', 'TENKH' => 'Phạm Văn D', 'SDT_KH' => '0977333444', 'DIACHI_KH' => '234 Cộng Hòa, Q.Tân Bình, TP.HCM', 'EMAIL_KH' => 'phamvand@outlook.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH005', 'TENKH' => 'Huỳnh Thị E', 'SDT_KH' => '0966555666', 'DIACHI_KH' => '567 An Dương Vương, Q.6, TP.HCM', 'EMAIL_KH' => 'huynhthie@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH006', 'TENKH' => 'Võ Văn T', 'SDT_KH' => '0955777888', 'DIACHI_KH' => '890 Võ Văn Kiệt, Q.8, TP.HCM', 'EMAIL_KH' => 'vovant@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH007', 'TENKH' => 'Đỗ Thị G', 'SDT_KH' => '0944999000', 'DIACHI_KH' => '111 Điện Biên Phủ, Q.Bình Thạnh, TP.HCM', 'EMAIL_KH' => 'dothig@yahoo.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH008', 'TENKH' => 'Phan Minh H', 'SDT_KH' => '0933121212', 'DIACHI_KH' => '222 Hoàng Diệu, Q.4, TP.HCM', 'EMAIL_KH' => 'phanminhh@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH009', 'TENKH' => 'Trương Văn K', 'SDT_KH' => '0922343434', 'DIACHI_KH' => '333 Lê Văn Sỹ, Q.Phú Nhuận, TP.HCM', 'EMAIL_KH' => 'truongvank@gmail.com', 'NGAYSINH' => null, 'GIOITINH' => null],
            ['MAKH' => 'KH010', 'TENKH' => 'Bùi Hoàng L', 'SDT_KH' => '0911565656', 'DIACHI_KH' => '444 Lũy Bán Bích, Q.Tân Phú, TP.HCM', 'EMAIL_KH' => 'buihoangl@outlook.com', 'NGAYSINH' => null, 'GIOITINH' => null],
        ]);
    }
}
