<?php

namespace App\Modules\Currency\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Currency\Services\ExchangeRateFactory;

class UpdateExchangeRates extends Command
{
    protected $signature = 'currency:update-rates';
    protected $description = 'Update cached exchange rates';
    
    public function handle(ExchangeRateFactory $factory): void
    {
        $drivers = ['xml', 'json', 'csv', 'average'];
        
        foreach ($drivers as $driverName) {
            $driver = $factory->driver($driverName);
            $driver->clearCache();
            $driver->getRate('USD', 'EUR'); // This will trigger cache update
            $this->info("Updated rates for {$driverName} driver");
        }
    }
}
