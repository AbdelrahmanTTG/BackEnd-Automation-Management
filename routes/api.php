<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;

use App\Http\Middleware\EncryptionMiddleware;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowExecutionController;

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
    Route::prefix('workflows')->group(function () {
        Route::get('/', [WorkflowController::class, 'index']);
        Route::post('/', [WorkflowController::class, 'store']);
        Route::get('/{id}', [WorkflowController::class, 'show']);
        Route::put('/{id}', [WorkflowController::class, 'update']);
        Route::delete('/{id}', [WorkflowController::class, 'destroy']);
        Route::post('/{id}/duplicate', [WorkflowController::class, 'duplicate']);
        Route::patch('/{id}/status', [WorkflowController::class, 'updateStatus']);

        // Execution routes
        Route::prefix('{workflowId}/executions')->group(function () {
            Route::get('/', [WorkflowExecutionController::class, 'index']);
            Route::post('/', [WorkflowExecutionController::class, 'store']);
            Route::get('/{executionId}', [WorkflowExecutionController::class, 'show']);
            Route::put('/{executionId}', [WorkflowExecutionController::class, 'update']);
            Route::delete('/{executionId}', [WorkflowExecutionController::class, 'destroy']);
            // Route::post('/{executionId}/pause', [WorkflowExecutionController::class, 'pause']);
            // Route::post('/{executionId}/resume', [WorkflowExecutionController::class, 'resume']);
            Route::post('/{executionId}/start', [WorkflowExecutionController::class, 'start']);
            Route::post('/{executionId}/stop', [WorkflowExecutionController::class, 'stop']);
        });
    });
});
Route::middleware(['auth:api'])->group(function () {
    Route::get('/perm', [AuthController::class, 'routes']);
});
