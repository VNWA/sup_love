<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountPrizeWinsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_prize_wins(): void
    {
        $this->get(route('account.prize-wins'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_prize_wins_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.prize-wins'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('account/PrizeWins')
                ->has('spins.data'));
    }
}
