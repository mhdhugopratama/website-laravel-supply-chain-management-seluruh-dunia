<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getWeather(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(3)
                ->withOptions(['verify' => false])
                ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'         => $lat,
                'longitude'        => $lon,
                'current'          => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                'forecast_days'    => 1,
            ]);

            if ($response->failed()) {
                return ['error' => true, 'temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];
            }
        } catch (\Exception $e) {
            return ['error' => true, 'temperature' => 0, 'precipitation' => 0, 'wind_speed' => 0, 'weather_code' => 0];
        }

        $current = $response->json('current', []);
        return [
            'temperature'  => $current['temperature_2m'] ?? 0,
            'precipitation'=> $current['precipitation'] ?? 0,
            'wind_speed'   => $current['wind_speed_10m'] ?? 0,
            'weather_code' => $current['weather_code'] ?? 0,
        ];
    }

    public function weatherRiskScore(array $weather): float
    {
        $score = 0;
        $score += min(40, ($weather['wind_speed'] / 120) * 40);
        $score += min(30, ($weather['precipitation'] / 50) * 30);
        $code  = $weather['weather_code'];
        if ($code >= 80) $score += 30;
        elseif ($code >= 60) $score += 15;
        return min(100, $score);
    }

    public function weatherLabel(int $code): string
    {
        return match(true) {
            $code === 0            => 'Clear Sky',
            $code <= 3             => 'Partly Cloudy',
            $code <= 49            => 'Foggy',
            $code <= 59            => 'Drizzle',
            $code <= 69            => 'Rain',
            $code <= 79            => 'Snow',
            $code <= 82            => 'Rain Showers',
            $code <= 86            => 'Snow Showers',
            $code <= 99            => 'Thunderstorm',
            default                => 'Unknown',
        };
    }
}
