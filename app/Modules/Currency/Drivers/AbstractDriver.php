<?php

namespace App\Modules\Currency\Drivers;

use App\Modules\Currency\Contracts\ExchangeRateDriverInterface;
use Illuminate\Support\Facades\Cache;

abstract class AbstractDriver implements ExchangeRateDriverInterface
{
    protected string $cacheKey;
    
    // Template method
    public function getRate(string $from, string $to): float
    {
        $rates = $this->getCachedRates();
        return $rates["{$from}_{$to}"] ?? 0.0;
    }
    
    // Helper method used by template method
    protected function getCachedRates(): array
    {
        return Cache::remember($this->cacheKey, config('currency.cache_ttl', 300), function () {
            return $this->fetchRates();
        });
    }
    
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
    
    // Hook method to be implemented by concrete classes
    abstract public function fetchRates(): array;
}
