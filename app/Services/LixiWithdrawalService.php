<?php

namespace App\Services;

use App\Enums\LixiWithdrawalStatus;
use App\Enums\PointTransactionType;
use App\Models\LixiWithdrawal;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LixiWithdrawalService
{
    /**
     * Trừ điểm và tạo yêu cầu rút ở trạng thái chờ duyệt.
     */
    public function createRequest(User $user, int $amount): LixiWithdrawal
    {
        if ($amount < 1) {
            throw ValidationException::withMessages([
                'amount' => ['Số điểm rút phải ít nhất 1.'],
            ]);
        }

        return DB::transaction(function () use ($user, $amount) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->hasCompleteBankProfile()) {
                throw ValidationException::withMessages([
                    'bank' => ['Vui lòng cập nhật đầy đủ thông tin ngân hàng nhận lì xì trước khi rút.'],
                ]);
            }

            if ($locked->point < $amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Bạn không đủ điểm để rút.'],
                ]);
            }

            $withdrawal = LixiWithdrawal::query()->create([
                'user_id' => $locked->getKey(),
                'amount' => $amount,
                'bank_name' => trim((string) $locked->bank_name),
                'bank_account_number' => trim((string) $locked->bank_account_number),
                'bank_account_holder' => trim((string) $locked->bank_account_holder),
                'status' => LixiWithdrawalStatus::Pending,
                'point_transaction_id' => null,
            ]);

            $locked->point = (int) $locked->point - $amount;
            $locked->save();

            $transaction = PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => null,
                'type' => PointTransactionType::LixiWithdrawalRequest,
                'amount' => -$amount,
                'balance_after' => max(0, (int) $locked->point),
                'admin_note' => null,
                'meta' => [
                    'lixi_withdrawal_id' => (int) $withdrawal->getKey(),
                ],
            ]);

            $withdrawal->point_transaction_id = $transaction->getKey();
            $withdrawal->save();

            return $withdrawal->fresh(['deductionTransaction']);
        });
    }

    /**
     * Admin đặt trạng thái; tự xử lý hoàn/trừ điểm theo chuyển trạng thái.
     */
    public function syncStatus(
        LixiWithdrawal $withdrawal,
        LixiWithdrawalStatus $newStatus,
        User $admin,
        ?string $adminNote = null,
    ): void {
        $withdrawal->refresh();
        $old = $withdrawal->status;

        if ($old === $newStatus) {
            return;
        }

        match ([$old, $newStatus]) {
            [LixiWithdrawalStatus::Pending, LixiWithdrawalStatus::Success] => $this->markSuccess($withdrawal, $admin),
            [LixiWithdrawalStatus::Pending, LixiWithdrawalStatus::Failed] => $this->markFailed($withdrawal, $admin, $adminNote),
            [LixiWithdrawalStatus::Success, LixiWithdrawalStatus::Failed] => $this->successToFailed($withdrawal, $admin, $adminNote),
            [LixiWithdrawalStatus::Success, LixiWithdrawalStatus::Pending] => $this->successToPending($withdrawal),
            [LixiWithdrawalStatus::Failed, LixiWithdrawalStatus::Success] => $this->failedToSuccess($withdrawal, $admin),
            [LixiWithdrawalStatus::Failed, LixiWithdrawalStatus::Pending] => $this->failedToPending($withdrawal, $admin),
            default => throw ValidationException::withMessages([
                'status' => ['Không thể chuyển từ «'.$old->value.'» sang «'.$newStatus->value.'».'],
            ]),
        };
    }

    /**
     * Admin duyệt: đã chuyển khoản — không hoàn điểm.
     */
    public function markSuccess(LixiWithdrawal $withdrawal, User $admin): void
    {
        if ($withdrawal->status !== LixiWithdrawalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => ['Chỉ duyệt được khi đang chờ.'],
            ]);
        }

        $withdrawal->status = LixiWithdrawalStatus::Success;
        $withdrawal->processed_by = $admin->getKey();
        $withdrawal->processed_at = now();
        $withdrawal->save();
    }

    /**
     * Admin từ chối: hoàn điểm cho thành viên.
     */
    public function markFailed(LixiWithdrawal $withdrawal, User $admin, ?string $note = null): void
    {
        if ($withdrawal->status !== LixiWithdrawalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => ['Chỉ từ chối được khi đang chờ.'],
            ]);
        }

        DB::transaction(function () use ($withdrawal, $admin, $note) {
            /** @var User $locked */
            $locked = User::query()->whereKey($withdrawal->user_id)->lockForUpdate()->firstOrFail();

            $amount = (int) $withdrawal->amount;
            $locked->point = (int) $locked->point + $amount;
            $locked->save();

            $refund = PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $admin->getKey(),
                'type' => PointTransactionType::LixiWithdrawalRefund,
                'amount' => $amount,
                'balance_after' => (int) $locked->point,
                'admin_note' => $note,
                'meta' => [
                    'lixi_withdrawal_id' => (int) $withdrawal->getKey(),
                ],
            ]);

            $withdrawal->status = LixiWithdrawalStatus::Failed;
            $withdrawal->refund_point_transaction_id = $refund->getKey();
            $withdrawal->admin_note = $note;
            $withdrawal->processed_by = $admin->getKey();
            $withdrawal->processed_at = now();
            $withdrawal->save();
        });
    }

    /**
     * Đã thành công → thất bại: hoàn điểm (trường hợp nhập nhầm / thu hồi).
     */
    private function successToFailed(LixiWithdrawal $withdrawal, User $admin, ?string $note): void
    {
        DB::transaction(function () use ($withdrawal, $admin, $note) {
            /** @var User $locked */
            $locked = User::query()->whereKey($withdrawal->user_id)->lockForUpdate()->firstOrFail();

            $amount = (int) $withdrawal->amount;
            $locked->point = (int) $locked->point + $amount;
            $locked->save();

            $refund = PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $admin->getKey(),
                'type' => PointTransactionType::LixiWithdrawalRefund,
                'amount' => $amount,
                'balance_after' => (int) $locked->point,
                'admin_note' => $note,
                'meta' => [
                    'lixi_withdrawal_id' => (int) $withdrawal->getKey(),
                    'reason' => 'admin_status_change_success_to_failed',
                ],
            ]);

            $withdrawal->status = LixiWithdrawalStatus::Failed;
            $withdrawal->refund_point_transaction_id = $refund->getKey();
            $withdrawal->admin_note = $note;
            $withdrawal->processed_by = $admin->getKey();
            $withdrawal->processed_at = now();
            $withdrawal->save();
        });
    }

    /**
     * Thành công → chờ lại: mở lại xét duyệt (điểm vẫn đang bị giữ).
     */
    private function successToPending(LixiWithdrawal $withdrawal): void
    {
        $withdrawal->status = LixiWithdrawalStatus::Pending;
        $withdrawal->processed_by = null;
        $withdrawal->processed_at = null;
        $withdrawal->save();
    }

    /**
     * Thất bại → thành công: trừ lại điểm (đã hoàn trước đó).
     */
    private function failedToSuccess(LixiWithdrawal $withdrawal, User $admin): void
    {
        DB::transaction(function () use ($withdrawal, $admin) {
            /** @var User $locked */
            $locked = User::query()->whereKey($withdrawal->user_id)->lockForUpdate()->firstOrFail();

            $amount = (int) $withdrawal->amount;

            if ($locked->point < $amount) {
                throw ValidationException::withMessages([
                    'status' => ['Thành viên không đủ điểm để ghi nhận thành công (cần '.$amount.' điểm).'],
                ]);
            }

            $locked->point = (int) $locked->point - $amount;
            $locked->save();

            PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $admin->getKey(),
                'type' => PointTransactionType::LixiWithdrawalRequest,
                'amount' => -$amount,
                'balance_after' => (int) $locked->point,
                'admin_note' => null,
                'meta' => [
                    'lixi_withdrawal_id' => (int) $withdrawal->getKey(),
                    'reason' => 'admin_status_change_failed_to_success',
                ],
            ]);

            $withdrawal->status = LixiWithdrawalStatus::Success;
            $withdrawal->refund_point_transaction_id = null;
            $withdrawal->admin_note = null;
            $withdrawal->processed_by = $admin->getKey();
            $withdrawal->processed_at = now();
            $withdrawal->save();
        });
    }

    /**
     * Thất bại → chờ: trừ lại điểm và mở yêu cầu (sau khi đã hoàn).
     */
    private function failedToPending(LixiWithdrawal $withdrawal, User $admin): void
    {
        DB::transaction(function () use ($withdrawal, $admin) {
            /** @var User $locked */
            $locked = User::query()->whereKey($withdrawal->user_id)->lockForUpdate()->firstOrFail();

            $amount = (int) $withdrawal->amount;

            if ($locked->point < $amount) {
                throw ValidationException::withMessages([
                    'status' => ['Thành viên không đủ điểm để mở lại yêu cầu (cần '.$amount.' điểm).'],
                ]);
            }

            $locked->point = (int) $locked->point - $amount;
            $locked->save();

            PointTransaction::query()->create([
                'user_id' => $locked->getKey(),
                'actor_id' => $admin->getKey(),
                'type' => PointTransactionType::LixiWithdrawalRequest,
                'amount' => -$amount,
                'balance_after' => (int) $locked->point,
                'admin_note' => null,
                'meta' => [
                    'lixi_withdrawal_id' => (int) $withdrawal->getKey(),
                    'reason' => 'admin_status_change_failed_to_pending',
                ],
            ]);

            $withdrawal->status = LixiWithdrawalStatus::Pending;
            $withdrawal->refund_point_transaction_id = null;
            $withdrawal->admin_note = null;
            $withdrawal->processed_by = null;
            $withdrawal->processed_at = null;
            $withdrawal->save();
        });
    }
}
