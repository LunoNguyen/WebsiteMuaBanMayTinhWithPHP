<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Chức vụ nhân viên.
 */
#[Table(name: 'CHUCVU', key: 'MACV', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['MACV', 'TENCV', 'MOTA_CV'])]
class ChucVu extends Model
{
    /**
     * @return HasMany<NhanVien, $this>
     */
    public function nhanViens(): HasMany
    {
        return $this->hasMany(NhanVien::class, 'MACV', 'MACV');
    }
}
