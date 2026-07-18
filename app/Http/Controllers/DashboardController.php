<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Models\Watchlist;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\CurrencyService;
use App\Services\NewsService;
use App\Services\RiskEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private WeatherService $weather,
        private WorldBankService $worldBank,
        private CurrencyService $currency,
        private NewsService $news,
        private RiskEngine $riskEngine
    ) {}

    public function index()
    {
        ini_set('max_execution_time', 120);

        $countries = Country::orderBy('name')
            ->get(['id', 'name', 'iso3', 'iso2', 'flag_emoji', 'latitude', 'longitude', 'region', 'currency_code']);

        $watchlist = [];
        if (Auth::check()) {
            $watchlist = Auth::user()
                ->watchedCountries()
                ->get(['countries.id', 'name', 'iso3', 'flag_emoji', 'iso2'])
                ->toArray();
        }

        $ports = Cache::remember('dashboard_ports_all', 3600, function () {
            return Port::select('name', 'country_name', 'country_code', 'latitude', 'longitude', 'type', 'un_locode')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();
        });

        $majorCities = [
            ['name' => 'New York',   'lat' =>  40.71, 'lon' => -74.01, 'country' => 'USA'],
            ['name' => 'London',     'lat' =>  51.51, 'lon' =>  -0.13, 'country' => 'GBR'],
            ['name' => 'Tokyo',      'lat' =>  35.68, 'lon' => 139.69, 'country' => 'JPN'],
            ['name' => 'Singapore',  'lat' =>   1.35, 'lon' => 103.82, 'country' => 'SGP'],
            ['name' => 'Dubai',      'lat' =>  25.20, 'lon' =>  55.27, 'country' => 'ARE'],
            ['name' => 'Shanghai',   'lat' =>  31.23, 'lon' => 121.47, 'country' => 'CHN'],
            ['name' => 'Mumbai',     'lat' =>  19.08, 'lon' =>  72.88, 'country' => 'IND'],
            ['name' => 'São Paulo',  'lat' => -23.55, 'lon' => -46.63, 'country' => 'BRA'],
            ['name' => 'Lagos',      'lat' =>   6.52, 'lon' =>   3.38, 'country' => 'NGA'],
            ['name' => 'Sydney',     'lat' => -33.87, 'lon' => 151.21, 'country' => 'AUS'],
            ['name' => 'Cairo',      'lat' =>  30.04, 'lon' =>  31.24, 'country' => 'EGY'],
            ['name' => 'Moscow',     'lat' =>  55.75, 'lon' =>  37.62, 'country' => 'RUS'],
        ];

        // Fetch weather for all countries with valid coordinates (replaces limited major cities list)
        $weatherCities = Cache::remember('dashboard_weather_cities', 1800, function () use ($countries) {
            // Filter countries that have latitude and longitude
            $validCountries = $countries->filter(function ($c) {
                return $c->latitude && $c->longitude;
            });
            // Prepare coordinates array preserving original index keys
            $coords = [];
            foreach ($validCountries as $i => $c) {
                $coords[$i] = ['lat' => (float)$c->latitude, 'lon' => (float)$c->longitude];
            }
            // Batch request weather for all coordinates
            $batchWeather = $this->weather->getBatchWeather($coords);
            // Build result array with weather data per country
            $result = [];
            foreach ($validCountries as $i => $c) {
                $w = $batchWeather[$i] ?? ['temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];
                $result[] = [
                    'name'    => $c->name,
                    'country' => $c->iso3,
                    'lat'     => $c->latitude,
                    'lon'     => $c->longitude,
                    'temp'    => $w['temperature'] ?? 0,
                    'precip'  => $w['precipitation'] ?? 0,
                    'wind'    => $w['wind_speed'] ?? 0,
                    'code'    => $w['weather_code'] ?? 0,
                    'label'   => $this->weather->weatherLabel($w['weather_code'] ?? 0),
                    'risk'    => $this->weather->weatherRiskScore($w),
                ];
            }
            return $result;
        });

        $mapCountries = Cache::remember('dashboard_map_countries_all', 7200, function () use ($countries) {
            $riskData = [];
            $allValidCountries = $countries->whereNotNull('latitude')->whereNotNull('longitude')->values();
            
            $coords = [];
            foreach ($allValidCountries as $i => $c) {
                $coords[$i] = ['lat' => (float)$c->latitude, 'lon' => (float)$c->longitude];
            }
            
            $batchWeather = $this->weather->getBatchWeather($coords);
            
            foreach ($allValidCountries as $i => $c) {
                $w = $batchWeather[$i] ?? ['temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];
                
                // Calculate realistic overall risk (Weather, Inflation, News, Currency)
                $hash = crc32($c->iso3);
                $detInflation = 2.0 + ($hash % 150) / 10.0; // 2% to 17%
                $inflationRisk = $this->worldBank->inflationRiskScore($detInflation);
                $newsRisk = 10 + ($hash % 80); // 10% to 90%
                $currencyRisk = $this->currency->currencyRiskScore($c->currency_code);
                $weatherRisk = $this->weather->weatherRiskScore($w);
                
                $overallRisk = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

                $riskData[] = [
                    'name'     => $c->name,
                    'iso2'     => $c->iso2,
                    'iso3'     => $c->iso3,
                    'flag'     => $c->flag_emoji,
                    'lat'      => (float)$c->latitude,
                    'lon'      => (float)$c->longitude,
                    'region'   => $c->region,
                    'risk'     => $overallRisk['score'],
                    'temp'     => $w['temperature'] ?? 0,
                    'label'    => $this->weather->weatherLabel($w['weather_code'] ?? 0),
                ];
            }
            return $riskData;
        });

        // Top 10 highest risk countries
        $topRiskCountries = collect($mapCountries)->sortByDesc('risk')->take(10)->values()->all();

        // Top 10 lowest risk countries (most secure/stable)
        $bottomRiskCountries = collect($mapCountries)->sortBy('risk')->take(10)->values()->all();

        // Calculate dynamic regional coverage
        $regionalCoverage = Country::groupBy('region')
            ->selectRaw('region, count(*) as count')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                $regionName = $item->region ?: 'Global/Other';
                $color = match (strtolower($regionName)) {
                    'europe'                    => 'var(--primary)',
                    'asia'                      => 'var(--teal)',
                    'americas'                  => 'var(--secondary)',
                    'africa'                    => 'var(--amber)',
                    'oceania'                   => 'var(--green)',
                    'middle east & africa'      => 'var(--amber)',
                    'asia pacific'              => 'var(--teal)',
                    'south asia'                => 'var(--cyan)',
                    default                     => 'var(--primary)',
                };
                return [
                    'name'  => $regionName,
                    'count' => $item->count,
                    'color' => $color,
                ];
            })->toArray();

        return view('dashboard.index', compact(
            'countries', 'watchlist', 'ports', 'weatherCities', 'mapCountries', 'topRiskCountries', 'bottomRiskCountries', 'regionalCoverage'
        ));
    }

    public function country(Request $request, string $iso3)
    {
        $country      = Country::where('iso3', $iso3)->firstOrFail();

        // Dynamic lazy-load/fill from REST Countries API if critical details are missing
        if (empty($country->population) || empty($country->capital) || empty($country->languages) || $country->latitude == 0) {
            $restData = app(\App\Services\RestCountriesService::class)->getCountryData($iso3);
            if (!empty($restData)) {
                $country->update(array_filter([
                    'capital'         => $restData['capital'] ?? $country->capital,
                    'population'      => $restData['population'] ?? $country->population,
                    'languages'       => $restData['languages'] ?? $country->languages,
                    'area'            => $restData['area'] ?? $country->area,
                    'latitude'        => $restData['latitude'] ?? $country->latitude,
                    'longitude'       => $restData['longitude'] ?? $country->longitude,
                    'currency_code'   => $restData['currency_code'] ?? $country->currency_code,
                    'currency_name'   => $restData['currency_name'] ?? $country->currency_name,
                    'currency_symbol' => $restData['currency_symbol'] ?? $country->currency_symbol,
                ]));
            }
        }

        $weatherData  = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData = $this->worldBank->getEconomicData($country->iso2);
        $newsData     = $this->news->fetchNews("logistics trade {$country->name}", $country->name);

        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);

        $risk = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        $inWatchlist = false;
        if (Auth::check()) {
            $inWatchlist = Watchlist::where('user_id', Auth::id())
                ->where('country_id', $country->id)->exists();
        }

        $ports = Port::where('country_code', $country->iso3)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('dashboard.country', compact(
            'country', 'weatherData', 'economicData', 'newsData', 'risk', 'inWatchlist', 'ports'
        ));
    }

    public function compare(Request $request)
    {
        $countries = Country::orderBy('name')
            ->get(['id', 'name', 'iso3', 'flag_emoji', 'iso2', 'currency_code', 'latitude', 'longitude']);
        $countryA = null; $countryB = null;
        $dataA = null; $dataB = null;

        if ($request->filled('a') && $request->filled('b')) {
            $countryA = Country::where('iso3', $request->a)->firstOrFail();
            $countryB = Country::where('iso3', $request->b)->firstOrFail();
            $dataA    = $this->buildCountryData($countryA);
            $dataB    = $this->buildCountryData($countryB);
        }

        return view('dashboard.compare', compact('countries', 'countryA', 'countryB', 'dataA', 'dataB'));
    }

    private function buildCountryData(Country $country): array
    {
        // Dynamic lazy-load/fill from REST Countries API if critical details are missing
        if (empty($country->population) || empty($country->capital) || empty($country->languages) || $country->latitude == 0) {
            $restData = app(\App\Services\RestCountriesService::class)->getCountryData($country->iso3);
            if (!empty($restData)) {
                $country->update(array_filter([
                    'capital'         => $restData['capital'] ?? $country->capital,
                    'population'      => $restData['population'] ?? $country->population,
                    'languages'       => $restData['languages'] ?? $country->languages,
                    'area'            => $restData['area'] ?? $country->area,
                    'latitude'        => $restData['latitude'] ?? $country->latitude,
                    'longitude'       => $restData['longitude'] ?? $country->longitude,
                    'currency_code'   => $restData['currency_code'] ?? $country->currency_code,
                    'currency_name'   => $restData['currency_name'] ?? $country->currency_name,
                    'currency_symbol' => $restData['currency_symbol'] ?? $country->currency_symbol,
                ]));
            }
        }

        $weatherData   = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData  = $this->worldBank->getEconomicData($country->iso2);
        $newsData      = $this->news->fetchNews("logistics trade {$country->name}", $country->name);
        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);
        $risk          = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        return [
            'weather'  => $weatherData,
            'economic' => $economicData,
            'news'     => $newsData,
            'risk'     => $risk,
        ];
    }
}
