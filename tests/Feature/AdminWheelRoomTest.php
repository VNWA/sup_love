<?php

namespace Tests\Feature;

use App\Enums\WheelRoundStatus;
use App\Events\WheelRoundEnded;
use App\Events\WheelRoundStarted;
use App\Models\User;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelRound;
use Database\Seeders\WheelChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminWheelRoomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        $this->seed(WheelChoiceSeeder::class);
    }

    public function test_non_admin_cannot_access_wheel_rooms(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.wheel-rooms.index'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_list_wheel_rooms(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.wheel-rooms.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/wheel-rooms/Index')
                ->has('rooms.data'));
    }

    public function test_admin_can_view_wheel_room_rounds_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.wheel-rooms.rounds.index', $room))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/wheel-rooms/rounds/Index')
                ->has('room')
                ->has('rounds.data')
                ->where('nextRoundNumber', 1));
    }

    public function test_admin_can_start_round_and_broadcasts_event(): void
    {
        Event::fake([WheelRoundStarted::class]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $choiceId = (int) WheelChoice::query()
            ->where('id', '!=', WheelChoice::CONSOLATION_CHOICE_ID)
            ->orderBy('id')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.store', $room), [
                'result_choice_id' => $choiceId,
            ])
            ->assertRedirect(route('admin.wheel-rooms.rounds.index', $room));

        $this->assertSame(1, WheelRound::query()->where('wheel_room_id', $room->getKey())->count());
        $round = WheelRound::query()->where('wheel_room_id', $room->getKey())->firstOrFail();
        $this->assertSame(WheelRoundStatus::Open, $round->status);
        $this->assertSame('Vòng quay #1', $round->name);

        Event::assertDispatched(WheelRoundStarted::class, function (WheelRoundStarted $e) use ($room, $round): bool {
            return $e->wheelRoomId === (int) $room->getKey()
                && $e->wheelRoundId === (int) $round->getKey()
                && $e->roundNumber === 1;
        });
    }

    public function test_admin_cannot_start_second_round_before_ending_first(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $choiceId = (int) WheelChoice::query()
            ->where('id', '!=', WheelChoice::CONSOLATION_CHOICE_ID)
            ->orderBy('id')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.store', $room), [
                'result_choice_id' => $choiceId,
            ])
            ->assertRedirect(route('admin.wheel-rooms.rounds.index', $room));

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.store', $room), [
                'result_choice_id' => $choiceId,
            ])
            ->assertSessionHasErrors('result_choice_id');
    }

    public function test_admin_can_end_round_and_broadcasts_event(): void
    {
        Event::fake([WheelRoundEnded::class]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $choiceId = (int) WheelChoice::query()
            ->where('id', '!=', WheelChoice::CONSOLATION_CHOICE_ID)
            ->orderBy('id')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.store', $room), [
                'result_choice_id' => $choiceId,
            ])
            ->assertRedirect(route('admin.wheel-rooms.rounds.index', $room));

        $round = WheelRound::query()->where('wheel_room_id', $room->getKey())->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.end', [$room, $round]))
            ->assertRedirect(route('admin.wheel-rooms.rounds.index', $room));

        $round->refresh();
        $this->assertSame(WheelRoundStatus::Closed, $round->status);
        $this->assertNotNull($round->ended_at);

        Event::assertDispatched(WheelRoundEnded::class, function (WheelRoundEnded $e) use ($room, $round): bool {
            return $e->wheelRoomId === (int) $room->getKey()
                && $e->wheelRoundId === (int) $round->getKey()
                && $e->roundNumber === 1;
        });
    }

    public function test_admin_can_create_round_with_custom_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $choiceId = (int) WheelChoice::query()
            ->where('id', '!=', WheelChoice::CONSOLATION_CHOICE_ID)
            ->orderBy('id')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.wheel-rooms.rounds.store', $room), [
                'result_choice_id' => $choiceId,
                'name' => 'Khai xuân 2026',
            ])
            ->assertRedirect(route('admin.wheel-rooms.rounds.index', $room));

        $round = WheelRound::query()->where('wheel_room_id', $room->getKey())->firstOrFail();
        $this->assertSame('Khai xuân 2026', $round->name);
    }
}
