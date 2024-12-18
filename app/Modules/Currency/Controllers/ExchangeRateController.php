<?php

namespace App\Modules\Currency\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExchangeRateController
{
    private array $rates = [
        'USD_EUR' => 0.92,
        'EUR_USD' => 1.09
    ];

    public function xml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rates></rates>');
        
        foreach ($this->rates as $pair => $rate) {
            list($from, $to) = explode('_', $pair);
            $rateElement = $xml->addChild('rate');
            $rateElement->addChild('from', $from);
            $rateElement->addChild('to', $to);
            $rateElement->addChild('value', $rate);
        }
        
        return Response::make($xml->asXML(), 200, ['Content-Type' => 'application/xml']);
    }

    public function json()
    {
        $data = [];
        foreach ($this->rates as $pair => $rate) {
            list($from, $to) = explode('_', $pair);
            $data['rates'][] = [
                'from' => $from,
                'to' => $to,
                'value' => $rate
            ];
        }
        
        return response()->json($data);
    }

    public function csv()
    {
        $output = '';
        foreach ($this->rates as $pair => $rate) {
            list($from, $to) = explode('_', $pair);
            $output .= "{$from},{$to},{$rate}\n";
        }
        
        return Response::make($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rates.csv"'
        ]);
    }
}
