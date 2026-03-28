<?php

namespace App\Models;

use Database\Factories\WheelPrizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'label_ngan', 'color', 'weight', 'sort_order', 'is_active'])]
class WheelPrize extends Model
{
    /** @use HasFactory<WheelPrizeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return Builder<$this>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Ô vòng quay đang bật, đúng thứ tự hiển thị / random có trọng số.
     *
     * @return list<array{id: int, label: string, label_ngan: string, color: string, weight: int}>
     */
    public static function rowsForWheel(): array
    {
        return static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (self $p) => $p->toWheelSlice())
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, label: string, label_ngan: string, color: string, weight: int}
     */
    public function toWheelSlice(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'label' => $this->label,
            'label_ngan' => $this->label_ngan,
            'color' => $this->color,
            'weight' => max(0, (int) $this->weight),
        ];
    }
}
