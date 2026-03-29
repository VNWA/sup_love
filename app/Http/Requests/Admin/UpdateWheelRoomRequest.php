<?php

namespace App\Http\Requests\Admin;

use App\Models\WheelRoom;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as RuleFacade;

class UpdateWheelRoomRequest extends FormRequest
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
        /** @var WheelRoom $room */
        $room = $this->route('wheel_room');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:64', RuleFacade::unique('wheel_rooms', 'slug')->ignore($room->getKey())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
