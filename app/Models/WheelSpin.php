<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WheelSpin extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'wheel_room_id',
        'wheel_round_id',
        'bet_choice_id',
        'bet_amount',
        'wish_category',
        'result_choice_id',
        'payout',
        'was_rigged',
        'point_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bet_amount' => 'integer',
            'payout' => 'integer',
            'was_rigged' => 'boolean',
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
     * @return BelongsTo<WheelRoom, $this>
     */
    public function wheelRoom(): BelongsTo
    {
        return $this->belongsTo(WheelRoom::class);
    }

    /**
     * @return BelongsTo<WheelRound, $this>
     */
    public function wheelRound(): BelongsTo
    {
        return $this->belongsTo(WheelRound::class);
    }

    /**
     * @return BelongsTo<WheelChoice, $this>
     */
    public function betChoice(): BelongsTo
    {
        return $this->belongsTo(WheelChoice::class, 'bet_choice_id');
    }

    /**
     * @return BelongsTo<WheelChoice, $this>
     */
    public function resultChoice(): BelongsTo
    {
        return $this->belongsTo(WheelChoice::class, 'result_choice_id');
    }

    /**
     * @return BelongsTo<PointTransaction, $this>
     */
    public function pointTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class);
    }
}
