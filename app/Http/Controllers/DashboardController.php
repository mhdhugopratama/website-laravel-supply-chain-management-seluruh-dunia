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
                ->get(['countries.id', 'name', 'iso3', 'flag_emoji'])
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

        $weatherCities = Cache::remember('dashboard_weather_cities', 1800, function () use ($majorCities) {
            $result = [];
            foreach ($majorCities as $city) {
                $w = $this->weather->getWeather($city['lat'], $city['lon']);
                $result[] = [
                    'name'    => $city['name'],
                    'country' => $city['country'],
                    'lat'     => $city['lat'],
                    'lon'     => $city['lon'],
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
                $riskData[] = [
                    'name'     => $c->name,
                    'iso3'     => $c->iso3,
                    'flag'     => $c->flag_emoji,
                    'lat'      => (float)$c->latitude,
                    'lon'      => (float)$c->longitude,
                    'region'   => $c->region,
                    'risk'     => $this->weather->weatherRiskScore($w),
                    'temp'     => $w['temperature'] ?? 0,
                    'label'    => $this->weather->weatherLabel($w['weather_code'] ?? 0),
                ];
            }
            return $riskData;
        });

        return view('dashboard.index', compact(
            'countries', 'watchlist', 'ports', 'weatherCities', 'mapCountries'
        ));
    }

    public function country(Request $request, string $iso3)
    {
        $country      = Country::where('iso3', $iso3)->firstOrFail();
        $weatherData  = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData = $this->worldBank->getEconomicData($country->iso2);
        $newsData     = $this->news->fetchNews("logistics trade {$country->name}");

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

        return view('dashboard.country', compact(
            'country', 'weatherData', 'economicData', 'newsData', 'risk', 'inWatchlist'
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
        $weatherData   = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData  = $this->worldBank->getEconomicData($country->iso2);
        $newsData      = $this->news->fetchNews("logistics trade {$country->name}");
        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);
        $risk          = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        return [
            'weather'  => $weatherData,
            'economic' => $economicData,
            'risk'     => $risk,
        ];
    }
}
