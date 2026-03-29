<?php

namespace App\Enums;

enum PointTransactionType: string
{
    case WheelSpin = 'wheel_spin';
    case AdminCredit = 'admin_credit';
    case AdminDebit = 'admin_debit';
    case LixiWithdrawalRequest = 'lixi_withdrawal_request';
    case LixiWithdrawalRefund = 'lixi_withdrawal_refund';
}
