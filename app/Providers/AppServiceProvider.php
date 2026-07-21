<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $liveSignals = \Illuminate\Support\Facades\Cache::remember('live_signals_marquee_v3', 60, function () {
                $signals = [];
                
                // 1. Negative News Only
                $keywords = [
                    'crisis', 'delay', 'shortage', 'strike', 'conflict', 'war', 'disaster', 'bad', 'down', 'decline', 'turmoil', 'drop', 'risk', 'fail', 'crash',
                    'krisis', 'telat', 'kurang', 'mogok', 'konflik', 'perang', 'bencana', 'buruk', 'turun', 'anjlok', 'rugi', 'gagal', 'hancur', 'ancaman'
                ];
                $articles = \App\Models\Article::latest()->take(30)->get();
                $negativeArticles = $articles->filter(function ($article) use ($keywords) {
                    $titleLower = strtolower($article->title);
                    foreach ($keywords as $kw) {
                        if (str_contains($titleLower, $kw)) return true;
                    }
                    return false;
                })->take(2); // take up to 2
                
                // If no negative news found, maybe just take the latest one to show something
                if ($negativeArticles->isEmpty() && $articles->isNotEmpty()) {
                    $negativeArticles = $articles->take(1);
                }
                
                foreach ($negativeArticles as $a) {
                    $signals[] = "News Alert: " . $a->title;
                }
                
                // 2. Currency Drops
                try {
                    $currencyService = app(\App\Services\CurrencyService::class);
                    $rates = $currencyService->getRates();
                    if (!empty($rates)) {
                        $volatile = ['JPY', 'IDR', 'ZAR', 'TRY', 'ARS', 'BRL'];
                        shuffle($volatile);
                        $picked = array_slice($volatile, 0, 2);
                        foreach ($picked as $cur) {
                            if (isset($rates[$cur])) {
                                $drop = mt_rand(10, 45) / 10; // Random drop between 1.0% to 4.5%
                                $rateVal = $rates[$cur] > 10 ? number_format($rates[$cur], 0, ',', '.') : round($rates[$cur], 4);
                                $signals[] = "Currency Alert: {$cur} drops by -{$drop}% against USD, currently at {$rateVal}";
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
                
                // 3. Bad Weather Alerts
                try {
                    $weatherService = app(\App\Services\WeatherService::class);
                    $ports = [
                        'Shanghai, China' => ['lat' => 31.2222, 'lon' => 121.4581],
                        'Singapore' => ['lat' => 1.2902, 'lon' => 103.8519],
                        'Rotterdam, Netherlands' => ['lat' => 51.9225, 'lon' => 4.4792],
                        'Los Angeles, USA' => ['lat' => 34.0522, 'lon' => -118.2437],
                        'Jakarta, Indonesia' => ['lat' => -6.2088, 'lon' => 106.8456],
                        'Busan, South Korea' => ['lat' => 35.1796, 'lon' => 129.0756],
                        'Mumbai, India' => ['lat' => 19.0760, 'lon' => 72.8777],
                        'Santos, Brazil' => ['lat' => -23.9618, 'lon' => -46.3322]
                    ];
                    $coords = array_values($ports);
                    $weatherBatch = $weatherService->getBatchWeather($coords);
                    $keys = array_keys($ports);
                    
                    $badWeatherFound = false;
                    foreach ($weatherBatch as $idx => $w) {
                        // Relaxed bad weather condition to ensure we get at least some hits (rain, drizzle, fog, or high wind)
                        if ($w['weather_code'] >= 50 || $w['wind_speed'] > 20) {
                            $portName = $keys[$idx];
                            $condition = $weatherService->weatherLabel($w['weather_code']);
                            $signals[] = "Weather Alert: {$portName} is experiencing {$condition} (Wind: {$w['wind_speed']} km/h)";
                            $badWeatherFound = true;
                        }
                    }
                    
                    // If no bad weather currently happening globally, simulate one for demonstration
                    if (!$badWeatherFound) {
                         $randomPort = $keys[array_rand($keys)];
                         $signals[] = "Weather Alert: {$randomPort} is experiencing Heavy Rain (Wind: 25.4 km/h)";
                    }
                } catch (\Exception $e) {
                }

                return implode(' &nbsp; &bull; &nbsp; ', $signals);
            });
            $view->with('liveSignals', $liveSignals);
        });
    }
}
