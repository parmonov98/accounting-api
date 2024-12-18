<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Controllers\ExchangeRateController;

Route::prefix('rates')->group(function () {
    Route::get('xml', [ExchangeRateController::class, 'xml']);
    Route::get('json', [ExchangeRateController::class, 'json']);
    Route::get('csv', [ExchangeRateController::class, 'csv']);
});
