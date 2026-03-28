<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWheelPrizeRequest;
use App\Models\WheelPrize;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WheelPrizeController extends Controller
{
    public function index(): Response
    {
        $prizes = WheelPrize::query()
            ->ordered()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('admin/wheel-prizes/Index', [
            'prizes' => $prizes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/wheel-prizes/Create');
    }

    public function store(StoreWheelPrizeRequest $request): RedirectResponse
    {
        WheelPrize::query()->create($request->prizePayload());

        return redirect()->route('admin.wheel-prizes.index')
            ->with('success', 'Đã thêm giải thưởng.');
    }

    public function edit(WheelPrize $wheelPrize): Response
    {
        return Inertia::render('admin/wheel-prizes/Edit', [
            'prize' => $wheelPrize,
        ]);
    }

    public function update(StoreWheelPrizeRequest $request, WheelPrize $wheelPrize): RedirectResponse
    {
        $wheelPrize->update($request->prizePayload());

        return redirect()->route('admin.wheel-prizes.index')
            ->with('success', 'Đã cập nhật giải thưởng.');
    }

    public function destroy(WheelPrize $wheelPrize): RedirectResponse
    {
        $wheelPrize->delete();

        return redirect()->route('admin.wheel-prizes.index')
            ->with('success', 'Đã xóa giải thưởng.');
    }
}
