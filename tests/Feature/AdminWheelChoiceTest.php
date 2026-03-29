<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\WheelChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminWheelChoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        $this->seed(WheelChoiceSeeder::class);
    }

    public function test_non_admin_cannot_manage_wheel_choices(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.wheel-choices.index'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_create_wheel_choice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'name' => 'Thử nghiệm',
            'sort_order' => 99,
        ];

        $this->actingAs($admin)
            ->post(route('admin.wheel-choices.store'), $payload)
            ->assertRedirect(route('admin.wheel-choices.index'));

        $this->assertDatabaseHas('wheel_choices', [
            'name' => 'Thử nghiệm',
            'sort_order' => 99,
        ]);
    }

    public function test_admin_can_view_game_choices_on_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.wheel-choices.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/wheel-choices/Index')
                ->has('choices.data'));
    }
}
