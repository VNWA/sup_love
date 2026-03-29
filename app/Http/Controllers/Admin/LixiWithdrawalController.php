<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LixiWithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLixiWithdrawalRequest;
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
        $status = $request->string('status', '')->value();

        $query = LixiWithdrawal::query()
            ->with(['user:id,username,name', 'processedBy:id,username,name'])
            ->latest();

        if (in_array($status, ['pending', 'success', 'failed'], true)) {
            $query->where('status', $status);
        }

        $withdrawals = $query->paginate(25)->withQueryString();

        return Inertia::render('admin/lixi-withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'filterStatus' => $status !== '' ? $status : null,
        ]);
    }

    public function update(UpdateLixiWithdrawalRequest $request, LixiWithdrawal $lixiWithdrawal): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin !== null, 401);

        $status = LixiWithdrawalStatus::from($request->validated('status'));
        $note = $request->validated('admin_note');

        $this->lixiWithdrawalService->syncStatus($lixiWithdrawal, $status, $admin, $note);

        return redirect()
            ->route('admin.lixi-withdrawals.index')
            ->with('success', 'Đã cập nhật trạng thái yêu cầu.');
    }
}
