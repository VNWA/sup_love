<?php

use App\Http\Controllers\Account\AccountHubController;
use App\Http\Controllers\Account\HistoryController;
use App\Http\Controllers\Account\PrizeWinsController;
use App\Http\Controllers\Game\GamePageController;
use App\Http\Controllers\Game\SpinWheelController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::inertia('/', 'Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ])->name('home');

    Route::get('game', GamePageController::class)->name('game');
    Route::post('game/spin', [SpinWheelController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('game.spin');

    Route::prefix('account')->name('account.')->group(function (): void {
        Route::get('/', AccountHubController::class)->name('index');
        Route::get('history', HistoryController::class)->name('history');
        Route::get('prize-wins', PrizeWinsController::class)->name('prize-wins');
    });

    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
