<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RestCountriesService
{
    public function getCountryData(string $iso3): array
    {
        $cacheKey = "restcountries_{$iso3}";

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
        }

        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => false])
                ->get("https://restcountries.com/v3.1/alpha/{$iso3}");

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && is_array($data)) {
                    $country = $data[0];

                    // Parse languages
                    $languages = isset($country['languages']) ? implode(', ', array_values($country['languages'])) : null;

                    // Parse currency
                    $currencyCode = null;
                    $currencyName = null;
                    $currencySymbol = null;
                    if (isset($country['currencies']) && is_array($country['currencies'])) {
                        $currencyCode = array_key_first($country['currencies']);
                        $currencyName = $country['currencies'][$currencyCode]['name'] ?? null;
                        $currencySymbol = $country['currencies'][$currencyCode]['symbol'] ?? null;
                    }

                    $parsed = [
                        'name'            => $country['name']['common'] ?? null,
                        'capital'         => isset($country['capital']) ? implode(', ', $country['capital']) : null,
                        'region'          => $country['region'] ?? null,
                        'subregion'       => $country['subregion'] ?? null,
                        'languages'       => $languages,
                        'latitude'        => $country['latlng'][0] ?? null,
                        'longitude'       => $country['latlng'][1] ?? null,
                        'area'            => $country['area'] ?? null,
                        'population'      => $country['population'] ?? null,
                        'currency_code'   => $currencyCode,
                        'currency_name'   => $currencyName,
                        'currency_symbol' => $currencySymbol,
                    ];

                    Cache::put($cacheKey, $parsed, 86400);
                    return $parsed;
                }
            }
        } catch (\Exception $e) {
            logger()->warning("REST Countries API error for {$iso3}: " . $e->getMessage());
        }

        return [];
    }
}
