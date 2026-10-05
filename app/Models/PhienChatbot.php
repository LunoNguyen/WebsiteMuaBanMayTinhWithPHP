<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Một phiên hội thoại với chatbot.
 */
#[Table(name: 'PHIEN_CHATBOT', key: 'MAPHIEN', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MAKH', 'MATK', 'THOIGIAN_BD', 'THOIGIAN_KT', 'TRANGTHAI'])]
class PhienChatbot extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'THOIGIAN_BD' => 'datetime',
            'THOIGIAN_KT' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<KhachHang, $this>
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    /**
     * @return BelongsTo<TaiKhoan, $this>
     */
    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'MATK', 'MATK');
    }

    /**
     * @return HasMany<LichSuChatbot, $this>
     */
    public function tinNhans(): HasMany
    {
        return $this->hasMany(LichSuChatbot::class, 'MAPHIEN', 'MAPHIEN');
    }
}
