<?php

namespace App\Modules\Currency;

use Illuminate\Support\ServiceProvider;
use App\Modules\Currency\Services\ExchangeRateFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Console\Commands\UpdateExchangeRates;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('currency', function ($app) {
            return new ExchangeRateFactory();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateExchangeRates::class,
            ]);
        }
    }
    
    public function boot(): void
    {
        // Register routes
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/routes.php');

        $this->publishes([
            __DIR__ . '/Config/currency.php' => config_path('currency.php'),
        ], 'currency');
        
        // Register the helper file
        require_once __DIR__ . '/Helpers/currency.php';
    }
}
