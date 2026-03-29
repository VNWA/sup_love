<?php

namespace App\Models;

use Database\Factories\WheelChoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WheelChoice extends Model
{
    /** @use HasFactory<WheelChoiceFactory> */
    use HasFactory;

    /** Ô “Chúc bạn may mắn lần sau” (seed trong migration). */
    public const CONSOLATION_CHOICE_ID = 8;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sort_order',
        'color',
    ];

    /**
     * Màu cố định theo thứ tự ô trên vòng (không lưu DB).
     *
     * @return list<string>
     */
    public static function segmentColors(): array
    {
        return [
            '#c2185b', '#e91e63', '#ec407a', '#f06292', '#ad1457',
            '#880e4f', '#f48fb1', '#fce4ec', '#9c27b0', '#7b1fa2',
            '#ff9800', '#ffb74d',
        ];
    }

    /**
     * @return list<array{id: int, name: string, color: string}>
     */
    public static function rowsForWheel(): array
    {
        $fallback = self::segmentColors();

        return self::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values()
            ->map(function (WheelChoice $c, int $i) use ($fallback): array {
                $hex = $c->color !== null && $c->color !== ''
                    ? (string) $c->color
                    : ($fallback[$i % count($fallback)] ?? '#e91e63');

                return [
                    'id' => (int) $c->getKey(),
                    'name' => $c->name,
                    'color' => $hex,
                ];
            })
            ->all();
    }

    /**
     * @return HasMany<WheelSpin, $this>
     */
    public function spinsAsBet(): HasMany
    {
        return $this->hasMany(WheelSpin::class, 'bet_choice_id');
    }

    /**
     * @return HasMany<WheelSpin, $this>
     */
    public function spinsAsResult(): HasMany
    {
        return $this->hasMany(WheelSpin::class, 'result_choice_id');
    }
}
