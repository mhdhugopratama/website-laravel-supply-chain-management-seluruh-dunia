<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WorldBankService
{
    public function getEconomicData(string $iso2): array
    {
        $cacheKey = "worldbank_{$iso2}";
        return Cache::remember($cacheKey, 3600 * 12, function () use ($iso2) {
            $gdp  = $this->fetchIndicator($iso2, 'NY.GDP.MKTP.CD');
            $infl = $this->fetchIndicator($iso2, 'FP.CPI.TOTL.ZG');
            $pop  = $this->fetchIndicator($iso2, 'SP.POP.TOTL');
            $exp  = $this->fetchIndicator($iso2, 'NE.EXP.GNFS.CD');
            $imp  = $this->fetchIndicator($iso2, 'NE.IMP.GNFS.CD');
            return [
                'gdp'        => $gdp,
                'inflation'  => $infl,
                'population' => $pop,
                'exports'    => $exp,
                'imports'    => $imp,
            ];
        });
    }

    private function fetchIndicator(string $iso2, string $indicator): ?float
    {
        $url = "https://api.worldbank.org/v2/country/{$iso2}/indicator/{$indicator}";
        try {
            $response = Http::timeout(3)
                ->withOptions(['verify' => false])
                ->get($url, [
                    'format'   => 'json',
                    'mrv'      => 5,
                    'per_page' => 5,
                ]);

            if ($response->failed()) return null;

            $data = $response->json();
            if (!isset($data[1]) || empty($data[1])) return null;

            foreach ($data[1] as $entry) {
                if (!is_null($entry['value'])) return (float) $entry['value'];
            }
        } catch (\Exception $e) {
            // Log or ignore timeout/connection issues to prevent page crash
            logger()->warning("WorldBank API error for {$iso2}/{$indicator}: " . $e->getMessage());
        }
        return null;
    }

    public function inflationRiskScore(?float $inflation): float
    {
        if (is_null($inflation)) return 30;
        if ($inflation < 2)  return 10;
        if ($inflation < 5)  return 25;
        if ($inflation < 10) return 50;
        if ($inflation < 20) return 75;
        return 100;
    }
}
