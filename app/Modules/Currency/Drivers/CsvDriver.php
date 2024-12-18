<?php

namespace App\Modules\Currency\Drivers;

use Illuminate\Support\Facades\Http;

class CsvDriver extends AbstractDriver
{
    protected string $cacheKey = 'currency_rates_csv';
    
    public function fetchRates(): array
    {
        try {
            $response = Http::accept('text/csv')->get(config('app.url') . '/api/rates/csv');
            
            if (!$response->successful()) {
                throw new \RuntimeException('Failed to fetch CSV rates');
            }
            
            $rates = [];
            $lines = explode("\n", trim($response->body()));
            
            foreach ($lines as $line) {
                $data = str_getcsv($line);
                if (count($data) === 3) {
                    $rates["{$data[0]}_{$data[1]}"] = (float)$data[2];
                }
            }
            
            return $rates;
        } catch (\Exception $e) {
            \Log::error('CSV Driver error: ' . $e->getMessage());
            return [
                'USD_EUR' => 0.93,
                'EUR_USD' => 1.08
            ];
        }
    }
}
