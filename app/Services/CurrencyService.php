<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    private string $baseUrl = 'https://open.er-api.com/v6/latest/USD';

    public function getRates(): array
    {
        return Cache::remember('exchange_rates_usd', 3600, function () {
            $response = Http::timeout(10)->withOptions(['verify' => false])->get($this->baseUrl);
            if ($response->failed()) return [];
            return $response->json('rates', []);
        });
    }

    public function convert(string $from, string $to, float $amount): array
    {
        $rates = $this->getRates();
        if (empty($rates) || !isset($rates[$from]) || !isset($rates[$to])) {
            return ['error' => true, 'result' => 0];
        }

        $usdAmount = $amount / $rates[$from];
        $result    = $usdAmount * $rates[$to];
        return [
            'from'   => $from,
            'to'     => $to,
            'amount' => $amount,
            'result' => round($result, 6),
            'rate'   => round($rates[$to] / $rates[$from], 6),
        ];
    }

    public function currencyRiskScore(string $currencyCode): float
    {
        $rates    = $this->getRates();
        $riskyCurrencies = ['VES', 'ZWL', 'SLL', 'IQD', 'SDG', 'SOS', 'MZN', 'GNF', 'HTG'];
        if (in_array($currencyCode, $riskyCurrencies)) return 80;
        $stableCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CHF', 'SGD', 'AUD', 'CAD'];
        if (in_array($currencyCode, $stableCurrencies)) return 10;
        return 35;
    }
}
