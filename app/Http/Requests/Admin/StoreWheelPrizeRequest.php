<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWheelPrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, array<int, Rule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:500'],
            'label_ngan' => ['required', 'string', 'max:80'],
            'color' => ['required', 'string', 'max:32', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
            'weight' => ['required', 'integer', 'min:0', 'max:999999'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{label: string, label_ngan: string, color: string, weight: int, sort_order: int, is_active: bool}
     */
    public function prizePayload(): array
    {
        $validated = $this->validated();

        return [
            'label' => $validated['label'],
            'label_ngan' => $validated['label_ngan'],
            'color' => $validated['color'],
            'weight' => (int) $validated['weight'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
