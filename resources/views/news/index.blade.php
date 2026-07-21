@extends('layouts.app')
@section('title', __('app.news.title') . ' | GoSupply')
@section('meta_description', 'Real-time logistics, trade, and shipping intelligence with lexicon-based sentiment analysis.')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="color: var(--text-dark);">{{ __('app.news.global_trade_intel') }}</h1>
            <p style="color: var(--text-muted); margin-bottom:0;">{{ __('app.news.global_trade_desc') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="?q={{ urlencode($query) }}&refresh=1" class="nb-btn nb-btn-outline" style="font-size:0.78rem">
                {{ __('Refresh Live News') }}
            </a>
        </div>
    </div>

    <!-- LIVE STATS ROW -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.70rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-newspaper" style="color:var(--primary)"></i> Total Articles</div>
                <div style="font-size:2rem; font-weight:900; color:var(--text-dark); margin-top:4px;">{{ count($data['articles']) }}</div>
                <div style="font-size:0.70rem; color:var(--text-muted);">Fetched in real-time</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.70rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-emoji-smile" style="color:var(--nb-green)"></i> Positive Words</div>
                <div style="font-size:2rem; font-weight:900; color:var(--nb-green); margin-top:4px;">{{ $data['pos_count'] ?? 0 }}</div>
                <div style="font-size:0.70rem; color:var(--text-muted);">Detected via lexicon</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.70rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-emoji-frown" style="color:var(--nb-red)"></i> Negative Words</div>
                <div style="font-size:2rem; font-weight:900; color:var(--nb-red); margin-top:4px;">{{ $data['neg_count'] ?? 0 }}</div>
                <div style="font-size:0.70rem; color:var(--text-muted);">Risk-related keywords</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.70rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-shield-fill-check" style="color:var(--primary)"></i> Overall Sentiment</div>
                @php
                    $sentColor = ($data['sentiment'] ?? 'Neutral') === 'Positive' ? 'var(--nb-green)' : (($data['sentiment'] ?? 'Neutral') === 'Negative' ? 'var(--nb-red)' : 'var(--nb-orange)');
                    $sentIcon = ($data['sentiment'] ?? 'Neutral') === 'Positive' ? '' : (($data['sentiment'] ?? 'Neutral') === 'Negative' ? '' : '');
                @endphp
                <div style="font-size:1.5rem; font-weight:900; color:{{ $sentColor }}; margin-top:4px;">{{ $sentIcon }} {{ $data['sentiment'] ?? 'Neutral' }}</div>
                <div style="font-size:0.70rem; color:var(--text-muted);">Market outlook</div>
            </div>
        </div>
    </div>

    <!-- Controls and Sentiment Row -->
    <div class="row g-4 mb-4">
        <!-- Search and Filter Panel -->
        <div class="col-12 col-xl-6">
            <div class="nb-card h-100">
                <div class="nb-card-header">{{ __('app.news.intel_filters') }}</div>
                <div class="nb-card-body">
                    <form method="GET" action="{{ route('news.index') }}" class="d-flex flex-column justify-content-center h-100" style="min-height: 120px;">
                        <div class="d-flex gap-2 mb-3">
                            <input type="text" name="q" class="nb-select" style="flex:1;"
                                placeholder="{{ __('app.news.search_placeholder') }}" value="{{ $query }}">
                            <button type="submit" class="nb-btn nb-btn-primary">{{ __('app.news.analyze_btn') }}</button>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span style="color:var(--text-muted); font-size:0.78rem; font-weight:600;">{{ __('app.news.categories') }}</span>
                            @foreach([
                                'Logistics' => 'logistics', 
                                'Trade'     => 'trade', 
                                'Shipping'  => 'shipping', 
                                'Economy'   => 'economy',
                                'Port Congestion' => 'port congestion delay',
                                'Tariff & Sanctions' => 'tariff sanctions trade war',
                            ] as $label => $preset)
                                <a href="?q={{ urlencode($preset) }}" class="nb-badge nb-badge-info" style="cursor:pointer; text-decoration:none; font-size:0.72rem;">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sentiment Analysis Panel -->
        <div class="col-12 col-xl-6">
            <div class="nb-card h-100">
                <div class="nb-card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-activity" style="color:var(--nb-red)"></i> {{ __('app.news.market_sentiment') }}</span>
                    <span class="nb-badge {{ ($data['sentiment'] ?? 'Neutral') === 'Positive' ? 'nb-badge-success' : (($data['sentiment'] ?? 'Neutral') === 'Negative' ? 'nb-badge-danger' : 'nb-badge-warning') }}" style="font-size:0.72rem;">
                        Sentiment: {{ $data['sentiment'] ?? 'Neutral' }}
                    </span>
                </div>
                <div class="nb-card-body">
                    <div class="d-flex rounded overflow-hidden mb-3" style="height: 22px; background: rgba(255,255,255,0.05); border: 1px solid var(--card-border);">
                        <div style="width:{{ $data['positive_pct'] }}%; background: var(--nb-green); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; color: #fff; transition: width 1s ease;">
                            {{ $data['positive_pct'] > 8 ? $data['positive_pct'].'%' : '' }}
                        </div>
                        <div style="width:{{ $data['neutral_pct'] }}%; background: var(--nb-orange); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; color: #fff; transition: width 1s ease;">
                            {{ $data['neutral_pct'] > 8 ? $data['neutral_pct'].'%' : '' }}
                        </div>
                        <div style="width:{{ $data['negative_pct'] }}%; background: var(--nb-red); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; color: #fff; transition: width 1s ease;">
                            {{ $data['negative_pct'] > 8 ? $data['negative_pct'].'%' : '' }}
                        </div>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div style="font-size:1.3rem; font-weight:900; color:var(--nb-green);">{{ $data['positive_pct'] }}%</div>
                            <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('app.news.pos_growth') }}</div>
                            <div style="font-size:0.65rem; color:var(--text-muted);">({{ $data['pos_count'] ?? 0 }} words)</div>
                        </div>
                        <div class="col-4">
                            <div style="font-size:1.3rem; font-weight:900; color:var(--nb-orange);">{{ $data['neutral_pct'] }}%</div>
                            <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('app.news.neutral') }}</div>
                        </div>
                        <div class="col-4">
                            <div style="font-size:1.3rem; font-weight:900; color:var(--nb-red);">{{ $data['negative_pct'] }}%</div>
                            <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">{{ __('app.news.risk_neg') }}</div>
                            <div style="font-size:0.65rem; color:var(--text-muted);">({{ $data['neg_count'] ?? 0 }} words)</div>
                        </div>
                    </div>

                    <!-- Sentiment Verdict -->
                    <div style="background: rgba(255,255,255,0.02); padding: 10px; border-radius: 8px; border: 1px solid var(--card-border); margin-top: 14px;">
                        <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;"> Sentiment Insight</div>
                        <span style="font-size: 0.82rem; color: var(--text-dark); line-height: 1.5;">
                            @if(($data['sentiment'] ?? 'Neutral') === 'Positive')
                                Overall market outlook is <strong>positive</strong>. News reports indicate growth, expansion, and stability in global logistics channels. Supply chain operators can expect smooth operations.
                            @elseif(($data['sentiment'] ?? 'Neutral') === 'Negative')
                                Overall market outlook is <strong>negative</strong>. Elevated mentions of disruptions, delays, and risk factors suggest heightened caution for shipments. Consider diversifying routes.
                            @else
                                Market sentiment is <strong>neutral</strong>. Mixed signals across logistics and trade news. Standard monitoring procedures are recommended.
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="nb-card-header mb-3" style="background: transparent; padding: 0; border: none; font-size: 0.95rem;">
        {{ __('app.news.intel_briefs') }} ({{ count($data['articles']) }})
    </div>
    
    <div class="row g-3">
        @forelse($data['articles'] as $article)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="nb-card h-100 d-flex flex-column position-relative overflow-hidden" style="transition: transform 0.2s, box-shadow 0.2s;">
                
                <!-- Card Image -->
                @if(!empty($article['image']))
                    <div style="height: 160px; overflow: hidden;">
                        <img src="{{ $article['image'] }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;" alt="Article Image" onerror="this.parentElement.innerHTML='<div style=\'height:100%;background:linear-gradient(135deg,rgba(99,102,241,0.15),rgba(14,165,233,0.08));display:flex;align-items:center;justify-content:center\'><i class=\'bi bi-newspaper\' style=\'font-size:2.5rem;color:var(--primary);opacity:0.4\'></i></div>'">
                    </div>
                @else
                    <div style="height: 160px; background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(14,165,233,0.06)); display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-newspaper" style="font-size: 2.5rem; color: var(--primary); opacity: 0.4;"></i>
                    </div>
                @endif
                
                <div class="nb-card-body d-flex flex-column flex-grow-1" style="padding: 24px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        @php
                            $artSent = $article['sentiment'] ?? 'Neutral';
                            $artSentClass = $artSent === 'Positive' ? 'success' : ($artSent === 'Negative' ? 'danger' : 'secondary');
                        @endphp
                        <span class="nb-badge nb-badge-{{ $artSentClass }}" style="font-size:0.62rem;">
                            {{ $artSent }} News
                        </span>
                        
                        <span class="nb-badge nb-badge-info" style="font-size:0.62rem;">
                            {{ Str::limit($article['source'] ?? 'Unknown', 18) }}
                        </span>
                    </div>
                    
                    <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); margin-bottom:6px;">
                        <i class="bi bi-clock-history"></i> 
                        @if(!empty($article['published']))
                            {{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}
                        @else
                            Recent
                        @endif
                    </div>
                    
                    <h6 style="font-weight: 800; line-height: 1.35; color: var(--text-dark); margin-bottom: 8px; font-size: 0.88rem;">
                        {{ Str::limit($article['title'], 85) }}
                    </h6>
                    
                    <p style="font-size:0.78rem; color:var(--text-muted); line-height:1.5; flex-grow:1; margin-bottom: 12px;">
                        {{ Str::limit($article['description'] ?? 'No description provided for this intelligence report.', 130) }}
                    </p>
                    
                    <!-- Explicit Button to Source -->
                    <a href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer" 
                       class="nb-btn nb-btn-outline" style="font-size:0.76rem; justify-content:center; width:100%;">
                        {{ __('app.news.read_full') }} </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="nb-card text-center" style="padding: 40px;">
                <i class="bi bi-search" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                <h4 style="font-weight: 800; color: var(--text-dark);">{{ __('app.news.no_intel') }}</h4>
                <p style="color: var(--text-muted);">{{ __('app.news.try_adjusting') }}</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Supply Chain Risk Digest -->
    @if(count($data['articles']) > 0)
    <div class="nb-card mt-4">
        <div class="nb-card-header" style="background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(239,68,68,0.03));">
            Supply Chain Risk Digest | Based on {{ count($data['articles']) }} Live Articles
        </div>
        <div class="nb-card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4" style="border-right: 1px solid var(--card-border);">
                    <h6 style="font-weight: 800; color: var(--text-dark); margin-bottom: 10px;"> Threat Level Assessment</h6>
                    @php
                        $negPct = $data['negative_pct'] ?? 0;
                        $threatLevel = $negPct > 60 ? 'CRITICAL' : ($negPct > 40 ? 'HIGH' : ($negPct > 25 ? 'MODERATE' : 'LOW'));
                        $threatColor = $negPct > 60 ? 'var(--nb-red)' : ($negPct > 40 ? 'var(--nb-orange)' : ($negPct > 25 ? 'var(--nb-orange)' : 'var(--nb-green)'));
                        $threatBadge = $negPct > 40 ? 'danger' : ($negPct > 25 ? 'warning' : 'success');
                    @endphp
                    <div style="text-align: center; margin: 15px 0;">
                        <div style="font-size: 2.5rem; font-weight: 900; color: {{ $threatColor }};">{{ $threatLevel }}</div>
                        <span class="nb-badge nb-badge-{{ $threatBadge }}" style="font-size: 0.75rem; padding: 4px 14px;">
                            Negative Signal: {{ round($negPct) }}%
                        </span>
                    </div>
                    <div class="risk-meter mt-2"><div class="risk-meter-fill" style="width:{{ $negPct }}%; background:{{ $threatColor }}"></div></div>
                </div>
                <div class="col-12 col-md-4" style="border-right: 1px solid var(--card-border);">
                    <h6 style="font-weight: 800; color: var(--text-dark); margin-bottom: 10px;"> Top Sources Analyzed</h6>
                    @php
                        $sources = collect($data['articles'])->pluck('source')->filter()->countBy()->sortDesc()->take(5);
                    @endphp
                    @foreach($sources as $sourceName => $count)
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom: 1px solid var(--card-border); font-size: 0.82rem;">
                        <span style="font-weight: 600; color: var(--text-dark);"><i class="bi bi-broadcast" style="color:var(--primary); font-size:0.72rem;"></i> {{ Str::limit($sourceName, 22) }}</span>
                        <span class="nb-badge nb-badge-info" style="font-size:0.65rem;">{{ $count }} {{ $count > 1 ? 'articles' : 'article' }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="col-12 col-md-4">
                    <h6 style="font-weight: 800; color: var(--text-dark); margin-bottom: 10px;"> Actionable Recommendation</h6>
                    <p style="font-size: 0.84rem; line-height: 1.6; color: var(--text-body);">
                        @if($negPct > 50)
                            <strong>️ High Alert:</strong> Current media coverage contains a significant proportion of disruption signals. We recommend re-evaluating existing routes and activating contingency logistics plans for affected corridors.
                        @elseif($negPct > 30)
                            <strong>🟡 Moderate Vigilance:</strong> Some disruption keywords detected. Monitor closely for further developments regarding tariffs, port delays, or labor disputes.
                        @else
                            <strong> Stable Outlook:</strong> Current news landscape shows a favorable balance of growth signals versus risk indicators. Standard logistics procedures may continue.
                        @endif
                    </p>
                    <div style="font-size:0.72rem; color:var(--text-muted); margin-top:8px;">
                        <i class="bi bi-clock"></i> Analysis refreshed {{ $data['from_cache'] ? 'from optimized cache' : 'from live API' }} · Query: <strong>"{{ $query }}"</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
