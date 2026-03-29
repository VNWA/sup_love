<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWheelRoomRequest;
use App\Models\WheelRoom;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WheelRoomController extends Controller
{
    public function index(): Response
    {
        $rooms = WheelRoom::query()
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/wheel-rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function edit(WheelRoom $wheelRoom): Response
    {
        return Inertia::render('admin/wheel-rooms/Edit', [
            'room' => [
                'id' => (int) $wheelRoom->getKey(),
                'name' => $wheelRoom->name,
                'slug' => $wheelRoom->slug,
                'is_active' => $wheelRoom->is_active,
            ],
        ]);
    }

    public function update(UpdateWheelRoomRequest $request, WheelRoom $wheelRoom): RedirectResponse
    {
        $data = $request->validated();
        $wheelRoom->name = $data['name'];
        $wheelRoom->slug = $data['slug'];
        $wheelRoom->is_active = $request->boolean('is_active', $wheelRoom->is_active);
        $wheelRoom->save();

        return redirect()
            ->route('admin.wheel-rooms.edit', $wheelRoom)
            ->with('success', 'Đã cập nhật phòng quay.');
    }
}
