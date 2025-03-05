<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::group(
    ["prefix" => "auth"],
    function () {
        Route::group(["prefix" => "github"], function () {
            Route::get("/", [AuthController::class, "redirectToGithub"]);
            Route::get("/callback", [AuthController::class, "handleGithubCallback"]);
        });
    }
);
