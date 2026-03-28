<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WheelPrizeController;
use App\Http\Controllers\Admin\WheelPrizeWinController;
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
        Route::resource('users', UserController::class)->except(['show']);

        Route::resource('wheel-prizes', WheelPrizeController::class)->except(['show']);

        Route::resource('wheel-prize-wins', WheelPrizeWinController::class)->only(['index', 'edit', 'update']);
    });
});
