<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLixiWithdrawalRequest extends FormRequest
{
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
            'amount' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = $this->user();
                    if ($user === null) {
                        return;
                    }

                    if (! is_numeric($value) || (int) $value > (int) $user->point) {
                        $fail('Số điểm rút không được vượt điểm hiện có.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->user()?->fresh();
                if ($user === null) {
                    return;
                }

                if (! $user->hasCompleteBankProfile()) {
                    $validator->errors()->add(
                        'bank',
                        'Vui lòng cập nhật đầy đủ thông tin ngân hàng nhận lì xì (tên ngân hàng, số tài khoản, chủ tài khoản) tại mục «Thông tin nhận lì xì» trước khi gửi yêu cầu rút.',
                    );
                }
            },
        ];
    }
}
