<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\JobStatusController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\UserController;
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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('user', UserController::class);
    Route::apiResource('role', RoleController::class);

    Route::apiResource('event', EventController::class);
    Route::post('/event/import', [EventController::class, 'import']);
    Route::post('/event/export', [EventController::class, 'export']);
    Route::apiResource('tiket', TiketController::class);
    Route::post('/tiket/import', [TiketController::class, 'import']);
    Route::post('/tiket/export', [TiketController::class, 'export']);
    Route::apiResource('peserta', PesertaController::class);
    Route::post('/peserta/import', [PesertaController::class, 'import']);
    Route::post('/peserta/export', [PesertaController::class, 'export']);
    Route::get('/check-queue-status/{id}', [JobStatusController::class, 'checkQueueStatus']);
});
