<?php

namespace App\Http\Controllers\Account;

use App\Enums\PointTransactionType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $tab = $request->string('tab', 'all')->value();
        if (! in_array($tab, ['all', 'spin', 'topup'], true)) {
            $tab = 'all';
        }

        $query = $user->pointTransactions()
            ->with('actor:id,name,username')
            ->latest();

        if ($tab === 'spin') {
            $query->where('type', PointTransactionType::WheelSpin);
        } elseif ($tab === 'topup') {
            $query->where('type', PointTransactionType::AdminCredit);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return Inertia::render('account/History', [
            'transactions' => $transactions,
            'tab' => $tab,
        ]);
    }
}
