<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\User\InfoController;
// Public routes - No auth needed
Route::get('/unauthorized', function () {
    return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
})->name('unauthorized');

Route::group(["prefix" => "api"], function () {
    Route::middleware(['auth:api'])->prefix('dashboard')->group(function () {
        Route::get('/repositories', [DashboardController::class, 'getRepositories'])->name('dashboard.repositories');
        Route::get('/repository', [DashboardController::class, 'getRepository'])->name('dashboard.repository');
        Route::post('/clear-repository-cache', [DashboardController::class, 'clearRepositoryCache'])->name('dashboard.clearRepositoryCache');
        Route::post('/pull-repository-code', [DashboardController::class, 'pullRepositoryCode'])->name('dashboard.pullRepositoryCode');
        Route::get('/repository/{owner}/{repo}/file', [DashboardController::class, 'getRepositoryFile'])
            ->name('dashboard.repository.file');
        Route::get('/repository/{owner}/{repo}/folder', [DashboardController::class, 'getRepositoryFolder'])
            ->name('dashboard.repository.folder');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/me', [InfoController::class, 'getUserInfo'])->name('user.info');
    })->middleware(['auth:api']);
});
