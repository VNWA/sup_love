<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWheelChoiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'color' => ['nullable', 'string', 'max:16'],
        ];
    }

    /**
     * @return array{name: string, sort_order: int, color: ?string}
     */
    public function choicePayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'color' => $validated['color'] ?? '#e91e63',
        ];
    }
}
