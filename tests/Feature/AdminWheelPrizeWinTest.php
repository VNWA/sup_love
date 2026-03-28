<?php

namespace Tests\Feature;

use App\Enums\WheelPrizeWinStatus;
use App\Models\User;
use App\Models\WheelPrizeWin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminWheelPrizeWinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
    }

    public function test_non_admin_cannot_list_wheel_prize_wins(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.wheel-prize-wins.index'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_list_wheel_prize_wins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        WheelPrizeWin::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.wheel-prize-wins.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/wheel-prize-wins/Index')
                ->has('wins.data', 1));
    }

    public function test_admin_can_mark_win_as_received(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        /** @var WheelPrizeWin $win */
        $win = WheelPrizeWin::factory()->create([
            'status' => WheelPrizeWinStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.wheel-prize-wins.update', $win), [
                'status' => WheelPrizeWinStatus::Received->value,
                'admin_note' => 'Đã giao quà',
            ])
            ->assertRedirect(route('admin.wheel-prize-wins.index'));

        $win->refresh();
        $this->assertSame(WheelPrizeWinStatus::Received, $win->status);
        $this->assertSame('Đã giao quà', $win->admin_note);
        $this->assertNotNull($win->received_at);
        $this->assertSame($admin->getKey(), $win->handled_by);
    }

    public function test_admin_can_revert_win_to_pending(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        /** @var WheelPrizeWin $win */
        $win = WheelPrizeWin::factory()->create([
            'status' => WheelPrizeWinStatus::Received,
            'received_at' => now(),
            'handled_by' => $admin->getKey(),
            'admin_note' => 'old',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.wheel-prize-wins.update', $win), [
                'status' => WheelPrizeWinStatus::Pending->value,
                'admin_note' => null,
            ])
            ->assertRedirect(route('admin.wheel-prize-wins.index'));

        $win->refresh();
        $this->assertSame(WheelPrizeWinStatus::Pending, $win->status);
        $this->assertNull($win->received_at);
        $this->assertNull($win->handled_by);
    }
}
