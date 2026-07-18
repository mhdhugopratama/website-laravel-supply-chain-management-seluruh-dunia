<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\CurrencyService;
use App\Services\RiskEngine;
use App\Services\NewsService;

class AnalyticsController extends Controller
{
    public function __construct(
        private WeatherService $weather,
        private WorldBankService $worldBank,
        private CurrencyService $currency,
        private RiskEngine $riskEngine,
        private NewsService $news
    ) {}

    public function index()
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'iso3', 'flag_emoji', 'iso2']);
        return view('analytics.index', compact('countries'));
    }

    public function data(string $iso3)
    {
        $country       = Country::where('iso3', $iso3)->firstOrFail();

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

        $weatherData   = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData  = $this->worldBank->getEconomicData($country->iso2);
        $newsData      = $this->news->fetchNews("trade {$country->name}", $country->name);
        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);
        $risk          = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        return response()->json([
            'country'  => $country,
            'weather'  => $weatherData,
            'economic' => $economicData,
            'news'     => $newsData,
            'risk'     => $risk,
        ]);
    }
}
