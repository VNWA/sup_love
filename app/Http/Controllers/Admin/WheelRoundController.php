<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWheelRoundRequest;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelRound;
use App\Services\WheelRoundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WheelRoundController extends Controller
{
    public function __construct(
        private WheelRoundService $wheelRoundService
    ) {}

    public function index(WheelRoom $wheelRoom): Response
    {
        $open = $wheelRoom->openRound();

        $rounds = WheelRound::query()
            ->where('wheel_room_id', $wheelRoom->getKey())
            ->with('resultChoice:id,name')
            ->withCount('wheelSpins')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $choices = WheelChoice::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name']);

        return Inertia::render('admin/wheel-rooms/rounds/Index', [
            'room' => [
                'id' => (int) $wheelRoom->getKey(),
                'name' => $wheelRoom->name,
                'slug' => $wheelRoom->slug,
                'is_active' => $wheelRoom->is_active,
            ],
            'openRound' => $open === null ? null : [
                'id' => (int) $open->getKey(),
                'round_number' => $open->round_number,
                'result_choice_id' => (int) $open->result_choice_id,
                'result_choice' => $open->resultChoice !== null
                    ? [
                        'id' => (int) $open->resultChoice->getKey(),
                        'name' => $open->resultChoice->name,
                    ]
                    : null,
                'started_at' => $open->started_at?->toIso8601String(),
            ],
            'rounds' => $rounds,
            'choicesForRound' => $choices,
        ]);
    }

    public function store(StoreWheelRoundRequest $request, WheelRoom $wheelRoom): RedirectResponse
    {
        $this->wheelRoundService->startRound(
            $wheelRoom,
            (int) $request->validated('result_choice_id'),
            $request->user(),
        );

        return redirect()
            ->route('admin.wheel-rooms.rounds.index', $wheelRoom)
            ->with('success', 'Đã khởi tạo vòng quay mới.');
    }

    public function end(Request $request, WheelRoom $wheelRoom, WheelRound $wheelRound): RedirectResponse
    {
        if ((int) $wheelRound->wheel_room_id !== (int) $wheelRoom->getKey()) {
            abort(404);
        }

        $this->wheelRoundService->endRound($wheelRound, $request->user());

        return redirect()
            ->route('admin.wheel-rooms.rounds.index', $wheelRoom)
            ->with('success', 'Đã kết thúc vòng quay.');
    }
}
