<?php

namespace App\Enums;

enum PointTransactionType: string
{
    case WheelSpin = 'wheel_spin';
    case AdminCredit = 'admin_credit';
}
