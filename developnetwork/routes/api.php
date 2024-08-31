<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/verify', [UserController::class, 'verify']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tags', TagController::class);
    Route::get('posts/trashed', [PostController::class, 'trashed']);
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{id}/restore', [PostController::class, 'restore']);
    Route::get('/stats', [StatsController::class, 'index']);
});
