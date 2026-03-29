<?php

namespace App\Models;

use App\Enums\WheelRoundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WheelRoom extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<WheelSpin, $this>
     */
    public function wheelSpins(): HasMany
    {
        return $this->hasMany(WheelSpin::class);
    }

    /**
     * @return HasMany<WheelRound, $this>
     */
    public function wheelRounds(): HasMany
    {
        return $this->hasMany(WheelRound::class);
    }

    public function openRound(): ?WheelRound
    {
        return $this->wheelRounds()
            ->where('status', WheelRoundStatus::Open)
            ->orderByDesc('id')
            ->first();
    }
}
