<?php

namespace App\Http\Controllers\Account;

use App\Enums\WheelPrizeWinStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrizeWinsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $status = $request->string('status', 'all')->value();
        if (! in_array($status, ['all', 'pending', 'received'], true)) {
            $status = 'all';
        }

        $query = $user->wheelPrizeWins()->latest();

        if ($status === 'pending') {
            $query->where('status', WheelPrizeWinStatus::Pending);
        } elseif ($status === 'received') {
            $query->where('status', WheelPrizeWinStatus::Received);
        }

        $wins = $query->paginate(20)->withQueryString();

        return Inertia::render('account/PrizeWins', [
            'wins' => $wins,
            'status' => $status,
        ]);
    }
}
