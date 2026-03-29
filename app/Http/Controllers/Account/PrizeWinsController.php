<?php

namespace App\Http\Controllers\Account;

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

        $spins = $user->wheelSpins()
            ->with([
                'betChoice:id,name',
                'resultChoice:id,name',
                'wheelRoom:id,name,slug',
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('account/PrizeWins', [
            'spins' => $spins,
        ]);
    }
}
