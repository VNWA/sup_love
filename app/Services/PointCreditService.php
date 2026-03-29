<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointCreditService
{
    public function creditFromAdmin(User $actor, User $target, int $amount, string $note): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        DB::transaction(function () use ($actor, $target, $amount, $note) {
            /** @var User $locked */
            $locked = User::query()->whereKey($target->getKey())->lockForUpdate()->firstOrFail();

            $locked->point = (int) $locked->point + $amount;
            $locked->save();

            PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $actor->getKey(),
                'type' => PointTransactionType::AdminCredit,
                'amount' => $amount,
                'balance_after' => max(0, (int) $locked->point),
                'admin_note' => $note,
                'meta' => null,
            ]);
        });
    }

    public function debitFromAdmin(User $actor, User $target, int $amount, string $note): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        DB::transaction(function () use ($actor, $target, $amount, $note): void {
            /** @var User $locked */
            $locked = User::query()->whereKey($target->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->point < $amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Người chơi chỉ còn '.$locked->point.' điểm, không đủ để trừ.'],
                ]);
            }

            $locked->point = (int) $locked->point - $amount;
            $locked->save();

            PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $actor->getKey(),
                'type' => PointTransactionType::AdminDebit,
                'amount' => -$amount,
                'balance_after' => max(0, (int) $locked->point),
                'admin_note' => $note,
                'meta' => null,
            ]);
        });
    }
}
