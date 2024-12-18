<?php

namespace App\Modules\Currency\Contracts;

interface ExchangeRateDriverInterface
{
    public function getRate(string $from, string $to): float;
    public function fetchRates(): array;
    public function clearCache(): void;
}
