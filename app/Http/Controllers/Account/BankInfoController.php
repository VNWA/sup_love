<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateBankInfoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankInfoController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        return Inertia::render('account/BankInfo', [
            'bank' => [
                'bank_name' => $user->bank_name,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_holder' => $user->bank_account_holder,
            ],
        ]);
    }

    public function update(UpdateBankInfoRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $data = $request->validated();
        $user->bank_name = $data['bank_name'] ?? null;
        $user->bank_account_number = $data['bank_account_number'] ?? null;
        $user->bank_account_holder = $data['bank_account_holder'] ?? null;
        $user->save();

        return redirect()
            ->route('account.bank')
            ->with('success', 'Đã lưu thông tin nhận lì xì.');
    }
}
