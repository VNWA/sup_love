<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\WheelSpinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpinWheelController extends Controller
{
    public function __construct(
        private WheelSpinService $wheelSpinService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $result = $this->wheelSpinService->spin($user);

        return response()->json($result);
    }
}
