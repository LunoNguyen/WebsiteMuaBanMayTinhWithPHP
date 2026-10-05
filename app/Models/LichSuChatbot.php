<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một tin nhắn trong phiên chatbot.
 */
#[Table(name: 'LICHSU_CHATBOT', key: 'MATIN', keyType: 'int', incrementing: true, timestamps: false)]
#[Fillable(['MAPHIEN', 'NGUOI_GUI', 'NOI_DUNG', 'THOIGIAN'])]
class LichSuChatbot extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'THOIGIAN' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PhienChatbot, $this>
     */
    public function phien(): BelongsTo
    {
        return $this->belongsTo(PhienChatbot::class, 'MAPHIEN', 'MAPHIEN');
    }
}
