<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelSpin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GamePageController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $raw = $request->query('room');
        $trimmed = is_string($raw) ? trim($raw) : '';

        if ($trimmed === '') {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Vui lòng nhập ID phòng hoặc mã phòng (slug) ở trang chủ trước khi vào vòng quay.',
                );
        }

        $room = ctype_digit($trimmed)
            ? WheelRoom::query()->whereKey((int) $trimmed)->first()
            : WheelRoom::query()->where('slug', $trimmed)->first();

        if ($room === null) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Không tìm thấy phòng quay. Kiểm tra lại ID số hoặc mã phòng do quản trị cung cấp.',
                );
        }

        $openRound = $room->openRound();
        $user = $request->user();

        $hasSpunCurrentRound = false;
        $lastSpinPreview = null;

        if ($user !== null && $openRound !== null) {
            $spin = WheelSpin::query()
                ->where('wheel_round_id', $openRound->getKey())
                ->where('user_id', $user->getKey())
                ->first();

            if ($spin !== null) {
                $hasSpunCurrentRound = true;
                $lastSpinPreview = $this->spinPreviewFromSpin($spin);
            }
        }

        $defaultParticipantName = '';
        if ($user !== null) {
            $n = trim((string) $user->name);
            if ($n !== '' && ! str_starts_with($n, 'user_')) {
                $defaultParticipantName = $n;
            }
        }

        return Inertia::render('Game', [
            'wheelRoom' => [
                'id' => (int) $room->getKey(),
                'name' => $room->name,
                'slug' => $room->slug,
                'is_active' => $room->is_active,
            ],
            'wheelRound' => $openRound === null ? null : [
                'id' => (int) $openRound->getKey(),
                'round_number' => $openRound->round_number,
            ],
            'hasSpunCurrentRound' => $hasSpunCurrentRound,
            'lastSpinPreview' => $lastSpinPreview,
            'wheelChoices' => WheelChoice::rowsForWheel(),
            'defaultParticipantName' => $defaultParticipantName,
        ]);
    }

    /**
     * @return array{result_name: string, result_color: string, bet_amount: int, is_consolation: bool}|null
     */
    private function spinPreviewFromSpin(WheelSpin $spin): ?array
    {
        $rows = WheelChoice::rowsForWheel();
        if ($rows === []) {
            return null;
        }

        $winnerIndex = 0;
        foreach ($rows as $i => $r) {
            if ((int) $r['id'] === (int) $spin->result_choice_id) {
                $winnerIndex = $i;
                break;
            }
        }

        $resultRow = $rows[$winnerIndex] ?? $rows[0];
        $isConsolation = (int) $spin->result_choice_id === WheelChoice::CONSOLATION_CHOICE_ID;

        return [
            'result_name' => $resultRow['name'],
            'result_color' => $resultRow['color'],
            'bet_amount' => (int) $spin->bet_amount,
            'is_consolation' => $isConsolation,
        ];
    }
}
