<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public routes
Route::get('/unauthorized', function () {
    return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
})->name('unauthorized');

// Dashboard routes
Route::middleware(['auth:api'])->prefix('dashboard')->group(function () {
    Route::get('/repositories', [DashboardController::class, 'getRepositories'])->name('dashboard.repositories');
    Route::get('/repository', [DashboardController::class, 'getRepository'])->name('dashboard.repository');
    Route::post('/clear-repository-cache', [DashboardController::class, 'clearRepositoryCache'])->name('dashboard.clearRepositoryCache');
    Route::post('/pull-repository-code', [DashboardController::class, 'pullRepositoryCode'])->name('dashboard.pullRepositoryCode');
});
