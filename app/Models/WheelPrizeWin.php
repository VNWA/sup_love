<?php

namespace App\Models;

use App\Enums\WheelPrizeWinStatus;
use Database\Factories\WheelPrizeWinFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'point_transaction_id',
    'wheel_prize_id',
    'prize_label',
    'prize_label_ngan',
    'color',
    'status',
    'received_at',
    'handled_by',
    'admin_note',
])]
class WheelPrizeWin extends Model
{
    /** @use HasFactory<WheelPrizeWinFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WheelPrizeWinStatus::class,
            'received_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<PointTransaction, $this>
     */
    public function pointTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class);
    }

    /**
     * @return BelongsTo<WheelPrize, $this>
     */
    public function wheelPrize(): BelongsTo
    {
        return $this->belongsTo(WheelPrize::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
