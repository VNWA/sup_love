<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\SpinWheelRequest;
use App\Services\WheelBetSpinService;
use Illuminate\Http\JsonResponse;

class SpinWheelController extends Controller
{
    public function __construct(
        private WheelBetSpinService $wheelBetSpinService
    ) {}

    public function store(SpinWheelRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $result = $this->wheelBetSpinService->spin(
            $user,
            (int) $request->validated('wheel_room_id'),
            (int) $request->validated('wheel_round_id'),
            (int) $request->validated('bet_amount'),
            (string) $request->validated('wish_category'),
            $request->validated('participant_name'),
        );

        return response()->json($result);
    }
}
