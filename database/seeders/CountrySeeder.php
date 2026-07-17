<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $endpoints = [
            "https://restcountries.com/v3.1/all?fields=name,cca2,cca3,capital,region,subregion,population,latlng,currencies,flag,area,languages",
            "https://restcountries.com/v3/all?fields=name,cca2,cca3,capital,region,subregion,population,latlng,currencies,flag,area,languages",
        ];

        $countries = null;
        foreach ($endpoints as $url) {
            $response = Http::timeout(30)->withOptions(["verify" => false])->get($url);
            if ($response->successful()) {
                $body = $response->json();
                if (is_array($body) && isset($body[0]["cca3"])) {
                    $countries = $body;
                    break;
                }
            }
        }

        if (!$countries) {
            $this->command->warn("REST Countries API unavailable. Seeding from local fallback data...");
            $this->seedFallback();
            return;
        }

        $inserted = 0;
        foreach ($countries as $c) {
            if (empty($c["cca3"])) continue;

            $currencies     = $c["currencies"] ?? [];
            $currencyCode   = array_key_first($currencies);
            $currencyName   = $currencies[$currencyCode]["name"] ?? null;
            $currencySymbol = $currencies[$currencyCode]["symbol"] ?? null;
            $lat            = $c["latlng"][0] ?? null;
            $lng            = $c["latlng"][1] ?? null;
            $langArr        = $c["languages"] ?? [];
            $languagesStr   = is_array($langArr) ? implode(", ", $langArr) : null;

            Country::updateOrCreate(
                ["iso3" => $c["cca3"]],
                [
                    "name"            => $c["name"]["common"],
                    "iso2"            => $c["cca2"] ?? null,
                    "capital"         => $c["capital"][0] ?? null,
                    "region"          => $c["region"] ?? null,
                    "subregion"       => $c["subregion"] ?? null,
                    "population"      => $c["population"] ?? null,
                    "latitude"        => $lat,
                    "longitude"       => $lng,
                    "currency_code"   => $currencyCode,
                    "currency_name"   => $currencyName,
                    "currency_symbol" => $currencySymbol,
                    "flag_emoji"      => $c["flag"] ?? null,
                    "area"            => $c["area"] ?? null,
                    "languages"       => $languagesStr,
                ]
            );
            $inserted++;
        }
        $this->command->info("Inserted/Updated {$inserted} countries.");
    }

    private function seedFallback(): void
    {
        $jsonPath = database_path("seeders/raw_countries.json");
        if (!file_exists($jsonPath)) return;

        $countries = json_decode(file_get_contents($jsonPath), true);
        if (!$countries) return;

        $inserted = 0;
        foreach ($countries as $c) {
            if (empty($c["cca3"])) continue;
            
            $currencyCode = null;
            if (!empty($c["currencies"])) {
                $currencyCode = is_array($c["currencies"]) ? (isset($c["currencies"][0]) ? $c["currencies"][0] : array_key_first($c["currencies"])) : null;
            }

            $lat = $c["latlng"][0] ?? null;
            $lng = $c["latlng"][1] ?? null;
            
            $languagesStr = null;
            if (!empty($c["languages"])) {
                $languagesStr = is_array($c["languages"]) ? implode(", ", array_values($c["languages"])) : $c["languages"];
            }
            
            $capital = null;
            if (!empty($c["capital"])) {
                $capital = is_array($c["capital"]) ? $c["capital"][0] : $c["capital"];
            }
            
            Country::updateOrCreate(
                ["iso3" => $c["cca3"]],
                [
                    "name"            => $c["name"]["common"] ?? ($c["name"] ?? null),
                    "iso2"            => $c["cca2"] ?? null,
                    "capital"         => $capital,
                    "region"          => $c["region"] ?? null,
                    "subregion"       => $c["subregion"] ?? null,
                    "latitude"        => $lat,
                    "longitude"       => $lng,
                    "currency_code"   => $currencyCode,
                    "area"            => $c["area"] ?? null,
                    "languages"       => $languagesStr,
                ]
            );
            $inserted++;
        }
        $this->command->info("Seeded {$inserted} countries from raw_countries.json.");
    }
}
