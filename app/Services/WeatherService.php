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
                return $this->getMockWeather($lat, $lon);
            }
        } catch (\Exception $e) {
            return $this->getMockWeather($lat, $lon);
        }

        $current = $response->json('current', []);
        return [
            'temperature'  => $current['temperature_2m'] ?? 0,
            'precipitation'=> $current['precipitation'] ?? 0,
            'wind_speed'   => $current['wind_speed_10m'] ?? 0,
            'weather_code' => $current['weather_code'] ?? 0,
        ];
    }

    public function getBatchWeather(array $coordinates): array
    {
        // formatnya kira2 kyk gini: [['lat'=>..., 'lon'=>...], ...]
        $results = [];
        $chunks = array_chunk($coordinates, 50, true);
        
        foreach ($chunks as $chunk) {
            $lats = implode(',', array_column($chunk, 'lat'));
            $lons = implode(',', array_column($chunk, 'lon'));
            
            try {
                $response = Http::timeout(10)
                    ->withOptions(['verify' => false])
                    ->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude'         => $lats,
                        'longitude'        => $lons,
                        'current'          => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                        'forecast_days'    => 1,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    // kl requestnya banyak, api bakal ngasih array
                    if (is_array($data) && isset($data[0]['current'])) {
                        $i = 0;
                        foreach ($chunk as $originalKey => $coord) {
                            $current = $data[$i]['current'] ?? [];
                            $results[$originalKey] = [
                                'temperature'  => $current['temperature_2m'] ?? 0,
                                'precipitation'=> $current['precipitation'] ?? 0,
                                'wind_speed'   => $current['wind_speed_10m'] ?? 0,
                                'weather_code' => $current['weather_code'] ?? 0,
                            ];
                            $i++;
                        }
                    } else if (isset($data['current'])) {
                        // ini kl cuma minta 1 tempat doang
                        $originalKey = array_key_first($chunk);
                        $current = $data['current'];
                        $results[$originalKey] = [
                            'temperature'  => $current['temperature_2m'] ?? 0,
                            'precipitation'=> $current['precipitation'] ?? 0,
                            'wind_speed'   => $current['wind_speed_10m'] ?? 0,
                            'weather_code' => $current['weather_code'] ?? 0,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // cuekin aja yg ini
            }
        }
        
        // kasih data bohongan kl api nya lg ngambek atau kelimit
        foreach ($coordinates as $k => $v) {
            if (!isset($results[$k])) {
                $results[$k] = $this->getMockWeather($v['lat'], $v['lon']);
            }
        }
        
        return $results;
    }

    private function getMockWeather(float $lat, float $lon): array
    {
        $hash = crc32(round($lat, 1) . round($lon, 1) . date('Y-m-d'));
        // buat suhu perkiraan yang masuk akal (makin dekat khatulistiwa makin panas)
        $tempBase = 30 - (abs($lat) / 3);
        $temp = $tempBase + (($hash % 100) / 10 - 5);
        
        // buat angka curah hujan acak dari 0 sampai 15
        $precip = ($hash % 150) / 10;
        if ($precip < 5) $precip = 0; // kalau angkanya sangat kecil, anggap saja tidak hujan
        
        // buat angka kecepatan angin acak
        $wind = 5 + ($hash % 25);
        
        // kode cuacanya
        $code = 0;
        if ($precip > 10) $code = 65; // deres nih
        elseif ($precip > 0) $code = 61; // gerimis doang
        elseif (($hash % 10) > 6) $code = 3; // mendung aja

        return [
            'temperature'  => round($temp, 1),
            'precipitation'=> round($precip, 1),
            'wind_speed'   => $wind,
            'weather_code' => $code,
            'is_mock'      => true // tandai kalau ini adalah data buatan sistem (bukan asli)
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
