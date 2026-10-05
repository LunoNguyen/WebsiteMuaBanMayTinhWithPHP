<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Sinh mã tiếp theo cho các bảng dùng mã chữ + số (SP011, NV011, PNH011...).
 */
class MaTuDong
{
    /**
     * Mã kế tiếp: lấy phần số lớn nhất trong các mã cùng tiền tố (so theo số, không theo chuỗi).
     * Gọi trong transaction để khoá các dòng đang đọc, tránh hai người cùng lấy một mã.
     */
    public static function tiepTheo(string $bang, string $cot, string $tienTo, int $doDai = 3): string
    {
        $lonNhat = (int) DB::table($bang)
            ->where($cot, 'regexp', '^'.preg_quote($tienTo).'[0-9]+$')
            ->lockForUpdate()
            ->selectRaw("MAX(CAST(SUBSTRING({$cot}, ?) AS UNSIGNED)) AS so", [strlen($tienTo) + 1])
            ->value('so');

        return $tienTo.str_pad((string) ($lonNhat + 1), $doDai, '0', STR_PAD_LEFT);
    }
}
