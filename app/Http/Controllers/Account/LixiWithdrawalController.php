<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreLixiWithdrawalRequest;
use App\Models\LixiWithdrawal;
use App\Services\LixiWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LixiWithdrawalController extends Controller
{
    public function __construct(
        private LixiWithdrawalService $lixiWithdrawalService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $withdrawals = LixiWithdrawal::query()
            ->where('user_id', $user->getKey())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('account/LixiWithdrawals', [
            'withdrawals' => $withdrawals,
            'bankComplete' => $user->hasCompleteBankProfile(),
        ]);
    }

    public function store(StoreLixiWithdrawalRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $this->lixiWithdrawalService->createRequest($user, (int) $request->validated('amount'));

        return redirect()
            ->route('account.lixi-withdrawals.index')
            ->with('success', 'Đã gửi yêu cầu rút lì xì. Vui lòng chờ xử lý.');
    }
}
