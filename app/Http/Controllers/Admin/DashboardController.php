<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PointTransactionType;
use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $today = now()->startOfDay();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users_count' => User::query()->count(),
                'points_credited_today' => (int) PointTransaction::query()
                    ->where('type', PointTransactionType::AdminCredit)
                    ->where('created_at', '>=', $today)
                    ->sum('amount'),
                'spins_today' => PointTransaction::query()
                    ->where('type', PointTransactionType::WheelSpin)
                    ->where('created_at', '>=', $today)
                    ->count(),
            ],
        ]);
    }
}
