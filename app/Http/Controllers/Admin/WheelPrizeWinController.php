<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WheelPrizeWinStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWheelPrizeWinRequest;
use App\Models\WheelPrizeWin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WheelPrizeWinController extends Controller
{
    public function index(Request $request): Response
    {
        $statusFilter = $request->string('status')->trim()->value();
        $username = $request->string('username')->trim()->value();

        $query = WheelPrizeWin::query()
            ->with(['user:id,username,name', 'handler:id,username,name'])
            ->latest();

        if (in_array($statusFilter, [WheelPrizeWinStatus::Pending->value, WheelPrizeWinStatus::Received->value], true)) {
            $query->where('status', $statusFilter);
        }

        if ($username !== '') {
            $query->whereHas('user', function ($q) use ($username): void {
                $q->where('username', 'like', '%'.$username.'%');
            });
        }

        $wins = $query->paginate(30)->withQueryString();

        return Inertia::render('admin/wheel-prize-wins/Index', [
            'wins' => $wins,
            'filters' => [
                'status' => $statusFilter,
                'username' => $username,
            ],
        ]);
    }

    public function edit(WheelPrizeWin $wheelPrizeWin): Response
    {
        $wheelPrizeWin->loadMissing(['user:id,username,name', 'handler:id,username,name']);

        return Inertia::render('admin/wheel-prize-wins/Edit', [
            'win' => $wheelPrizeWin,
        ]);
    }

    public function update(UpdateWheelPrizeWinRequest $request, WheelPrizeWin $wheelPrizeWin): RedirectResponse
    {
        $status = WheelPrizeWinStatus::from($request->validated('status'));
        $wheelPrizeWin->status = $status;
        $note = $request->validated('admin_note');
        $wheelPrizeWin->admin_note = is_string($note) && trim($note) !== '' ? trim($note) : null;

        if ($status === WheelPrizeWinStatus::Received) {
            $wheelPrizeWin->received_at ??= now();
            $wheelPrizeWin->handled_by = $request->user()?->getKey();
        } else {
            $wheelPrizeWin->received_at = null;
            $wheelPrizeWin->handled_by = null;
        }

        $wheelPrizeWin->save();

        return redirect()->route('admin.wheel-prize-wins.index')
            ->with('success', 'Đã cập nhật phần thưởng.');
    }
}
