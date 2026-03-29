<?php

namespace Tests\Feature;

use App\Enums\LixiWithdrawalStatus;
use App\Models\LixiWithdrawal;
use App\Models\User;
use Database\Seeders\WheelChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountBankAndLixiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function sampleBankProfile(): array
    {
        return [
            'bank_name' => 'Vietcombank',
            'bank_account_number' => '0123456789',
            'bank_account_holder' => 'NGUYEN VAN A',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WheelChoiceSeeder::class);
    }

    public function test_user_can_update_bank_info(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.bank.update'), [
                'bank_name' => 'Vietcombank',
                'bank_account_number' => '0123456789',
                'bank_account_holder' => 'NGUYEN VAN A',
            ])
            ->assertRedirect(route('account.bank'));

        $user->refresh();
        $this->assertSame('Vietcombank', $user->bank_name);
        $this->assertSame('0123456789', $user->bank_account_number);
        $this->assertSame('NGUYEN VAN A', $user->bank_account_holder);
    }

    public function test_lixi_withdrawal_requires_complete_bank_profile(): void
    {
        $user = User::factory()->create([
            'point' => 100,
            'bank_name' => 'VCB',
            'bank_account_number' => '',
            'bank_account_holder' => 'A',
        ]);

        $this->actingAs($user)
            ->post(route('account.lixi-withdrawals.store'), [
                'amount' => 10,
            ])
            ->assertSessionHasErrors('bank');

        $this->assertSame(0, LixiWithdrawal::query()->count());
        $user->refresh();
        $this->assertSame(100, $user->point);
    }

    public function test_user_can_request_lixi_withdrawal_and_points_deducted(): void
    {
        $user = User::factory()->create([
            'point' => 100,
            ...$this->sampleBankProfile(),
        ]);

        $this->actingAs($user)
            ->post(route('account.lixi-withdrawals.store'), [
                'amount' => 40,
            ])
            ->assertRedirect(route('account.lixi-withdrawals.index'));

        $user->refresh();
        $this->assertSame(60, $user->point);

        $w = LixiWithdrawal::query()->where('user_id', $user->getKey())->first();
        $this->assertNotNull($w);
        $this->assertSame(40, $w->amount);
        $this->assertSame(LixiWithdrawalStatus::Pending, $w->status);
        $this->assertNotNull($w->point_transaction_id);
        $this->assertSame('Vietcombank', $w->bank_name);
        $this->assertSame('0123456789', $w->bank_account_number);
        $this->assertSame('NGUYEN VAN A', $w->bank_account_holder);
    }

    public function test_admin_can_approve_lixi_withdrawal(): void
    {
        Role::findOrCreate('admin', 'web');

        $user = User::factory()->create([
            'point' => 50,
            ...$this->sampleBankProfile(),
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($user)
            ->post(route('account.lixi-withdrawals.store'), ['amount' => 20])
            ->assertRedirect(route('account.lixi-withdrawals.index'));

        $w = LixiWithdrawal::query()->where('user_id', $user->getKey())->firstOrFail();
        $this->assertSame(LixiWithdrawalStatus::Pending, $w->status);

        $user->refresh();
        $this->assertSame(30, $user->point);

        $this->actingAs($admin)
            ->patch(route('admin.lixi-withdrawals.update', $w), [
                'status' => 'success',
            ])
            ->assertRedirect(route('admin.lixi-withdrawals.index'));

        $w->refresh();
        $this->assertSame(LixiWithdrawalStatus::Success, $w->status);
        $user->refresh();
        $this->assertSame(30, $user->point);
    }

    public function test_admin_can_reject_lixi_withdrawal_and_refund_points(): void
    {
        Role::findOrCreate('admin', 'web');

        $user = User::factory()->create([
            'point' => 50,
            ...$this->sampleBankProfile(),
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($user)
            ->post(route('account.lixi-withdrawals.store'), ['amount' => 20])
            ->assertRedirect(route('account.lixi-withdrawals.index'));

        $w = LixiWithdrawal::query()->where('user_id', $user->getKey())->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.lixi-withdrawals.update', $w), [
                'status' => 'failed',
                'admin_note' => 'Sai thông tin NH',
            ])
            ->assertRedirect(route('admin.lixi-withdrawals.index'));

        $w->refresh();
        $this->assertSame(LixiWithdrawalStatus::Failed, $w->status);
        $this->assertNotNull($w->refund_point_transaction_id);

        $user->refresh();
        $this->assertSame(50, $user->point);
    }
}
