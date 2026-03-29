<?php

namespace App\Enums;

enum LixiWithdrawalStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
}
