<?php

namespace App\Http\Requests\Admin;

use App\Enums\WheelPrizeWinStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWheelPrizeWinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(WheelPrizeWinStatus::class)],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
