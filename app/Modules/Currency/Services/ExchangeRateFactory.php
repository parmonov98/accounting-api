<?php

namespace App\Modules\Currency\Services;

use App\Modules\Currency\Contracts\ExchangeRateDriverInterface;
use App\Modules\Currency\Drivers\XmlDriver;
use App\Modules\Currency\Drivers\JsonDriver;
use App\Modules\Currency\Drivers\CsvDriver;
use App\Modules\Currency\Drivers\AverageDriver;

class ExchangeRateFactory
{
    public function driver(?string $driver = null): ExchangeRateDriverInterface
    {
        $driver = $driver ?? config('currency.default_driver', 'average');
        
        return match($driver) {
            'xml' => new XmlDriver(),
            'json' => new JsonDriver(),
            'csv' => new CsvDriver(),
            'average' => new AverageDriver([
                new XmlDriver(),
                new JsonDriver(),
                new CsvDriver(),
            ]),
            default => throw new \InvalidArgumentException("Driver [{$driver}] not supported."),
        };
    }

    public function format(float $amount, string $fromCurrency = 'EUR', string $toCurrency = 'USD'): array
    {
        $rate = $this->driver()->getRate($fromCurrency, $toCurrency);
        
        return [
            $fromCurrency => number_format($amount, 2),
            $toCurrency => number_format($amount * $rate, 2)
        ];
    }
}
