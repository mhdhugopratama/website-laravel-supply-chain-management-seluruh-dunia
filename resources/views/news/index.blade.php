@extends('layouts.app')
@section('title', __('app.news.title') . ' — SupplyChainIQ')
@section('meta_description', 'Real-time logistics, trade, and shipping intelligence with lexicon-based sentiment analysis.')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="color: var(--nb-text);"><i class="bi bi-globe-americas text-primary"></i> {{ __('app.news.global_trade_intel') }}</h1>
            <p class="text-muted mb-0">{{ __('app.news.global_trade_desc') }}</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge {{ $data['from_cache'] ? 'bg-secondary' : 'bg-success' }} bg-opacity-75 p-2 rounded-pill shadow-sm" style="backdrop-filter: blur(5px);">
                @if($data['from_cache'])
                    <i class="bi bi-archive-fill"></i> {{ __('app.news.cached_result') }}
                @else
                    <i class="bi bi-record-circle text-danger"></i> {{ __('app.news.live_stream') }}
                @endif
            </span>
        </div>
    </div>

    <!-- Controls and Sentiment Row -->
    <div class="row g-4 mb-4">
        <!-- Search and Filter Panel -->
        <div class="col-12 col-xl-7">
            <div class="nb-card h-100 p-4 border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border-radius: 16px;">
                <h5 class="fw-bold mb-3"><i class="bi bi-funnel-fill text-primary"></i> {{ __('app.news.intel_filters') }}</h5>
                <form method="GET" action="{{ route('news.index') }}">
                    <div class="input-group mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-0 py-3" 
                            placeholder="{{ __('app.news.search_placeholder') }}" value="{{ $query }}">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">{{ __('app.news.analyze_btn') }}</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="text-muted small me-2 mt-1">{{ __('app.news.categories') }}</span>
                        @foreach([
                            'Logistics' => 'logistics', 
                            'Trade'     => 'trade', 
                            'Shipping'  => 'shipping', 
                            'Economy'   => 'economy'
                        ] as $label => $preset)
                            <a href="?q={{ urlencode($preset) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" style="font-size: 0.8rem; font-weight: 500;">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

        <!-- Sentiment Analysis Panel -->
        <div class="col-12 col-xl-5">
            <div class="nb-card h-100 p-4 border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border-radius: 16px;">
                <h5 class="fw-bold mb-3"><i class="bi bi-activity text-danger"></i> {{ __('app.news.market_sentiment') }}</h5>
                <div class="sentiment-bar mb-3 shadow-sm rounded-pill overflow-hidden d-flex" style="height:25px; background: rgba(0,0,0,0.05);">
                    <div class="bg-success text-white text-center fw-bold" style="width:{{ $data['positive_pct'] }}%; font-size: 0.75rem; line-height: 25px; transition: width 1s ease;">
                        {{ $data['positive_pct'] > 5 ? $data['positive_pct'].'%' : '' }}
                    </div>
                    <div class="bg-warning text-dark text-center fw-bold" style="width:{{ $data['neutral_pct'] }}%; font-size: 0.75rem; line-height: 25px; transition: width 1s ease;">
                        {{ $data['neutral_pct'] > 5 ? $data['neutral_pct'].'%' : '' }}
                    </div>
                    <div class="bg-danger text-white text-center fw-bold" style="width:{{ $data['negative_pct'] }}%; font-size: 0.75rem; line-height: 25px; transition: width 1s ease;">
                        {{ $data['negative_pct'] > 5 ? $data['negative_pct'].'%' : '' }}
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-success">{{ $data['positive_pct'] }}%</div>
                        <div class="text-muted small text-uppercase fw-bold">{{ __('app.news.pos_growth') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-warning">{{ $data['neutral_pct'] }}%</div>
                        <div class="text-muted small text-uppercase fw-bold">{{ __('app.news.neutral') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-danger">{{ $data['negative_pct'] }}%</div>
                        <div class="text-muted small text-uppercase fw-bold">{{ __('app.news.risk_neg') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-file-earmark-text text-primary"></i> {{ __('app.news.intel_briefs') }} ({{ count($data['articles']) }})</h5>
    
    <div class="row g-4">
        @forelse($data['articles'] as $article)
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="nb-card h-100 d-flex flex-column border-0 shadow-sm position-relative overflow-hidden" 
                 style="background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px); border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                
                <!-- Card Image -->
                @if(!empty($article['image']))
                    <div style="height: 180px; overflow: hidden;">
                        <img src="{{ $article['image'] }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;" alt="Article Image" onerror="this.style.display='none'">
                    </div>
                @else
                    <div style="height: 180px; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-info-rgb), 0.1)); display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-newspaper text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                @endif
                
                <!-- Source Badge overlay -->
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge bg-dark bg-opacity-75 shadow-sm rounded-pill px-3 py-2" style="backdrop-filter: blur(4px);">
                        <i class="bi bi-building"></i> {{ Str::limit($article['source'] ?? 'Unknown', 15) }}
                    </span>
                </div>

                <div class="p-4 d-flex flex-column flex-grow-1">
                    <div class="text-muted small fw-bold mb-2">
                        <i class="bi bi-clock-history"></i> 
                        @if(!empty($article['published']))
                            {{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}
                        @else
                            Recent
                        @endif
                    </div>
                    
                    <h6 class="fw-bold mb-3" style="line-height: 1.4;">
                        {{ Str::limit($article['title'], 80) }}
                    </h6>
                    
                    <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                        {{ Str::limit($article['description'] ?? 'No description provided for this intelligence report.', 120) }}
                    </p>
                    
                    <!-- Explicit Button to Source -->
                    <a href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer" 
                       class="btn btn-outline-primary w-100 fw-bold rounded-pill shadow-sm" style="transition: all 0.2s;">
                        {{ __('app.news.read_full') }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="nb-card p-5 text-center border-0 shadow-sm rounded-4" style="background: rgba(255,255,255,0.5); backdrop-filter: blur(10px);">
                <i class="bi bi-search text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="fw-bold">{{ __('app.news.no_intel') }}</h4>
                <p class="text-muted">{{ __('app.news.try_adjusting') }}</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
