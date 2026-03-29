<?php

namespace App\Models;

use App\Enums\WheelRoundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WheelRound extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'wheel_room_id',
        'round_number',
        'name',
        'status',
        'result_choice_id',
        'started_at',
        'ended_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WheelRoundStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WheelRoom, $this>
     */
    public function wheelRoom(): BelongsTo
    {
        return $this->belongsTo(WheelRoom::class);
    }

    /**
     * @return BelongsTo<WheelChoice, $this>
     */
    public function resultChoice(): BelongsTo
    {
        return $this->belongsTo(WheelChoice::class, 'result_choice_id');
    }

    /**
     * @return HasMany<WheelSpin, $this>
     */
    public function wheelSpins(): HasMany
    {
        return $this->hasMany(WheelSpin::class);
    }

    public function isOpen(): bool
    {
        return $this->status === WheelRoundStatus::Open;
    }
}
