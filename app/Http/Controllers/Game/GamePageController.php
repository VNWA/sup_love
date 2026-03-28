<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\WheelPrize;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GamePageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Game', [
            'wheelPrizes' => WheelPrize::rowsForWheel(),
            'costPerSpin' => (int) config('wheel.cost_per_spin', 1),
        ]);
    }
}
