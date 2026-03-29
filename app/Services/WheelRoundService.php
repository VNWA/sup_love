<?php

namespace App\Services;

use App\Enums\WheelRoundStatus;
use App\Events\WheelRoundEnded;
use App\Events\WheelRoundStarted;
use App\Models\User;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelRound;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WheelRoundService
{
    /**
     * Khởi tạo vòng quay mới (kết quả đã định sẵn). Chỉ khi không còn vòng đang mở.
     */
    public function startRound(WheelRoom $room, int $resultChoiceId, User $admin, ?string $displayName = null): WheelRound
    {
        if (! $admin->hasRole('admin')) {
            abort(403);
        }

        return DB::transaction(function () use ($room, $resultChoiceId, $displayName) {
            /** @var WheelRoom $lockedRoom */
            $lockedRoom = WheelRoom::query()->whereKey($room->getKey())->lockForUpdate()->firstOrFail();

            if (! $lockedRoom->is_active) {
                throw ValidationException::withMessages([
                    'result_choice_id' => ['Phòng đang tạm dừng, không thể mở vòng mới.'],
                ]);
            }

            if (WheelRound::query()->where('wheel_room_id', $lockedRoom->getKey())->where('status', WheelRoundStatus::Open)->exists()) {
                throw ValidationException::withMessages([
                    'result_choice_id' => ['Vui lòng kết thúc vòng quay hiện tại trước khi khởi tạo vòng mới.'],
                ]);
            }

            if (! WheelChoice::query()->whereKey($resultChoiceId)->exists()) {
                throw ValidationException::withMessages([
                    'result_choice_id' => ['Ô kết quả không hợp lệ.'],
                ]);
            }

            $nextNumber = (int) (WheelRound::query()
                ->where('wheel_room_id', $lockedRoom->getKey())
                ->max('round_number') ?? 0) + 1;

            $name = $displayName !== null && trim($displayName) !== ''
                ? mb_substr(trim($displayName), 0, 120)
                : sprintf('Vòng quay #%d', $nextNumber);

            $round = WheelRound::query()->create([
                'wheel_room_id' => $lockedRoom->getKey(),
                'round_number' => $nextNumber,
                'name' => $name,
                'status' => WheelRoundStatus::Open,
                'result_choice_id' => $resultChoiceId,
                'started_at' => now(),
                'ended_at' => null,
            ]);

            event(new WheelRoundStarted(
                (int) $lockedRoom->getKey(),
                (int) $round->getKey(),
                (int) $round->round_number,
            ));

            return $round;
        });
    }

    /**
     * Kết thúc vòng quay đang mở.
     */
    public function endRound(WheelRound $round, User $admin): void
    {
        if (! $admin->hasRole('admin')) {
            abort(403);
        }

        DB::transaction(function () use ($round) {
            /** @var WheelRound $locked */
            $locked = WheelRound::query()->whereKey($round->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== WheelRoundStatus::Open) {
                throw ValidationException::withMessages([
                    'round' => ['Vòng quay này đã kết thúc.'],
                ]);
            }

            $locked->status = WheelRoundStatus::Closed;
            $locked->ended_at = now();
            $locked->save();

            event(new WheelRoundEnded(
                (int) $locked->wheel_room_id,
                (int) $locked->getKey(),
                (int) $locked->round_number,
            ));
        });
    }
}
