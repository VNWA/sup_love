<?php

namespace Tests\Feature;

use App\Enums\WheelRoundStatus;
use App\Models\User;
use App\Models\WheelChoice;
use App\Models\WheelRoom;
use App\Models\WheelRound;
use App\Models\WheelSpin;
use Database\Seeders\WheelChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WheelChoiceSeeder::class);
    }

    private function defaultRoomId(): int
    {
        $id = WheelRoom::query()->where('slug', 'default')->value('id');
        $this->assertNotNull($id);

        return (int) $id;
    }

    private function gameUrl(string|int $room): string
    {
        return route('game', ['room' => (string) $room]);
    }

    private function firstChoiceId(): int
    {
        return (int) WheelChoice::query()
            ->where('id', '!=', WheelChoice::CONSOLATION_CHOICE_ID)
            ->orderBy('id')
            ->value('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function spinPayload(int $roomId, int $roundId, int $betAmount = 100_000, string $wishCategory = 'tinh_yeu'): array
    {
        return [
            'wheel_room_id' => $roomId,
            'wheel_round_id' => $roundId,
            'bet_amount' => $betAmount,
            'wish_category' => $wishCategory,
        ];
    }

    private function openRoundForDefaultRoom(int $resultChoiceId): WheelRound
    {
        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();

        return WheelRound::query()->create([
            'wheel_room_id' => $room->getKey(),
            'round_number' => 1,
            'name' => 'Vòng quay #1',
            'status' => WheelRoundStatus::Open,
            'result_choice_id' => $resultChoiceId,
            'started_at' => now(),
            'ended_at' => null,
        ]);
    }

    public function test_guest_is_redirected_from_game(): void
    {
        $this->get(route('game'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_game_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->gameUrl('default'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game')
                ->has('wheelRoom')
                ->where('wheelRoom.is_active', true)
                ->has('wheelChoices')
                ->where('hasSpunCurrentRound', false)
            );
    }

    public function test_authenticated_user_without_room_query_redirects_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('game'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_authenticated_user_unknown_room_redirects_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->gameUrl('khong-ton-tai-xyz'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_game_page_resolves_room_by_numeric_id(): void
    {
        $user = User::factory()->create();
        $id = $this->defaultRoomId();

        $this->actingAs($user)
            ->get($this->gameUrl($id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game')
                ->where('wheelRoom.id', $id)
                ->where('wheelRoom.slug', 'default'));
    }

    public function test_guest_cannot_spin_wheel(): void
    {
        $this->postJson(route('game.spin'), $this->spinPayload($this->defaultRoomId(), 1))
            ->assertUnauthorized();
    }

    public function test_spin_requires_validation(): void
    {
        $user = User::factory()->create(['point' => 50]);

        $this->actingAs($user)
            ->postJson(route('game.spin'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['wheel_room_id', 'wheel_round_id', 'bet_amount', 'wish_category']);
    }

    public function test_spin_succeeds_and_does_not_change_points(): void
    {
        $resultId = $this->firstChoiceId();
        $before = 50;
        $user = User::factory()->create(['point' => $before]);

        $round = $this->openRoundForDefaultRoom($resultId);

        $response = $this->actingAs($user)->postJson(
            route('game.spin'),
            $this->spinPayload($this->defaultRoomId(), (int) $round->getKey(), 200_000, 'gia_dinh'),
        );

        $response->assertOk()
            ->assertJsonPath('is_consolation', false)
            ->assertJsonPath('result.id', $resultId);

        $user->refresh();
        $this->assertSame($before, $user->point);

        $this->assertSame(1, WheelSpin::query()->where('user_id', $user->getKey())->where('wheel_round_id', $round->getKey())->count());
    }

    public function test_spin_returns_consolation_when_result_is_consolation_slot(): void
    {
        $user = User::factory()->create(['point' => 100]);

        $round = $this->openRoundForDefaultRoom(WheelChoice::CONSOLATION_CHOICE_ID);

        $response = $this->actingAs($user)->postJson(
            route('game.spin'),
            $this->spinPayload($this->defaultRoomId(), (int) $round->getKey(), 50_000, 'suc_khoe'),
        );

        $response->assertOk()
            ->assertJsonPath('is_consolation', true);
    }

    public function test_cannot_spin_twice_in_same_round(): void
    {
        $resultId = $this->firstChoiceId();
        $user = User::factory()->create(['point' => 100]);

        $round = $this->openRoundForDefaultRoom($resultId);

        $this->actingAs($user)->postJson(
            route('game.spin'),
            $this->spinPayload($this->defaultRoomId(), (int) $round->getKey()),
        )->assertOk();

        $this->actingAs($user)
            ->postJson(
                route('game.spin'),
                $this->spinPayload($this->defaultRoomId(), (int) $round->getKey()),
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['wheel_round_id']);
    }

    public function test_game_page_shows_inactive_room_without_404(): void
    {
        $user = User::factory()->create();

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $room->is_active = false;
        $room->save();

        $this->actingAs($user)
            ->get($this->gameUrl('default'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game')
                ->where('wheelRoom.is_active', false));
    }

    public function test_spin_rejected_when_room_is_inactive(): void
    {
        $resultId = $this->firstChoiceId();
        $user = User::factory()->create(['point' => 50]);

        $room = WheelRoom::query()->where('slug', 'default')->firstOrFail();
        $room->is_active = false;
        $room->save();

        $round = WheelRound::query()->create([
            'wheel_room_id' => $room->getKey(),
            'round_number' => 1,
            'name' => 'Vòng quay #1',
            'status' => WheelRoundStatus::Open,
            'result_choice_id' => $resultId,
            'started_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(
                route('game.spin'),
                $this->spinPayload((int) $room->getKey(), (int) $round->getKey()),
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['wheel_room_id']);
    }
}
