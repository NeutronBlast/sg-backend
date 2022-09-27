<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register', [RegisteredUserController::class, 'register']);
Route::post('login', [AuthenticatedSessionController::class, 'login']);
Route::post('logout', [RegisteredUserController::class, 'logout'])->name('logout');

Route::resource('users', \App\Http\Controllers\UserController::class);
Route::get('report', [\App\Http\Controllers\UserController::class, 'showNumberOfParticipants']);
Route::put('users/disable/{id}', [\App\Http\Controllers\UserController::class, 'changeStatus']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
