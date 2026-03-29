<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Enums\WheelRoundStatus;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelRound;
use App\Models\WheelSpin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WheelBetSpinService
{
    /**
     * Quay theo kết quả đã định sẵn; không trừ/cộng điểm tài khoản.
     *
     * @return array{
     *     winner_index: int,
     *     result: array{id: int, name: string, color: string},
     *     is_consolation: bool,
     *     bet_amount: int,
     *     wish_category: string,
     *     participant_name: string,
     *     points: int,
     *     wheel_round_id: int
     * }
     */
    public function spin(User $user, int $wheelRoomId, int $wheelRoundId, int $betAmount, string $wishCategory, ?string $participantNameInput): array
    {
        $rows = WheelChoice::rowsForWheel();

        if ($rows === []) {
            throw ValidationException::withMessages([
                'wheel' => ['Chưa có ô lựa chọn trên vòng quay.'],
            ]);
        }

        $choiceIds = array_map(fn (array $r): int => (int) $r['id'], $rows);

        $participantName = $this->resolveParticipantName($user, $participantNameInput);

        return DB::transaction(function () use ($user, $wheelRoomId, $wheelRoundId, $betAmount, $wishCategory, $rows, $choiceIds, $participantName) {
            /** @var WheelRound|null $round */
            $round = WheelRound::query()
                ->whereKey($wheelRoundId)
                ->lockForUpdate()
                ->first();

            if ($round === null || $round->status !== WheelRoundStatus::Open) {
                throw ValidationException::withMessages([
                    'wheel_round_id' => ['Vòng quay không hợp lệ hoặc đã kết thúc.'],
                ]);
            }

            if ((int) $round->wheel_room_id !== $wheelRoomId) {
                throw ValidationException::withMessages([
                    'wheel_round_id' => ['Vòng quay không thuộc phòng này.'],
                ]);
            }

            /** @var WheelRoom $room */
            $room = WheelRoom::query()
                ->whereKey($wheelRoomId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($room === null) {
                throw ValidationException::withMessages([
                    'wheel_room_id' => ['Phòng quay không tồn tại hoặc đã tạm dừng.'],
                ]);
            }

            if (WheelSpin::query()
                ->where('wheel_round_id', $round->getKey())
                ->where('user_id', $user->getKey())
                ->exists()) {
                throw ValidationException::withMessages([
                    'wheel_round_id' => ['Bạn đã quay trong vòng này. Mỗi vòng chỉ được quay một lần.'],
                ]);
            }

            /** @var User $locked */
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $resultChoiceId = (int) $round->result_choice_id;

            if (! in_array($resultChoiceId, $choiceIds, true)) {
                throw ValidationException::withMessages([
                    'wheel' => ['Cấu hình kết quả vòng quay không hợp lệ.'],
                ]);
            }

            $winnerIndex = null;
            foreach ($rows as $i => $r) {
                if ((int) $r['id'] === $resultChoiceId) {
                    $winnerIndex = $i;
                    break;
                }
            }

            if ($winnerIndex === null) {
                $winnerIndex = 0;
            }

            $resultRow = $rows[$winnerIndex] ?? $rows[0];
            $resultName = $resultRow['name'] ?? '';
            $isConsolation = $resultChoiceId === WheelChoice::CONSOLATION_CHOICE_ID;

            $transaction = PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => null,
                'type' => PointTransactionType::WheelSpin,
                'amount' => 0,
                'balance_after' => max(0, (int) $locked->point),
                'admin_note' => null,
                'meta' => [
                    'winner_index' => $winnerIndex,
                    'wheel_room_id' => (int) $room->getKey(),
                    'wheel_room_name' => $room->name,
                    'wheel_room_slug' => $room->slug,
                    'wheel_round_id' => (int) $round->getKey(),
                    'wheel_round_number' => $round->round_number,
                    'wish_category' => $wishCategory,
                    'bet_amount' => $betAmount,
                    'participant_name' => $participantName,
                    'result_choice_id' => $resultChoiceId,
                    'result_choice_name' => $resultName,
                    'is_consolation' => $isConsolation,
                    'predetermined_round' => true,
                    'no_point_movement' => true,
                ],
            ]);

            WheelSpin::query()->create([
                'user_id' => $locked->getKey(),
                'wheel_room_id' => $room->getKey(),
                'wheel_round_id' => $round->getKey(),
                'bet_choice_id' => $resultChoiceId,
                'bet_amount' => $betAmount,
                'wish_category' => $wishCategory,
                'participant_name' => $participantName,
                'result_choice_id' => $resultChoiceId,
                'payout' => 0,
                'was_rigged' => false,
                'point_transaction_id' => $transaction->getKey(),
            ]);

            return [
                'winner_index' => $winnerIndex,
                'result' => [
                    'id' => $resultChoiceId,
                    'name' => $resultName,
                    'color' => $resultRow['color'] ?? '#e91e63',
                ],
                'is_consolation' => $isConsolation,
                'bet_amount' => $betAmount,
                'wish_category' => $wishCategory,
                'participant_name' => $participantName,
                'points' => (int) $locked->point,
                'wheel_round_id' => (int) $round->getKey(),
            ];
        });
    }

    /**
     * Tên hiển thị trên lượt quay: ưu tiên ô nhập; không có thì tên tài khoản (nếu hợp lệ); cuối cùng tên ngẫu nhiên gọn.
     */
    private function resolveParticipantName(User $user, ?string $input): string
    {
        $trimmed = trim((string) $input);
        if ($trimmed !== '') {
            return mb_substr($trimmed, 0, 120);
        }

        $fromUser = trim((string) $user->name);
        if ($fromUser !== '' && ! str_starts_with($fromUser, 'user_')) {
            return mb_substr($fromUser, 0, 120);
        }

        return 'Bạn '.str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }
}
