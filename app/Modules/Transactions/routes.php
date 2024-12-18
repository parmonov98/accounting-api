<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Transactions\Controllers\TransactionController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    // User balance endpoint
    Route::get('me/balance', [TransactionController::class, 'balance']);
    
    // Transaction routes
    Route::prefix('transactions')->group(function () {
        // List and Create
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        
        // Summary endpoint
        Route::get('/summary', [TransactionController::class, 'summary']);
        
        // Resource operations with ID validation
        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('/', [TransactionController::class, 'show']);
            Route::put('/', [TransactionController::class, 'update']);
            Route::delete('/', [TransactionController::class, 'destroy']);
        });
    });
});
