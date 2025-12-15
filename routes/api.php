<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Middleware\EncryptionMiddleware;

Route::middleware([EncryptionMiddleware::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:api', EncryptionMiddleware::class])->group(function () {
    Route::post('/permission', [AuthController::class, 'userPermission']);
    Route::get('/perm', [AuthController::class, 'routes']);
    Route::post('/AddNewAutomation', [ConfigController::class, 'store']);
    Route::post('/UpdateAutomation', [ConfigController::class, 'update']);
    Route::post('/configs', [ConfigController::class, 'index']);
    Route::delete('/DeleteAutomation/{id}', [ConfigController::class, 'delete']);
});
Route::middleware(['auth:api'])->group(function () {
    Route::get('/perm', [AuthController::class, 'routes']);
});
