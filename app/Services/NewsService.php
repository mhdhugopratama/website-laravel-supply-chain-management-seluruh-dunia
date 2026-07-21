<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsService
{
    private array $positiveWords = [];

    private array $negativeWords = [];

    public function __construct()
    {
        // ambil kamus kata dari database, kalau kosong pakai bawaan dari kode
        $this->positiveWords = DB::table('positive_words')->pluck('word')->toArray();
        $this->negativeWords = DB::table('negative_words')->pluck('word')->toArray();

        if (empty($this->positiveWords)) {
            $this->positiveWords = [
                'growth', 'increase', 'profit', 'stable', 'improve', 'surge',
                'boost', 'recovery', 'expansion', 'gain', 'rise', 'success',
                'opportunity', 'demand', 'strong', 'robust', 'advance', 'breakthrough',
            ];
        }
        if (empty($this->negativeWords)) {
            $this->negativeWords = [
                'war', 'crisis', 'inflation', 'delay', 'disaster', 'strike',
                'shortage', 'congestion', 'conflict', 'disruption', 'collapse',
                'decline', 'recession', 'tariff', 'sanction', 'loss', 'risk',
                'attack', 'blockage', 'halt', 'suspend', 'ban', 'threat',
            ];
        }
    }

    public function fetchNews(string $query = 'logistics shipping trade economy', ?string $countryName = null, bool $forceRefresh = false): array
    {
        $cacheKey = 'news_'.md5($query.'_'.$countryName);

        if (! $forceRefresh) {
            $cached = NewsCache::where('cache_key', $cacheKey)->first();

            if ($cached && $cached->cached_at->diffInMinutes(now()) < 120) {
                $articles = is_string($cached->articles) ? json_decode($cached->articles, true) : $cached->articles;

                // pake cache cuma kl artikelnya beneran ada, jgn yg fake
                if (! empty($articles) && ! str_starts_with($articles[0]['url'] ?? '', '#')) {
                    $sentiment = $this->analyzeSentiment($articles);

                    return [
                        'articles' => $articles,
                        'positive_pct' => $sentiment['positive'],
                        'neutral_pct' => $sentiment['neutral'],
                        'negative_pct' => $sentiment['negative'],
                        'pos_count' => $sentiment['pos_count'],
                        'neg_count' => $sentiment['neg_count'],
                        'sentiment' => $sentiment['sentiment'],
                        'from_cache' => true,
                    ];
                }
            }
        }

        $apiKey = config('services.gnews.key');
        $articles = [];

        // coba cari pake keyword awal dulu
        $articles = $this->callGNews($query, $apiKey);

        // kl yg awal zonk, pake keyword cadangan
        if (empty($articles) && $countryName) {
            $broadQuery = "{$countryName} (logistics OR trade OR shipping OR economy)";
            $articles = $this->callGNews($broadQuery, $apiKey);

            if (empty($articles)) {
                $articles = $this->callGNews($countryName, $apiKey);
            }
        }

        // ambil berita buatan admin trus taruh paling atas ntar
        $localArticles = Article::where('status', 'published')->latest()->get()->map(function ($a) {
            return [
                'title' => $a->title,
                'description' => $a->excerpt ?? Str::limit(strip_tags($a->body), 150),
                'url' => $a->source_url ?? '#',
                'source' => 'Internal / Admin',
                'published' => $a->created_at->toISOString(),
                'image' => null, // ntar thumbnail bisa diurus dimari kl niat
            ];
        })->toArray();

        // gabungin berita lokal dulu, baru deh berita api
        if (! empty($localArticles)) {
            $articles = array_merge($localArticles, $articles);
            $articles = collect($articles)->unique('title')->values()->toArray();
        }

        // simpen ke cache kl sukses nyomot berita aslinya
        if (! empty($articles)) {
            NewsCache::updateOrCreate(['cache_key' => $cacheKey], [
                'articles' => $articles,
                'cached_at' => now(),
            ]);
        } else {
            // kl api down trus cache kosong, yaudah comot cache yg lama aja kepaksa
            $cached = NewsCache::where('cache_key', $cacheKey)->first();
            if ($cached) {
                $articles = is_string($cached->articles) ? json_decode($cached->articles, true) : $cached->articles;
                // buang aja berita karangan dr hasilnya
                $articles = array_filter($articles, fn ($a) => ! str_starts_with($a['url'] ?? '', '#'));
            }
        }

        $sentiment = $this->analyzeSentiment($articles);

        NewsCache::updateOrCreate(['cache_key' => $cacheKey], [
            'articles' => $articles,
            'positive_pct' => $sentiment['positive'],
            'neutral_pct' => $sentiment['neutral'],
            'negative_pct' => $sentiment['negative'],
            'cached_at' => now(),
        ]);

        return [
            'articles' => $articles,
            'positive_pct' => $sentiment['positive'],
            'neutral_pct' => $sentiment['neutral'],
            'negative_pct' => $sentiment['negative'],
            'pos_count' => $sentiment['pos_count'],
            'neg_count' => $sentiment['neg_count'],
            'sentiment' => $sentiment['sentiment'],
            'from_cache' => false,
        ];
    }

    private function callGNews(string $query, ?string $apiKey): array
    {
        if (empty($apiKey)) {
            return [];
        }
        $articles = [];
        try {
            $response = Http::timeout(4)->withOptions(['verify' => false])->get('https://gnews.io/api/v4/search', [
                'q' => $query,
                'lang' => 'en',
                'max' => 15,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $raw = $response->json('articles', []);
                foreach ($raw as $a) {
                    $articles[] = [
                        'title' => $a['title'] ?? '',
                        'description' => $a['description'] ?? '',
                        'url' => $a['url'] ?? '',
                        'source' => $a['source']['name'] ?? '',
                        'published' => $a['publishedAt'] ?? '',
                        'image' => $a['image'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            logger()->warning('GNews API exception: '.$e->getMessage());
        }

        return $articles;
    }

    public function analyzeSentiment(array &$articles): array
    {
        $pos = 0;
        $neg = 0;
        $total = 0;

        foreach ($articles as &$article) {
            $text = strtolower(($article['title'] ?? '').' '.($article['description'] ?? ''));
            $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);

            $artPos = 0;
            $artNeg = 0;

            foreach ($words as $word) {
                if (in_array($word, $this->positiveWords)) {
                    $pos++;
                    $artPos++;
                }
                if (in_array($word, $this->negativeWords)) {
                    $neg++;
                    $artNeg++;
                }
            }

            $article['sentiment'] = $artPos > $artNeg ? 'Positive' : ($artPos < $artNeg ? 'Negative' : 'Neutral');

            $total += count($words);
        }

        $sum = $pos + $neg;
        if ($sum === 0) {
            return [
                'positive' => 33.33,
                'neutral' => 33.34,
                'negative' => 33.33,
                'pos_count' => 0,
                'neg_count' => 0,
                'sentiment' => 'Neutral',
            ];
        }

        $posPercent = round(($pos / $sum) * 100, 2);
        $negPercent = round(($neg / $sum) * 100, 2);
        $neuPercent = round(100 - $posPercent - $negPercent, 2);

        $sentiment = $pos > $neg ? 'Positive' : ($pos < $neg ? 'Negative' : 'Neutral');

        return [
            'positive' => max(0, $posPercent),
            'neutral' => max(0, $neuPercent),
            'negative' => max(0, $negPercent),
            'pos_count' => $pos,
            'neg_count' => $neg,
            'sentiment' => $sentiment,
        ];
    }

    public function newsRiskScore(float $negativePct): float
    {
        return min(100, $negativePct * 1.5);
    }

    private function getFallbackArticles(?string $countryName = null): array
    {
        $country = $countryName ?? 'Global';

        return [
            [
                'title' => "Shipping Rates and Logistics Conditions Stabilize in {$country}",
                'description' => "Freight rates and border processing indicators show signs of stabilization across {$country} trade routes. Industry experts remain cautiously optimistic about the coming quarter.",
                'url' => '#',
                'source' => 'Supply Chain Digest',
                'published' => now()->toISOString(),
                'image' => null,
            ],
            [
                'title' => "{$country} Trade Volume Expansion Gains Momentum",
                'description' => "Recent analysis shows strong import/export momentum in {$country} driven by rising demand in the manufacturing and retail sectors.",
                'url' => '#',
                'source' => 'Trade Monitor',
                'published' => now()->subHours(1)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Transit Congestion and Port Risk Assessment for {$country}",
                'description' => "Vessel delay tracking shows moderate queue patterns at major entry gateways of {$country}. Carriers are exploring alternate routing to minimize bottlenecks.",
                'url' => '#',
                'source' => 'Logistics Weekly',
                'published' => now()->subHours(3)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "New Tariff Regulations Implemented in {$country} Region",
                'description' => 'Authorities have announced a series of new tariff structures affecting cross-border logistics. Forwarders prepare for increased compliance checks.',
                'url' => '#',
                'source' => 'Economic Times',
                'published' => now()->subHours(5)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Supply Chain Resilience: How {$country} is Adapting",
                'description' => "A deep dive into the strategies adopted by major distributors in {$country} to combat recent disruptions, including nearshoring and digital twins.",
                'url' => '#',
                'source' => 'Global Freight Journal',
                'published' => now()->subHours(8)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Fuel Price Volatility Impacts {$country} Shipping Lanes",
                'description' => "Recent fluctuations in bunker fuel costs are causing carriers to adjust their bunker adjustment factors (BAF) for {$country} bound vessels.",
                'url' => '#',
                'source' => 'Maritime Executive',
                'published' => now()->subHours(12)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Automated Terminals Boost Efficiency in {$country}",
                'description' => "Investment in automated guided vehicles (AGVs) and robotic cranes has significantly reduced turnaround times at key {$country} ports.",
                'url' => '#',
                'source' => 'Tech Logistics',
                'published' => now()->subHours(18)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Severe Weather Alerts Disrupt {$country} Supply Chains",
                'description' => "Meteorological agencies have issued warnings that could lead to temporary port closures and supply chain delays in {$country} over the weekend.",
                'url' => '#',
                'source' => 'Supply Chain Risk Network',
                'published' => now()->subHours(24)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "E-Commerce Boom Strains {$country} Last-Mile Delivery",
                'description' => "Record online sales volumes are stretching last-mile delivery networks in {$country} to their limits, prompting a surge in gig-economy courier hiring.",
                'url' => '#',
                'source' => 'Retail Logistics Magazine',
                'published' => now()->subDays(2)->toISOString(),
                'image' => null,
            ],
            [
                'title' => "Green Shipping Initiatives Take Center Stage in {$country}",
                'description' => "Major shipping lines servicing {$country} are accelerating their transition to alternative fuels like methanol and ammonia to meet strict new emission targets.",
                'url' => '#',
                'source' => 'Eco Freight News',
                'published' => now()->subDays(3)->toISOString(),
                'image' => null,
            ],
        ];
    }
}
