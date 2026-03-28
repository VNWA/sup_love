<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountHubController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('account/Index');
    }
}
