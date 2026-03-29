<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWheelChoiceRequest;
use App\Models\WheelChoice;
use App\Models\WheelSpin;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WheelChoiceController extends Controller
{
    public function index(): Response
    {
        $choices = WheelChoice::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('admin/wheel-choices/Index', [
            'choices' => $choices,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/wheel-choices/Create');
    }

    public function store(StoreWheelChoiceRequest $request): RedirectResponse
    {
        WheelChoice::query()->create($request->choicePayload());

        return redirect()->route('admin.wheel-choices.index')
            ->with('success', 'Đã thêm ô lựa chọn.');
    }

    public function edit(WheelChoice $wheelChoice): Response
    {
        return Inertia::render('admin/wheel-choices/Edit', [
            'choice' => $wheelChoice->only(['id', 'name', 'sort_order', 'color']),
        ]);
    }

    public function update(StoreWheelChoiceRequest $request, WheelChoice $wheelChoice): RedirectResponse
    {
        $wheelChoice->update($request->choicePayload());

        return redirect()->route('admin.wheel-choices.index')
            ->with('success', 'Đã cập nhật ô lựa chọn.');
    }

    public function destroy(WheelChoice $wheelChoice): RedirectResponse
    {
        $referenced = WheelSpin::query()
            ->where(function ($q) use ($wheelChoice): void {
                $q->where('bet_choice_id', $wheelChoice->getKey())
                    ->orWhere('result_choice_id', $wheelChoice->getKey());
            })
            ->exists();

        if ($referenced) {
            return redirect()->route('admin.wheel-choices.index')
                ->with('error', 'Không xóa được: đã có lượt quay gắn với ô này.');
        }

        $wheelChoice->delete();

        return redirect()->route('admin.wheel-choices.index')
            ->with('success', 'Đã xóa ô lựa chọn.');
    }
}
