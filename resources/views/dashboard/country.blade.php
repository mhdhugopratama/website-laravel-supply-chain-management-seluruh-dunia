@extends('layouts.app')

@section('title', $country->name . ' - GoSupply')
@section('meta_description', 'Supply chain risk intelligence for ' . $country->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #mainMap { height: 350px; width: 100%; border-radius: 0 0 8px 8px; z-index: 1; }
</style>
@endpush

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1>
                @if(!empty($country->iso2))
                    <img src="https://flagcdn.com/h40/{{ strtolower($country->iso2) }}.png" height="30" alt="Flag" style="border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.3); vertical-align: middle; margin-right: 8px;">
                @else
                    <span style="font-size:2.2rem"><i class="bi bi-globe2"></i></span>
                @endif
                {{ $country->name }}
                <span class="nb-badge nb-badge-info ms-2">{{ $country->iso3 }}</span>
            </h1>
            <p>{{ $country->capital ? __('app.country.capital') . ': ' . $country->capital . ' · ' : '' }}{{ $country->region }} · {{ $country->subregion }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @auth
            <button class="nb-btn {{ $inWatchlist ? 'nb-btn-danger' : 'nb-btn-success' }}" id="watchlistBtn"
                onclick="toggleWatchlist({{ $country->id }}, this)">
                <i class="bi {{ $inWatchlist ? 'bi-star-fill' : 'bi-star' }}"></i>
                {{ $inWatchlist ? __('app.country.remove_watchlist') : __('app.country.add_watchlist') }}
            </button>
            @endauth
            <a href="{{ route('dashboard') }}" class="nb-btn nb-btn-outline">
                {{ __('app.country.back') }}
            </a>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-12 col-lg-4 d-flex flex-column">
            <div class="nb-card mb-3">
                <div class="nb-card-header">{{ __('app.risk.score') }}</div>
                <div class="nb-card-body text-center">
                    <div class="risk-score-display mb-2" style="color: {{ $risk['level']['color'] }}">
                        {{ $risk['score'] }}
                    </div>
                    <div class="nb-badge nb-badge-{{ $risk['level']['badge'] }} mb-3">
                        {{ $risk['level']['label'] }}
                    </div>
                    <div class="risk-meter mb-3">
                        <div class="risk-meter-fill" style="width: {{ $risk['score'] }}%; background: {{ $risk['level']['color'] }}"></div>
                    </div>
                    <div class="row g-2 text-start mt-3">
                        @foreach([
                            [__('app.risk.weather'), $risk['weather_risk'], 'bi-cloud-lightning'],
                            [__('app.risk.inflation'), $risk['inflation_risk'], 'bi-graph-up-arrow'],
                            [__('app.risk.news'), $risk['news_risk'], 'bi-newspaper'],
                            [__('app.risk.currency'), $risk['currency_risk'], 'bi-currency-exchange'],
                        ] as $r)
                        <div class="col-6">
                            <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--nb-text-muted)">
                                <i class="bi {{ $r[2] }}"></i> {{ $r[0] }}
                            </div>
                            <div class="risk-meter mt-1">
                                <div class="risk-meter-fill" style="width:{{ $r[1] }}%;background:{{ $r[1] > 60 ? 'var(--nb-red)' : ($r[1] > 30 ? 'var(--nb-orange)' : 'var(--nb-green)') }}"></div>
                            </div>
                            <div style="font-weight:800;font-size:0.9rem">{{ $r[1] }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="nb-card mb-3 flex-grow-1">
                <div class="nb-card-header">{{ __('app.country.profile') }}</div>
                <div class="nb-card-body d-flex flex-column justify-content-center">
                    <table class="nb-table">
                        <tr><td><strong>{{ __('app.country.population') }}</strong></td><td>{{ $country->population ? number_format($country->population) : __('app.country.no_data') }}</td></tr>
                        <tr><td><strong>{{ __('app.country.currency') }}</strong></td><td>{{ $country->currency_code }} {{ $country->currency_symbol }}{{ $country->currency_name ? ' (' . $country->currency_name . ')' : '' }}</td></tr>
                        <tr><td><strong>ISO2</strong></td><td>{{ $country->iso2 }}</td></tr>
                        <tr><td><strong>{{ __('app.country.region') }}</strong></td><td>{{ $country->region }}</td></tr>
                        <tr><td><strong>{{ __('app.country.subregion') }}</strong></td><td>{{ $country->subregion }}</td></tr>
                        <tr><td><strong>{{ __('app.country.area') }}</strong></td><td>{{ $country->area !== null ? number_format($country->area, $country->area < 10 ? 2 : 0) : __('app.country.no_data') }}</td></tr>
                        <tr><td><strong>{{ __('app.country.languages') }}</strong></td><td>{{ $country->languages ?? __('app.country.no_data') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="nb-card h-100">
                        <div class="nb-card-header">{{ __('app.country.weather') }}</div>
                        <div class="nb-card-body">
                            @if(isset($weatherData['error']))
                                <div class="nb-alert nb-alert-danger">{{ __('app.country.no_data') }}</div>
                            @else
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="weather-icon-large">
                                    @php
                                        $code = $weatherData['weather_code'] ?? 0;
                                        echo $code >= 95 ? '' : ($code >= 80 ? '' : ($code >= 60 ? '' : ($code >= 50 ? '' : ($code >= 3 ? '' : '️'))));
                                    @endphp
                                </div>
                                <div>
                                    <div class="nb-stat">{{ $weatherData['temperature'] }}°C</div>
                                    <div class="nb-stat-label">{{ __('app.country.temperature') }}</div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="nb-stat-label"><i class="bi bi-moisture"></i> {{ __('app.country.precipitation') }}</div>
                                    <div style="font-weight:800">{{ $weatherData['precipitation'] }} mm</div>
                                </div>
                                <div class="col-6">
                                    <div class="nb-stat-label"><i class="bi bi-wind"></i> {{ __('app.country.wind_speed') }}</div>
                                    <div style="font-weight:800">{{ $weatherData['wind_speed'] }} km/h</div>
                                </div>
                            </div>
                            @if($weatherData['wind_speed'] > 60)
                                <div class="nb-badge nb-badge-danger mt-2"> {{ __('app.country.storm_risk') }}</div>
                            @elseif($weatherData['precipitation'] > 10)
                                <div class="nb-badge nb-badge-warning mt-2"> {{ __('app.country.heavy_rain') }}</div>
                            @else
                                <div class="nb-badge nb-badge-success mt-2"> {{ __('app.country.normal') }}</div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="nb-card h-100">
                        <div class="nb-card-header">{{ __('app.country.economic') }}</div>
                        <div class="nb-card-body">
                            <div class="row g-3">
                                <div class="col-6 mb-2">
                                    <div class="nb-stat-label"><i class="bi bi-graph-up"></i> {{ __('app.country.gdp') }}</div>
                                    <div style="font-weight:800;font-size:1.1rem;margin-top:0.2rem">
                                        @if($economicData['gdp'])
                                            ${{ number_format($economicData['gdp'] / 1e9, 1) }}B
                                        @else
                                            <span style="color:var(--nb-text-muted)">{{ __('app.country.no_data') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <div class="nb-stat-label">{{ __('app.country.inflation') }}</div>
                                    <div style="font-weight:800;font-size:1.1rem;margin-top:0.2rem;color:{{ ($economicData['inflation'] ?? 0) > 10 ? 'var(--nb-red)' : (($economicData['inflation'] ?? 0) > 5 ? 'var(--nb-orange)' : 'var(--nb-green)') }}">
                                        @if(!is_null($economicData['inflation']))
                                            {{ number_format($economicData['inflation'], 2) }}%
                                        @else
                                            <span style="color:var(--nb-text-muted)">{{ __('app.country.no_data') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="nb-stat-label">{{ __('app.country.exports') }}</div>
                                    <div style="font-weight:800;font-size:1.1rem;margin-top:0.2rem">
                                        @if(!empty($economicData['exports']))
                                            ${{ number_format($economicData['exports'] / 1e9, 1) }}B
                                        @else
                                            <span style="color:var(--nb-text-muted)">{{ __('app.country.no_data') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="nb-stat-label">{{ __('app.country.imports') }}</div>
                                    <div style="font-weight:800;font-size:1.1rem;margin-top:0.2rem">
                                        @if(!empty($economicData['imports']))
                                            ${{ number_format($economicData['imports'] / 1e9, 1) }}B
                                        @else
                                            <span style="color:var(--nb-text-muted)">{{ __('app.country.no_data') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nb-card mb-3">
                <div class="nb-card-header">{{ __('app.country.map') }}</div>
                <div class="nb-card-body p-0">
                    <div class="nb-map-wrapper">
                        <div id="mainMap"></div>
                    </div>
                </div>
            </div>

        </div> <!-- close col-12 col-lg-8 -->
    </div> <!-- close row g-4 -->
    
    <!-- LATEST NEWS SECTION FULL WIDTH AT THE BOTTOM -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="nb-card">
                <div class="nb-card-header">{{ __('app.country.news') }} - {{ $country->name }}</div>
                <div class="nb-card-body">
                    <div class="mb-4">
                        <div class="nb-stat-label mb-1">{{ __('app.country.sentiment') }}</div>
                        <div class="sentiment-bar mb-2" style="height: 10px; border-radius: 4px; overflow: hidden; display: flex;">
                            <div class="sentiment-pos" style="width: {{ $newsData['positive_pct'] }}%; background: var(--nb-green);"></div>
                            <div class="sentiment-neu" style="width: {{ $newsData['neutral_pct'] }}%; background: #b8a000;"></div>
                            <div class="sentiment-neg" style="width: {{ $newsData['negative_pct'] }}%; background: var(--nb-red);"></div>
                        </div>
                        <div class="d-flex gap-3" style="font-size:0.8rem; font-weight:700;">
                            <span style="color:var(--nb-green)">● {{ __('app.news.positive_label') }} {{ $newsData['positive_pct'] }}%</span>
                            <span style="color:#b8a000">● {{ __('app.news.neutral_label') }} {{ $newsData['neutral_pct'] }}%</span>
                            <span style="color:var(--nb-red)">● {{ __('app.news.negative_label') }} {{ $newsData['negative_pct'] }}%</span>
                        </div>
                    </div>
                    
                    @if(empty($newsData['articles']))
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-journal-x" style="font-size: 2rem;"></i>
                            <p class="mt-2">{{ __('app.country.no_data') ?? 'No news articles found for this country.' }}</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach(array_slice($newsData['articles'], 0, 6) as $article)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="nb-card h-100" style="border: 1px solid var(--card-border); background: var(--bg); box-shadow: none;">
                                    <div class="nb-card-body d-flex flex-column">
                                        <a href="{{ $article['url'] }}" target="_blank" class="text-decoration-none" style="color:var(--text-dark); font-weight: 700; font-size: 0.95rem; line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $article['title'] }}
                                        </a>
                                        <p style="font-size: 0.8rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 12px; flex-grow: 1;">
                                            {{ strip_tags($article['description'] ?? '') }}
                                        </p>
                                        @php
                                            $artSent = $article['sentiment'] ?? 'Neutral';
                                            $artSentClass = $artSent === 'Positive' ? 'success' : ($artSent === 'Negative' ? 'danger' : 'secondary');
                                        @endphp
                                        <div style="margin-bottom: 8px;">
                                            <span class="nb-badge nb-badge-{{ $artSentClass }}" style="font-size:0.62rem;">{{ $artSent }} News</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-auto" style="font-size:0.75rem; font-weight:600; color:var(--text-muted);">
                                            <span><i class="bi bi-building"></i> {{ $article['source'] }}</span>
                                            <span><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div> <!-- close container-fluid px-4 -->
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('mainMap').setView([{{ $country->latitude ?? 0 }}, {{ $country->longitude ?? 0 }}], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

const ports = @json($ports);

const weatherCode = {{ $weatherData['weather_code'] ?? 0 }};
const windSpeed   = {{ $weatherData['wind_speed'] ?? 0 }};
const precip      = {{ $weatherData['precipitation'] ?? 0 }};

let markerColor = '#00E676';
if (windSpeed > 60 || weatherCode >= 95) markerColor = '#FF1744';
else if (precip > 10 || weatherCode >= 80) markerColor = '#FFE500';

const markerHtml = `<div style="width:36px;height:36px;background:${markerColor};border:3px solid #000;box-shadow:4px 4px 0 #000;display:flex;align-items:center;justify-content:center;font-size:1.1rem;border-radius:50%">
    ${weatherCode >= 95 ? '' : weatherCode >= 80 ? '' : weatherCode >= 60 ? '' : '️'}
</div>`;

const icon = L.divIcon({ html: markerHtml, className: '', iconSize: [36, 36], iconAnchor: [18, 18] });
const centerMarker = L.marker([{{ $country->latitude ?? 0 }}, {{ $country->longitude ?? 0 }}], { icon })
    .addTo(map)
    .bindPopup(`<strong>{{ $country->name }} (Capital/Center)</strong><br>{{ __('app.country.temperature') }}: {{ $weatherData['temperature'] ?? 'N/A' }}°C<br>{{ __('app.country.wind_speed') }}: {{ $weatherData['wind_speed'] ?? 0 }} km/h`)
    .openPopup();

const portMarkers = [];
if (ports && ports.length > 0) {
    ports.forEach(p => {
        const pm = L.circleMarker([p.latitude, p.longitude], {
            radius: 7,
            fillColor: '#3b82f6',
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        })
        .addTo(map)
        .bindPopup(`<strong><svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: -0.125em;" xmlns="http://www.w3.org/2000/svg"><path d="M8 1a2 2 0 1 0 0 4 2 2 0 0 0 0-4M7 3a1 1 0 1 1 2 0 1 1 0 0 1-2 0"/><path d="M7.5 5h1v6h-1zm1 7h-1v2a.5.5 0 0 0 1 0z"/><path d="M8 15A6.5 6.5 0 0 1 1.5 8.5a.5.5 0 0 0-1 0 7.5 7.5 0 0 0 15 0 .5.5 0 0 0-1 0A6.5 6.5 0 0 1 8 15"/></svg> ${p.name}</strong><br>UN/LOCODE: ${p.un_locode || 'N/A'}<br>Type: ${p.type || 'Sea Port'}`);
        portMarkers.push(pm);
    });

    const group = new L.featureGroup([centerMarker, ...portMarkers]);
    map.fitBounds(group.getBounds().pad(0.15));
}

@auth
function toggleWatchlist(countryId, btn) {
    fetch('/watchlist/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ country_id: countryId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.action === 'added') {
            btn.className = 'nb-btn nb-btn-danger';
            btn.innerHTML = '<i class="bi bi-star-fill"></i> {{ __("app.country.remove_watchlist") }}';
        } else {
            btn.className = 'nb-btn nb-btn-success';
            btn.innerHTML = '<i class="bi bi-star"></i> {{ __("app.country.add_watchlist") }}';
        }
    });
}
@endauth
</script>
@endpush
