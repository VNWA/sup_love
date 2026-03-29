<?php

namespace App\Http\Requests\Game;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as RuleFacade;

class SpinWheelRequest extends FormRequest
{
    /** @var list<string> */
    public const WISH_CATEGORY_KEYS = [
        'hon_nhan',
        'suc_khoe',
        'tinh_yeu',
        'gia_dinh',
        'su_nghiep',
        'ban_be',
        'du_lich',
        'tai_chinh',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, Rule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'wheel_room_id' => [
                'required',
                'integer',
                RuleFacade::exists('wheel_rooms', 'id')->where('is_active', true),
            ],
            'wheel_round_id' => ['required', 'integer', RuleFacade::exists('wheel_rounds', 'id')],
            'bet_amount' => ['required', 'integer', 'min:1'],
            'wish_category' => ['required', 'string', RuleFacade::in(self::WISH_CATEGORY_KEYS)],
            'participant_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
