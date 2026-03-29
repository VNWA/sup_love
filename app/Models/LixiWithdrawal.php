<?php

namespace App\Models;

use App\Enums\LixiWithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LixiWithdrawal extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'status',
        'point_transaction_id',
        'refund_point_transaction_id',
        'admin_note',
        'processed_by',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LixiWithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<PointTransaction, $this>
     */
    public function deductionTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class, 'point_transaction_id');
    }

    /**
     * @return BelongsTo<PointTransaction, $this>
     */
    public function refundTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class, 'refund_point_transaction_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
