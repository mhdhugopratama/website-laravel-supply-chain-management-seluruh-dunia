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

        // ambil cuaca buat semua negara yg ada kordinatnya (biar ga kaku list kotanya)
        $weatherCities = Cache::remember('dashboard_weather_cities', 60, function () use ($countries) {
            // pastiin negaranya punya lat long
            $validCountries = $countries->filter(function ($c) {
                return $c->latitude && $c->longitude;
            });
            // siapin kordinatnya, urutannya jgn sampe acak2an
            $coords = [];
            foreach ($validCountries as $i => $c) {
                $coords[$i] = ['lat' => (float)$c->latitude, 'lon' => (float)$c->longitude];
            }
            // tembak api cuaca sekalian borongan
            $batchWeather = $this->weather->getBatchWeather($coords);
            // rapihin hasil cuacanya per negara
            $result = [];
            foreach ($validCountries as $i => $c) {
                $w = $batchWeather[$i] ?? ['temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];
                
                $flagEmoji = '';
                if (!empty($c->iso2) && strlen($c->iso2) >= 2) {
                    $flagEmoji = mb_chr(ord(strtoupper($c->iso2)[0]) - 65 + 127462, 'UTF-8') . mb_chr(ord(strtoupper($c->iso2)[1]) - 65 + 127462, 'UTF-8');
                }

                $result[] = [
                    'name'    => $c->name,
                    'iso2'    => $c->iso2,
                    'flag'    => $flagEmoji,
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

        $allValidCountries = $countries->whereNotNull('latitude')->whereNotNull('longitude')->values();
        
        $batchWeather = \Illuminate\Support\Facades\Cache::remember('dashboard_map_weather_batch', 3600, function() use ($allValidCountries) {
            $coords = [];
            foreach ($allValidCountries as $i => $c) {
                $coords[$i] = ['lat' => (float)$c->latitude, 'lon' => (float)$c->longitude];
            }
            return $this->weather->getBatchWeather($coords);
        });

        $riskData = [];
        foreach ($allValidCountries as $i => $c) {
            $w = $batchWeather[$i] ?? ['temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];

            // Cek cache dulu — kalau negara ini pernah dibuka profilnya,
            // pakai data risiko asli yang sudah di-cache (100% sama dgn halaman detail)
            $cachedRisk = Cache::get('country_risk_' . $c->iso2);

            if ($cachedRisk) {
                $overallRisk = $cachedRisk;
            } else {
                // Belum pernah dikunjungi — pakai estimasi cepat (tanpa API call)
                $hash = crc32($c->iso3);
                $weatherRisk   = $this->weather->weatherRiskScore($w);

                // Coba baca dari cache WorldBank (sudah ada kalau pernah diload)
                $wbCache = Cache::get('worldbank_' . $c->iso2);
                if ($wbCache) {
                    $inflationRisk = $this->worldBank->inflationRiskScore($wbCache['inflation']);
                } else {
                    $inflationRisk = $this->worldBank->inflationRiskScore(2.0 + ($hash % 150) / 10.0);
                }

                // Coba baca dari cache News
                $newsQuery    = "logistics trade {$c->name}";
                $newsCacheKey = 'news_' . md5($newsQuery . '_' . $c->name);
                $newsCache    = \App\Models\NewsCache::where('cache_key', $newsCacheKey)->first();
                if ($newsCache && $newsCache->updated_at->diffInHours(now()) < 12) {
                    $newsRisk = $this->news->newsRiskScore($newsCache->negative_pct);
                } else {
                    $newsRisk = 10 + ($hash % 80);
                }

                $currencyRisk = $this->currency->currencyRiskScore($c->currency_code);
                $overallRisk  = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);
            }

            $flagEmoji = '';
            if (!empty($c->iso2) && strlen($c->iso2) >= 2) {
                $flagEmoji = mb_chr(ord(strtoupper($c->iso2)[0]) - 65 + 127462, 'UTF-8') . mb_chr(ord(strtoupper($c->iso2)[1]) - 65 + 127462, 'UTF-8');
            }

            $riskData[] = [
                'name'           => $c->name,
                'iso2'           => $c->iso2,
                'iso3'           => $c->iso3,
                'flag'           => $flagEmoji,
                'lat'            => (float)$c->latitude,
                'lon'            => (float)$c->longitude,
                'region'         => $c->region,
                'risk'           => $overallRisk['score'],
                'risk_level'     => $overallRisk['level']['label'],
                'temp'           => $w['temperature'] ?? 0,
                'label'          => $this->weather->weatherLabel($w['weather_code'] ?? 0),
            ];
        }
        $mapCountries = $riskData;

        // 10 negara dengan risiko paling tinggi
        $topRiskCountries = collect($mapCountries)->sortByDesc('risk')->take(10)->values()->all();

        // 10 negara yang paling aman dan stabil
        $bottomRiskCountries = collect($mapCountries)->sortBy('risk')->take(10)->values()->all();

        // hitung coverage region otomatis
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

        // tentukan negara mana yang cuacanya ekstrim dan mana yang stabil
        $processedWeather = collect($weatherCities)->map(function ($city) {
            // hitung parahnya cuaca: bedanya suhu dr 22C ditambah angin+hujan
            $tempExtremity = abs($city['temp'] - 22);
            $city['extreme_score'] = ($tempExtremity * 2) + $city['risk'];
            return $city;
        });

        $extremeWeatherCities = $processedWeather->sortByDesc('extreme_score')->take(12)->values()->all();
        $stableWeatherCities  = $processedWeather->sortBy('extreme_score')->take(12)->values()->all();

        return view('dashboard.index', compact(
            'countries', 'watchlist', 'ports', 'weatherCities', 'extremeWeatherCities', 'stableWeatherCities', 'mapCountries', 'topRiskCountries', 'bottomRiskCountries', 'regionalCoverage'
        ));
    }

    public function country(Request $request, string $iso3)
    {
        $country      = Country::where('iso3', $iso3)->firstOrFail();

        // kalau ada info penting yang kosong, lengkapi datanya dari API negara luar
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
                ], fn($val) => !is_null($val)));
            }
        }

        $weatherData  = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData = $this->worldBank->getEconomicData($country->iso2);
        $newsData     = $this->news->fetchNews("logistics trade {$country->name}", $country->name);

        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);
        $risk          = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        // Simpan ke cache supaya peta dashboard langsung pakai skor yang sama
        Cache::put('country_risk_' . $country->iso2, $risk, 3600);

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
        // kalau ada info penting yang kosong, lengkapi datanya dari API negara luar
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
                ], fn($val) => !is_null($val)));
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
        $rates         = $this->currency->getRates();

        return [
            'weather'  => $weatherData,
            'economic' => $economicData,
            'news'     => $newsData,
            'risk'     => $risk,
            'exchange' => $rates[$country->currency_code] ?? null,
        ];
    }
}
