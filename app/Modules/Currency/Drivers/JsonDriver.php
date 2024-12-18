<?php

namespace App\Modules\Currency\Drivers;

use Illuminate\Support\Facades\Http;

class JsonDriver extends AbstractDriver
{
    protected string $cacheKey = 'currency_rates_json';
    
    public function fetchRates(): array
    {
        try {
            $response = Http::acceptJson()->get(config('app.url') . '/api/rates/json');
            
            if (!$response->successful()) {
                throw new \RuntimeException('Failed to fetch JSON rates');
            }
            
            $data = $response->json();
            if (!isset($data['rates'])) {
                throw new \RuntimeException('Invalid JSON response format');
            }
            
            $rates = [];
            foreach ($data['rates'] as $rate) {
                $rates["{$rate['from']}_{$rate['to']}"] = (float)$rate['value'];
            }
            
            return $rates;
        } catch (\Exception $e) {
            \Log::error('JSON Driver error: ' . $e->getMessage());
            return [
                'USD_EUR' => 0.91,
                'EUR_USD' => 1.10
            ];
        }
    }
}
