<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderBalanceController;
use App\Http\Controllers\TokenUsageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Agents
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');

    // Token Usages
    Route::get('/token-usages', [TokenUsageController::class, 'index'])->name('token-usages.index');
    Route::get('/token-usages/create', [TokenUsageController::class, 'create'])->name('token-usages.create');
    Route::post('/token-usages', [TokenUsageController::class, 'store'])->name('token-usages.store');
    Route::get('/token-usages/{tokenUsage}/edit', [TokenUsageController::class, 'edit'])->name('token-usages.edit');
    Route::put('/token-usages/{tokenUsage}', [TokenUsageController::class, 'update'])->name('token-usages.update');
    Route::delete('/token-usages/{tokenUsage}', [TokenUsageController::class, 'destroy'])->name('token-usages.destroy');

    // Provider Balances
    Route::get('/provider-balances', [ProviderBalanceController::class, 'index'])->name('provider-balances.index');
    Route::get('/provider-balances/create', [ProviderBalanceController::class, 'create'])->name('provider-balances.create');
    Route::post('/provider-balances', [ProviderBalanceController::class, 'store'])->name('provider-balances.store');
    Route::get('/provider-balances/{providerBalance}/edit', [ProviderBalanceController::class, 'edit'])->name('provider-balances.edit');
    Route::put('/provider-balances/{providerBalance}', [ProviderBalanceController::class, 'update'])->name('provider-balances.update');
    Route::delete('/provider-balances/{providerBalance}', [ProviderBalanceController::class, 'destroy'])->name('provider-balances.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
