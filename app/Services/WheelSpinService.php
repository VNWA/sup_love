<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Enums\WheelPrizeWinStatus;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WheelPrize;
use App\Models\WheelPrizeWin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WheelSpinService
{
    /**
     * @return array{winner_index: int, prize: array{label: string, label_ngan: string, color: string, id?: int}, points: int}
     */
    public function spin(User $user): array
    {
        $cost = (int) config('wheel.cost_per_spin', 1);
        $prizes = WheelPrize::rowsForWheel();

        if ($prizes === []) {
            throw ValidationException::withMessages([
                'wheel' => ['Cấu hình vòng quay chưa sẵn sàng.'],
            ]);
        }

        return DB::transaction(function () use ($user, $cost, $prizes) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->point < $cost) {
                throw ValidationException::withMessages([
                    'point' => ['Bạn không đủ điểm để quay.'],
                ]);
            }

            $winnerIndex = $this->pickWeightedIndex($prizes);
            $prize = $prizes[$winnerIndex] ?? $prizes[0];

            $locked->point -= $cost;
            $locked->save();

            $transaction = PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => null,
                'type' => PointTransactionType::WheelSpin,
                'amount' => -$cost,
                'balance_after' => max(0, (int) $locked->point),
                'admin_note' => null,
                'meta' => [
                    'winner_index' => $winnerIndex,
                    'prize_label' => $prize['label'] ?? '',
                    'prize_label_ngan' => $prize['label_ngan'] ?? '',
                    'wheel_prize_id' => $prize['id'] ?? null,
                ],
            ]);

            WheelPrizeWin::query()->create([
                'user_id' => $locked->getKey(),
                'point_transaction_id' => $transaction->getKey(),
                'wheel_prize_id' => isset($prize['id']) ? (int) $prize['id'] : null,
                'prize_label' => (string) ($prize['label'] ?? ''),
                'prize_label_ngan' => (string) ($prize['label_ngan'] ?? ''),
                'color' => (string) ($prize['color'] ?? '#e91e63'),
                'status' => WheelPrizeWinStatus::Pending,
                'received_at' => null,
                'handled_by' => null,
                'admin_note' => null,
            ]);

            return [
                'winner_index' => $winnerIndex,
                'prize' => [
                    'label' => $prize['label'] ?? '',
                    'label_ngan' => $prize['label_ngan'] ?? '',
                    'color' => $prize['color'] ?? '#e91e63',
                ],
                'points' => (int) $locked->point,
            ];
        });
    }

    /**
     * @param  array<int, array{weight?: int|float}>  $prizes
     */
    private function pickWeightedIndex(array $prizes): int
    {
        $weights = array_map(fn (array $p) => max(0, (float) ($p['weight'] ?? 1)), $prizes);
        $total = array_sum($weights);

        if ($total <= 0) {
            return random_int(0, count($prizes) - 1);
        }

        $r = (mt_rand() / mt_getrandmax()) * $total;

        foreach ($weights as $i => $w) {
            $r -= $w;

            if ($r <= 0) {
                return $i;
            }
        }

        return count($prizes) - 1;
    }
}
