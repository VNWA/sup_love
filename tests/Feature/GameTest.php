<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WheelPrizeWin;
use Database\Seeders\WheelPrizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WheelPrizeSeeder::class);
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
            ->get(route('game'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Game')
                ->has('wheelPrizes')
                ->where('costPerSpin', (int) config('wheel.cost_per_spin', 1))
            );
    }

    public function test_guest_cannot_spin_wheel(): void
    {
        $this->postJson(route('game.spin'))
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_spin_when_has_enough_points(): void
    {
        $cost = (int) config('wheel.cost_per_spin', 1);
        $before = max(50, $cost + 5);
        $user = User::factory()->create(['point' => $before]);

        $response = $this->actingAs($user)->postJson(route('game.spin'));

        $response->assertOk()
            ->assertJsonStructure([
                'winner_index',
                'prize' => ['label', 'label_ngan', 'color'],
                'points',
            ]);

        $user->refresh();
        $this->assertSame($before - $cost, $user->point);
        $this->assertSame($user->point, $response->json('points'));

        $this->assertSame(1, WheelPrizeWin::query()->where('user_id', $user->getKey())->count());
        $this->assertDatabaseHas('wheel_prize_wins', [
            'user_id' => $user->getKey(),
            'status' => 'pending',
        ]);
    }

    public function test_spin_fails_when_insufficient_points(): void
    {
        $cost = (int) config('wheel.cost_per_spin', 1);
        $user = User::factory()->create(['point' => max(0, $cost - 1)]);

        $this->actingAs($user)
            ->postJson(route('game.spin'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['point']);
    }
}
