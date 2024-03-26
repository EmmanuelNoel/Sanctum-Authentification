<?php

use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value)->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value)->get('/user', [UserController::class, 'index']);

Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value)->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);
});

Route::resource('user', UserController::class)->middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value);

Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value)->group(function () {
    Route::get('/refresh-token', [AuthController::class, 'refreshToken']);
});
