<?php

namespace App\Services;

use App\Models\NewsCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class NewsService
{
    private array $positiveWords = [
        'growth', 'increase', 'profit', 'stable', 'improve', 'surge',
        'boost', 'recovery', 'expansion', 'gain', 'rise', 'success',
        'opportunity', 'demand', 'strong', 'robust', 'advance', 'breakthrough',
    ];

    private array $negativeWords = [
        'war', 'crisis', 'inflation', 'delay', 'disaster', 'strike',
        'shortage', 'congestion', 'conflict', 'disruption', 'collapse',
        'decline', 'recession', 'tariff', 'sanction', 'loss', 'risk',
        'attack', 'blockage', 'halt', 'suspend', 'ban', 'threat',
    ];

    public function fetchNews(string $query = 'logistics shipping trade economy'): array
    {
        $cacheKey = 'news_' . md5($query);
        $cached   = NewsCache::where('cache_key', $cacheKey)->first();

        if ($cached && $cached->cached_at->diffInMinutes(now()) < 60) {
            return [
                'articles'     => $cached->articles,
                'positive_pct' => $cached->positive_pct,
                'neutral_pct'  => $cached->neutral_pct,
                'negative_pct' => $cached->negative_pct,
                'from_cache'   => true,
            ];
        }

        $apiKey = config('services.gnews.key');
        $articles = [];
        try {
            $response = Http::timeout(5)->withOptions(['verify' => false])->get('https://gnews.io/api/v4/search', [
                'q'       => $query,
                'lang'    => 'en',
                'max'     => 20,
                'apikey'  => $apiKey,
            ]);

            if ($response->successful()) {
                $raw = $response->json('articles', []);
                foreach ($raw as $a) {
                    $articles[] = [
                        'title'       => $a['title'] ?? '',
                        'description' => $a['description'] ?? '',
                        'url'         => $a['url'] ?? '',
                        'source'      => $a['source']['name'] ?? '',
                        'published'   => $a['publishedAt'] ?? '',
                        'image'       => $a['image'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Ignore timeout/connection exceptions, fallback will be used
        }

        if (empty($articles)) {
            $articles = $this->getFallbackArticles();
        }

        $sentiment = $this->analyzeSentiment($articles);

        NewsCache::updateOrCreate(['cache_key' => $cacheKey], [
            'articles'     => $articles,
            'positive_pct' => $sentiment['positive'],
            'neutral_pct'  => $sentiment['neutral'],
            'negative_pct' => $sentiment['negative'],
            'cached_at'    => now(),
        ]);

        return [
            'articles'     => $articles,
            'positive_pct' => $sentiment['positive'],
            'neutral_pct'  => $sentiment['neutral'],
            'negative_pct' => $sentiment['negative'],
            'from_cache'   => false,
        ];
    }

    public function analyzeSentiment(array $articles): array
    {
        $pos = 0; $neg = 0; $total = 0;

        foreach ($articles as $article) {
            $text  = strtolower(($article['title'] ?? '') . ' ' . ($article['description'] ?? ''));
            $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($words as $word) {
                if (in_array($word, $this->positiveWords)) $pos++;
                if (in_array($word, $this->negativeWords)) $neg++;
            }
            $total += count($words);
        }

        $sum = $pos + $neg;
        if ($sum === 0) return ['positive' => 33.33, 'neutral' => 33.33, 'negative' => 33.33];

        $posPercent = round(($pos / $sum) * 100, 2);
        $negPercent = round(($neg / $sum) * 100, 2);
        $neuPercent = round(100 - $posPercent - $negPercent, 2);

        return [
            'positive' => max(0, $posPercent),
            'neutral'  => max(0, $neuPercent),
            'negative' => max(0, $negPercent),
        ];
    }

    public function newsRiskScore(float $negativePct): float
    {
        return min(100, $negativePct * 1.5);
    }

    private function getFallbackArticles(): array
    {
        return [
            [
                'title'       => 'Global Shipping Rates Stabilize After Recent Disruptions',
                'description' => 'Freight rates show signs of stabilization as port congestion eases across major hubs.',
                'url'         => '#',
                'source'      => 'Supply Chain Digest',
                'published'   => now()->toISOString(),
                'image'       => null,
            ],
            [
                'title'       => 'Trade Volume Expansion Continues in Q4',
                'description' => 'Global trade shows strong growth momentum driven by demand recovery.',
                'url'         => '#',
                'source'      => 'Trade Monitor',
                'published'   => now()->subHours(2)->toISOString(),
                'image'       => null,
            ],
            [
                'title'       => 'Port Delays Reported at Major Asian Hubs',
                'description' => 'Congestion risk rises as vessel queues grow at Singapore and Shanghai terminals.',
                'url'         => '#',
                'source'      => 'Logistics Weekly',
                'published'   => now()->subHours(4)->toISOString(),
                'image'       => null,
            ],
        ];
    }
}
