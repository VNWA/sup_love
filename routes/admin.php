<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LixiWithdrawalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WheelChoiceController;
use App\Http\Controllers\Admin\WheelRoomController;
use App\Http\Controllers\Admin\WheelRoundController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', function (Request $request): RedirectResponse {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        if (! $user->hasRole('admin')) {
            return redirect()->route('admin.login');
        }

        return redirect()->route('admin.users.index');
    })->name('index');

    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');

    Route::middleware(['auth', 'verified', 'role:admin'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('users/{user}/points', [UserController::class, 'points'])->name('users.points');
        Route::post('users/{user}/points', [UserController::class, 'credit'])->name('users.points.store');
        Route::post('users/{user}/points/debit', [UserController::class, 'debit'])->name('users.points.debit');
        Route::resource('users', UserController::class)->except(['show']);

        Route::resource('wheel-choices', WheelChoiceController::class)->except(['show']);
        Route::get('wheel-rooms/{wheelRoom}/rounds', [WheelRoundController::class, 'index'])
            ->name('wheel-rooms.rounds.index');
        Route::post('wheel-rooms/{wheelRoom}/rounds', [WheelRoundController::class, 'store'])
            ->name('wheel-rooms.rounds.store');
        Route::post('wheel-rooms/{wheelRoom}/rounds/{wheelRound}/end', [WheelRoundController::class, 'end'])
            ->name('wheel-rooms.rounds.end');
        Route::resource('wheel-rooms', WheelRoomController::class)->only(['index', 'edit', 'update']);

        Route::get('lixi-withdrawals', [LixiWithdrawalController::class, 'index'])->name('lixi-withdrawals.index');
        Route::patch('lixi-withdrawals/{lixiWithdrawal}', [LixiWithdrawalController::class, 'update'])->name('lixi-withdrawals.update');
    });
});
