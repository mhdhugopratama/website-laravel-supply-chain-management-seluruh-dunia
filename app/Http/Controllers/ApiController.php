<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Services\NewsService;
use App\Services\CurrencyService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\RiskEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    public function __construct(
        private WeatherService $weather,
        private WorldBankService $worldBank,
        private CurrencyService $currency,
        private NewsService $news,
        private RiskEngine $riskEngine
    ) {}

    public function countries(Request $request): JsonResponse
    {
        $query = Country::orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return response()->json($query->get());
    }

    public function risk(Request $request): JsonResponse
    {
        $iso3    = $request->input('iso3');
        $country = Country::where('iso3', $iso3)->firstOrFail();

        $weatherData   = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $economicData  = $this->worldBank->getEconomicData($country->iso2);
        $newsData      = $this->news->fetchNews("logistics {$country->name}");

        $weatherRisk   = $this->weather->weatherRiskScore($weatherData);
        $inflationRisk = $this->worldBank->inflationRiskScore($economicData['inflation']);
        $newsRisk      = $this->news->newsRiskScore($newsData['negative_pct']);
        $currencyRisk  = $this->currency->currencyRiskScore($country->currency_code);

        $risk = $this->riskEngine->calculate($weatherRisk, $inflationRisk, $newsRisk, $currencyRisk);

        return response()->json(array_merge($risk, [
            'country'  => $country->name,
            'weather'  => $weatherData,
            'economic' => $economicData,
        ]));
    }

    public function ports(Request $request): JsonResponse
    {
        $query = Port::query();
        if ($request->filled('country')) {
            $query->where('country_code', strtoupper($request->country));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return response()->json($query->limit(500)->get());
    }

    public function news(Request $request): JsonResponse
    {
        $query  = $request->input('q', 'logistics shipping trade economy');
        $result = $this->news->fetchNews($query);
        return response()->json($result);
    }

    public function currency(Request $request): JsonResponse
    {
        $from   = strtoupper($request->input('from', 'USD'));
        $to     = strtoupper($request->input('to', 'EUR'));
        $amount = (float) $request->input('amount', 1);

        $result = $this->currency->convert($from, $to, $amount);
        return response()->json($result);
    }

    public function rates(): JsonResponse
    {
        $rates = $this->currency->getRates();
        return response()->json(['rates' => $rates]);
    }

    public function weather(Request $request): JsonResponse
    {
        $iso3    = $request->input('iso3');
        $country = Country::where('iso3', $iso3)->firstOrFail();
        $data    = $this->weather->getWeather((float)$country->latitude, (float)$country->longitude);
        $data['label'] = $this->weather->weatherLabel((int)$data['weather_code']);
        return response()->json($data);
    }
}
