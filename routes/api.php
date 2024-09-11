<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('profile-update', [UserController::class, 'profileUpdate']);
    Route::post('me', [UserController::class, 'me']);
});