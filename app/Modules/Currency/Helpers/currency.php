<?php

if (!function_exists('currency_convert')) {
    function currency_convert(float $amount, string $from, string $to, ?string $driver = null): float
    {
        return app('currency')->driver($driver)->getRate($from, $to) * $amount;
    }
}
