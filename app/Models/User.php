<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'username',
    'point',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'point' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->name)) {
                $user->name = 'user_'.Str::random(10);
            }

            if (empty($user->email)) {
                $user->email = $user->username.'_'.Str::random(10).'@example.com';
            }
        });
    }

    /**
     * @return HasMany<PointTransaction, $this>
     */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * @return HasMany<WheelSpin, $this>
     */
    public function wheelSpins(): HasMany
    {
        return $this->hasMany(WheelSpin::class);
    }

    /**
     * @return HasMany<LixiWithdrawal, $this>
     */
    public function lixiWithdrawals(): HasMany
    {
        return $this->hasMany(LixiWithdrawal::class);
    }

    /**
     * Đủ thông tin ngân hàng để rút lì xì (tên NH, số TK, chủ TK).
     */
    public function hasCompleteBankProfile(): bool
    {
        foreach (['bank_name', 'bank_account_number', 'bank_account_holder'] as $field) {
            if (trim((string) ($this->{$field} ?? '')) === '') {
                return false;
            }
        }

        return true;
    }
}
