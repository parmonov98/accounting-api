<?php

namespace App\Modules\Currency\Drivers;

use Illuminate\Support\Facades\Http;

class XmlDriver extends AbstractDriver
{
    protected string $cacheKey = 'currency_rates_xml';
    
    public function fetchRates(): array
    {
        try {
            $response = Http::acceptJson()->get(config('app.url') . '/api/rates/xml');
            
            if (!$response->successful()) {
                throw new \RuntimeException('Failed to fetch XML rates');
            }
            
            $xml = simplexml_load_string($response->body());
            if ($xml === false) {
                throw new \RuntimeException('Failed to parse XML response');
            }
            
            $rates = [];
            foreach ($xml->rate as $rate) {
                $from = (string)$rate->from;
                $to = (string)$rate->to;
                $rates["{$from}_{$to}"] = (float)$rate->value;
            }
            
            return $rates;
        } catch (\Exception $e) {
            \Log::error('XML Driver error: ' . $e->getMessage());
            return [
                'USD_EUR' => 0.92,
                'EUR_USD' => 1.09
            ];
        }
    }
}
