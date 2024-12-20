<?php

namespace App\Modules\Currency\Drivers;

class AverageDriver extends AbstractDriver
{
    //TODO: to enum
    protected string $cacheKey = 'currency_rates_average';
    protected array $drivers;

    public function __construct(array $drivers)
    {
        $this->drivers = $drivers;
    }

    public function fetchRates(): array
    {
        $allRates = [];
        foreach ($this->drivers as $driver) {
            $rates = $driver->fetchRates();
            foreach ($rates as $key => $rate) {
                if (!isset($allRates[$key])) {
                    $allRates[$key] = [];
                }
                $allRates[$key][] = $rate;
            }
        }

        $averageRates = [];
        foreach ($allRates as $key => $rates) {
            $averageRates[$key] = array_sum($rates) / count($rates);
        }

        return $averageRates;
    }
}
