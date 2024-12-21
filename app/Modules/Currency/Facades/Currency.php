<?php

namespace App\Modules\Currency\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Modules\Currency\Contracts\ExchangeRateDriverInterface driver()
 * @method static array format(float $amount, string $fromCurrency = 'EUR', string $toCurrency = 'USD')
 */
class Currency extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'currency';
    }
}
