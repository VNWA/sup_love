<?php

namespace Tests\Feature;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserPointsDebitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
    }

    public function test_non_admin_cannot_debit_points(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create(['point' => 50]);

        $this->actingAs($user)
            ->post(route('admin.users.points.debit', $target), [
                'amount' => 10,
                'note' => 'Should not apply',
            ])
            ->assertRedirect(route('home'));

        $target->refresh();
        $this->assertSame(50, $target->point);
    }

    public function test_admin_can_debit_user_points(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $player = User::factory()->create(['point' => 100]);

        $this->actingAs($admin)
            ->from(route('admin.users.points', $player))
            ->post(route('admin.users.points.debit', $player), [
                'amount' => 25,
                'note' => 'Điều chỉnh theo yêu cầu',
            ])
            ->assertRedirect(route('admin.users.points', $player));

        $player->refresh();
        $this->assertSame(75, $player->point);

        $tx = PointTransaction::query()
            ->where('user_id', $player->getKey())
            ->where('type', PointTransactionType::AdminDebit)
            ->latest('id')
            ->first();

        $this->assertNotNull($tx);
        $this->assertSame(-25, $tx->amount);
        $this->assertSame(75, $tx->balance_after);
        $this->assertSame($admin->getKey(), $tx->actor_id);
        $this->assertSame('Điều chỉnh theo yêu cầu', $tx->admin_note);
    }

    public function test_admin_debit_fails_when_balance_insufficient(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $player = User::factory()->create(['point' => 5]);

        $this->actingAs($admin)
            ->from(route('admin.users.points', $player))
            ->post(route('admin.users.points.debit', $player), [
                'amount' => 20,
                'note' => 'Too large debit',
            ])
            ->assertSessionHasErrors('amount');

        $player->refresh();
        $this->assertSame(5, $player->point);

        $this->assertSame(
            0,
            PointTransaction::query()
                ->where('user_id', $player->getKey())
                ->where('type', PointTransactionType::AdminDebit)
                ->count()
        );
    }
}
