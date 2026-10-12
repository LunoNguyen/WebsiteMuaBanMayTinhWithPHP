<?php

namespace App\Support;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * Giá trị mặc định "thời điểm hiện tại" cho cột ngày giờ trong migration.
 * MySQL: CURRENT_TIMESTAMP (theo múi giờ server). SQLite: CURRENT_TIMESTAMP là giờ UTC, nên dùng giờ địa phương của máy.
 */
class ThoiDiem
{
    public static function macDinh(): Expression
    {
        return new Expression(DB::getDriverName() === 'sqlite' ? "(datetime('now', 'localtime'))" : 'CURRENT_TIMESTAMP');
    }
}
