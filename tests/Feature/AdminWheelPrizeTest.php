<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WheelPrize;
use Database\Seeders\WheelPrizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminWheelPrizeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        $this->seed(WheelPrizeSeeder::class);
    }

    public function test_non_admin_cannot_manage_wheel_prizes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.wheel-prizes.index'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_create_wheel_prize(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'label' => 'Giải test đầy đủ',
            'label_ngan' => 'Test',
            'color' => '#abc',
            'weight' => 5,
            'sort_order' => 99,
            'is_active' => true,
        ];

        $this->actingAs($admin)
            ->post(route('admin.wheel-prizes.store'), $payload)
            ->assertRedirect(route('admin.wheel-prizes.index'));

        $this->assertDatabaseHas('wheel_prizes', [
            'label' => 'Giải test đầy đủ',
            'label_ngan' => 'Test',
            'color' => '#abc',
            'weight' => 5,
            'sort_order' => 99,
        ]);
    }

    public function test_game_uses_database_prizes_after_seed(): void
    {
        WheelPrize::query()->delete();
        WheelPrize::factory()->create([
            'label' => 'Chỉ một giải',
            'label_ngan' => 'Một',
            'color' => '#111111',
            'weight' => 1,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('game'))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('wheelPrizes.0.label', 'Chỉ một giải')
                ->where('wheelPrizes.0.label_ngan', 'Một')
                ->where('wheelPrizes.0.color', '#111111'));
    }
}
