@extends('layouts.app')
@section('title', __('app.news.title') . ' — SupplyChainIQ')
@section('meta_description', 'Real-time logistics, trade, and shipping news with lexicon-based sentiment analysis.')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1><i class="bi bi-newspaper"></i> {{ __('app.news.title') }}</h1>
        <p>{{ __('app.news.subtitle') }}</p>
    </div>
</div>

<div class="container-fluid px-4">
    <form method="GET" action="{{ route('news.index') }}" class="mb-4">
        <div class="nb-card">
            <div class="nb-card-body d-flex gap-2 flex-wrap align-items-center">
                <input type="text" name="q" class="nb-input" style="max-width:350px"
                    placeholder="{{ __('app.news.search_placeholder') }}" value="{{ $query }}">
                <button type="submit" class="nb-btn nb-btn-primary"><i class="bi bi-search"></i> Search</button>
                @foreach(['logistics shipping trade', 'global economy inflation', 'port congestion', 'supply chain disruption'] as $preset)
                    <a href="?q={{ urlencode($preset) }}" class="nb-btn nb-btn-outline btn-sm">{{ $preset }}</a>
                @endforeach
                <span class="ms-auto nb-badge {{ $data['from_cache'] ? 'nb-badge-info' : 'nb-badge-success' }}">
                    {{ $data['from_cache'] ? '📦 ' . __('app.news.from_cache') : '🔴 ' . __('app.news.live_data') }}
                </span>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-8">
            <div class="nb-card">
                <div class="nb-card-header"><i class="bi bi-graph-up"></i> {{ __('app.news.sentiment') }}</div>
                <div class="nb-card-body">
                    <div class="sentiment-bar mb-3" style="height:50px">
                        <div class="sentiment-pos" style="width:{{ $data['positive_pct'] }}%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem">
                            {{ $data['positive_pct'] > 8 ? $data['positive_pct'].'%' : '' }}
                        </div>
                        <div class="sentiment-neu" style="width:{{ $data['neutral_pct'] }}%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem">
                            {{ $data['neutral_pct'] > 8 ? $data['neutral_pct'].'%' : '' }}
                        </div>
                        <div class="sentiment-neg" style="width:{{ $data['negative_pct'] }}%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;color:#fff">
                            {{ $data['negative_pct'] > 8 ? $data['negative_pct'].'%' : '' }}
                        </div>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div style="font-size:1.5rem;font-weight:900;color:var(--nb-green)">{{ $data['positive_pct'] }}%</div>
                            <div class="nb-stat-label">{{ __('app.news.positive_label') }}</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:1.5rem;font-weight:900;color:#b8a000">{{ $data['neutral_pct'] }}%</div>
                            <div class="nb-stat-label">{{ __('app.news.neutral_label') }}</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:1.5rem;font-weight:900;color:var(--nb-red)">{{ $data['negative_pct'] }}%</div>
                            <div class="nb-stat-label">{{ __('app.news.negative_label') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="nb-card h-100">
                <div class="nb-card-header"><i class="bi bi-info-circle"></i> {{ __('app.news.about') }}</div>
                <div class="nb-card-body" style="font-size:0.82rem">
                    <p>{{ __('app.news.about_text') }}</p>
                    <div class="mb-1"><span class="nb-badge nb-badge-success me-1">{{ __('app.news.positive_label') }}</span> growth, profit, stable, improve...</div>
                    <div class="mb-1"><span class="nb-badge nb-badge-danger me-1">{{ __('app.news.negative_label') }}</span> crisis, delay, war, disaster...</div>
                    <div><span class="nb-badge nb-badge-warning me-1">{{ __('app.news.neutral_label') }}</span> remaining words</div>
                </div>
            </div>
        </div>
    </div>

    <div class="nb-section-title"><i class="bi bi-rss"></i> {{ __('app.news.articles_count', ['count' => count($data['articles'])]) }}</div>
    <div class="row g-3">
        @forelse($data['articles'] as $article)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="nb-news-card h-100">
                @if(!empty($article['image']))
                    <img src="{{ $article['image'] }}" alt="Article image"
                        style="width:100%;height:160px;object-fit:cover;border-bottom:2px solid #000;margin-bottom:0.8rem"
                        onerror="this.style.display='none'">
                @endif
                <div class="nb-news-title mb-2">
                    <a href="{{ $article['url'] }}" target="_blank" rel="noopener"
                        style="color:var(--nb-text);text-decoration:none">
                        {{ $article['title'] }}
                    </a>
                </div>
                <p style="font-size:0.82rem;color:var(--nb-text-muted);line-height:1.5">
                    {{ Str::limit($article['description'] ?? '', 120) }}
                </p>
                <div class="nb-news-meta mt-auto">
                    <i class="bi bi-building"></i> {{ $article['source'] ?? 'N/A' }} ·
                    <i class="bi bi-clock"></i>
                    @if(!empty($article['published']))
                        {{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
        @empty
            <div class="col-12">
                <div class="nb-card text-center py-5">
                    <div style="font-size:3rem">📭</div>
                    <h3 class="mt-3" style="font-weight:900">No articles found</h3>
                    <p style="color:var(--nb-text-muted)">Add your GNews API key in <code>.env</code> or try a different search term.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
