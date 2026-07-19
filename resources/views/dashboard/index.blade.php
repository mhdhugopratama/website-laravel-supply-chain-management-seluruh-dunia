@extends('layouts.app')

@section('title', __('app.dashboard.title') . ' — SupplyChainIQ')
@section('page_title', __('app.dashboard.title'))
@section('meta_description', 'Monitor global supply chain risks with real-time economic, weather, and geopolitical data.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#riskWorldMap, #weatherWorldMap, #portWorldMap { height: 360px; width: 100%; border-radius: 10px; z-index: 1; }

.map-legend {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.72rem;
    font-weight: 600;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.legend-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 3px;
}

.map-tab-btns { display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap; }

.map-tab-btn {
    padding: 5px 14px;
    border-radius: var(--r-full);
    border: 1.5px solid var(--card-border);
    background: var(--card-bg);
    color: var(--text-body);
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.map-tab-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.map-tab-btn:hover:not(.active) { background: var(--primary-lt); color: var(--primary); border-color: var(--primary); }

.map-panel { display: none; }
.map-panel.active { display: block; }

.weather-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: var(--r-full);
    font-size: 0.73rem;
    font-weight: 600;
    background: var(--bg);
    border: 1px solid var(--card-border);
    color: var(--text-body);
    white-space: nowrap;
}
</style>
@endpush

@section('content')

{{-- ── KPI STRIP ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-label">{{ __('app.dashboard.countries') }}</span>
                <div class="icon-pill icon-pill-primary"><i class="bi bi-globe2"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--primary)">{{ $countries->count() }}</div>
            <div class="kpi-delta up"><i class="bi bi-arrow-up-right"></i> {{ __('app.dashboard.track_globally') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-label">{{ __('app.dashboard.major_ports') }}</span>
                <div class="icon-pill icon-pill-teal"><i class="bi bi-anchor"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--teal)">{{ $ports->count() }}</div>
            <div class="kpi-delta flat"><i class="bi bi-dash"></i> {{ __('app.dashboard.sea_ports_mapped') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-label">{{ __('app.dashboard.weather_stations') }}</span>
                <div class="icon-pill icon-pill-orange"><i class="bi bi-cloud-sun"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--secondary)">{{ count($weatherCities) }}</div>
            <div class="kpi-delta up"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> {{ __('app.dashboard.live_readings') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-label">{{ __('app.dashboard.data_feed') }}</span>
                <div class="icon-pill icon-pill-green"><i class="bi bi-activity"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--green)">{{ __('app.dashboard.live') }}</div>
            <div class="kpi-delta up"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> {{ __('app.dashboard.realtime_feed') }}</div>
        </div>
    </div>
</div>

{{-- ── WORLD MAPS SECTION ────────────────────────────────────────────── --}}
<div class="nb-card mb-4">
    <div class="nb-card-header">
        <i class="bi bi-map-fill"></i> {{ __('app.dashboard.global_maps') }}
        <span class="ms-auto" style="font-size:0.68rem;text-transform:none;font-weight:500;color:var(--text-muted)">{{ __('app.dashboard.data_updated') }} · {{ __('app.dashboard.click_marker') }}</span>
    </div>
    <div class="nb-card-body">

        <div class="map-tab-btns" id="mapTabBtns">
            <button class="map-tab-btn active" onclick="switchMap('risk',this)">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ __('app.dashboard.risk_map') }}
            </button>
            <button class="map-tab-btn" onclick="switchMap('route',this)">
                <i class="bi bi-geo-alt-fill"></i> {{ __('app.dashboard.route_tracker') }}
            </button>
            <button class="map-tab-btn" onclick="switchMap('weather',this)">
                <i class="bi bi-cloud-lightning-rain"></i> {{ __('app.dashboard.live_weather_map') }}
            </button>
            <button class="map-tab-btn" onclick="switchMap('ports',this)">
                <i class="bi bi-anchor"></i> {{ __('app.dashboard.port_map') }}
            </button>
        </div>

        {{-- Risk Map Panel --}}
        <div class="map-panel active" id="panel-risk">
            <div id="riskWorldMap"></div>
            <div class="map-legend mt-2">
                <span><span class="legend-dot" style="background:#10b981"></span>Low Risk (0–30)</span>
                <span><span class="legend-dot" style="background:#f59e0b"></span>Medium (31–60)</span>
                <span><span class="legend-dot" style="background:#ef4444"></span>High Risk (61–100)</span>
                <span class="ms-auto" style="color:var(--text-muted);font-weight:500">{{ count($mapCountries) }} countries plotted · click any for full info</span>
            </div>
        </div>

        {{-- Route Tracker Panel --}}
        <div class="map-panel" id="panel-route">
            <div class="d-flex gap-2 mb-2 p-2 align-items-center" style="background:var(--card-bg); border-radius:8px; border:1px solid var(--card-border);">
                <div id="routeStatus" class="flex-grow-1 text-center fw-bold" style="font-size:0.85rem; color:var(--primary)">
                    <i class="bi bi-hand-index-thumb"></i> <span id="routeStatusText">{{ __('app.dashboard.step1') }}</span>
                </div>
                <button onclick="resetRoute()" class="nb-btn nb-btn-outline" style="padding:4px 12px; font-size:0.75rem">
                    <i class="bi bi-arrow-counterclockwise"></i> {{ __('app.dashboard.reset') }}
                </button>
            </div>
            <div id="routeWorldMap" style="height: 320px; width: 100%; border-radius: 10px; z-index: 1;"></div>
            <div class="map-legend mt-2">
                <span><span class="legend-dot" style="background:#3b82f6"></span>{{ __('app.dashboard.route_tracking') }}</span>
                <span class="ms-auto" style="color:var(--text-muted);font-weight:500">{{ __('app.dashboard.route_est') }}</span>
            </div>
        </div>

        {{-- Weather Map Panel --}}
        <div class="map-panel" id="panel-weather">
            <div id="weatherWorldMap"></div>
            <div class="map-legend mt-2">
                <span><span class="legend-dot" style="background:#3b82f6"></span>Cold (< 10°C)</span>
                <span><span class="legend-dot" style="background:#10b981"></span>Mild (10–25°C)</span>
                <span><span class="legend-dot" style="background:#f97316"></span>Hot (25–35°C)</span>
                <span><span class="legend-dot" style="background:#ef4444"></span>Extreme (> 35°C)</span>
                <span class="ms-auto" style="color:var(--text-muted);font-weight:500">{{ count($weatherCities) }} major cities · live from Open-Meteo</span>
            </div>
        </div>

        {{-- Port Map Panel --}}
        <div class="map-panel" id="panel-ports">
            <div id="portWorldMap"></div>
            <div class="map-legend mt-2">
                <span><span class="legend-dot" style="background:#7c3aed"></span>Sea Port</span>
                <span><span class="legend-dot" style="background:#0ea5e9"></span>Container Terminal</span>
                <span><span class="legend-dot" style="background:#f97316"></span>Dry Port</span>
                <span class="ms-auto" style="color:var(--text-muted);font-weight:500">{{ $ports->count() }} ports plotted worldwide</span>
            </div>
        </div>

    </div>
</div>

{{-- ── LIVE WEATHER STRIP ───────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Column 1: Extreme Weather --}}
    <div class="col-12 col-lg-6">
        <div class="nb-card h-100">
            <div class="nb-card-header"><i class="bi bi-thermometer-high" style="color:var(--red)"></i> {{ __('Top 12 Negara Cuaca Ekstrim') }}</div>
            <div class="nb-card-body">
                <div class="row g-2">
                    @foreach($extremeWeatherCities as $city)
                    @php
                        $icon = match(true) {
                            $city['code'] === 0         => '☀️',
                            $city['code'] <= 3          => '⛅',
                            $city['code'] <= 49         => '🌫️',
                            $city['code'] <= 59         => '🌦️',
                            $city['code'] <= 69         => '🌧️',
                            $city['code'] <= 79         => '❄️',
                            $city['code'] <= 82         => '🌧️',
                            $city['code'] <= 86         => '🌨️',
                            $city['code'] <= 99         => '⛈️',
                            default                     => '🌡️',
                        };
                        $tempColor = match(true) {
                            $city['temp'] < 10  => 'var(--teal)',
                            $city['temp'] < 25  => 'var(--green)',
                            $city['temp'] < 35  => 'var(--secondary)',
                            default             => 'var(--red)',
                        };
                        $riskBadge = $city['risk'] < 30 ? 'success' : ($city['risk'] < 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-4 col-xl-3">
                        <div class="nb-card" style="padding:10px 5px;text-align:center;height:100%;">
                            <div style="font-size:1.4rem;line-height:1">{{ $icon }}</div>
                            <div style="font-weight:700;font-size:0.75rem;margin-top:6px;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $city['name'] }}"><img src="https://flagcdn.com/w20/{{ strtolower($city['iso2']) }}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> {{ $city['name'] }}</div>
                            <div style="font-size:1.1rem;font-weight:800;color:{{ $tempColor }};margin:2px 0">{{ $city['temp'] }}°C</div>
                            <div style="font-size:0.62rem;color:var(--text-muted)">{{ $city['label'] }}</div>
                            <div class="mt-1">
                                <span class="nb-badge nb-badge-{{ $riskBadge }}" style="font-size:0.6rem">Risk {{ round($city['risk']) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Column 2: Stable Weather --}}
    <div class="col-12 col-lg-6">
        <div class="nb-card h-100">
            <div class="nb-card-header"><i class="bi bi-thermometer-sun" style="color:var(--green)"></i> {{ __('Top 12 Negara Cuaca Stabil') }}</div>
            <div class="nb-card-body">
                <div class="row g-2">
                    @foreach($stableWeatherCities as $city)
                    @php
                        $icon = match(true) {
                            $city['code'] === 0         => '☀️',
                            $city['code'] <= 3          => '⛅',
                            $city['code'] <= 49         => '🌫️',
                            $city['code'] <= 59         => '🌦️',
                            $city['code'] <= 69         => '🌧️',
                            $city['code'] <= 79         => '❄️',
                            $city['code'] <= 82         => '🌧️',
                            $city['code'] <= 86         => '🌨️',
                            $city['code'] <= 99         => '⛈️',
                            default                     => '🌡️',
                        };
                        $tempColor = match(true) {
                            $city['temp'] < 10  => 'var(--teal)',
                            $city['temp'] < 25  => 'var(--green)',
                            $city['temp'] < 35  => 'var(--secondary)',
                            default             => 'var(--red)',
                        };
                        $riskBadge = $city['risk'] < 30 ? 'success' : ($city['risk'] < 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-4 col-xl-3">
                        <div class="nb-card" style="padding:10px 5px;text-align:center;height:100%;">
                            <div style="font-size:1.4rem;line-height:1">{{ $icon }}</div>
                            <div style="font-weight:700;font-size:0.75rem;margin-top:6px;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $city['name'] }}"><img src="https://flagcdn.com/w20/{{ strtolower($city['iso2']) }}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> {{ $city['name'] }}</div>
                            <div style="font-size:1.1rem;font-weight:800;color:{{ $tempColor }};margin:2px 0">{{ $city['temp'] }}°C</div>
                            <div style="font-size:0.62rem;color:var(--text-muted)">{{ $city['label'] }}</div>
                            <div class="mt-1">
                                <span class="nb-badge nb-badge-{{ $riskBadge }}" style="font-size:0.6rem">Risk {{ round($city['risk']) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Column 1: Top 10 Lowest Risk --}}
    <div class="col-12 col-md-4">
        <div class="nb-card h-100">
            <div class="nb-card-header"><i class="bi bi-shield-fill-check" style="color:var(--nb-green)"></i> {{ __('Top 10 Negara Paling Aman') }}</div>
            <div class="nb-card-body">
                <div style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
                    @foreach($bottomRiskCountries as $bc)
                    @php
                        $badgeClass = $bc['risk'] < 30 ? 'success' : ($bc['risk'] < 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="d-flex align-items-center justify-content-between py-1" style="border-bottom: 1px solid var(--card-border)">
                        <a href="{{ route('country.show', $bc['iso3']) }}" class="d-flex align-items-center gap-2 text-decoration-none py-1">
                            @if(!empty($bc['iso2']))
                                <img src="https://flagcdn.com/w20/{{ strtolower($bc['iso2']) }}.png" width="16" alt="Flag" style="border-radius: 2px;">
                            @else
                                <span style="font-size: 1rem;">🌐</span>
                            @endif
                            <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-dark)">{{ $bc['name'] }}</span>
                        </a>
                        <span class="nb-badge nb-badge-{{ $badgeClass }}" style="font-size: 0.70rem;">
                            Risk: {{ round($bc['risk']) }}%
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Column 2: Regional Coverage --}}
    <div class="col-12 col-md-4">
        <div class="nb-card h-100">
            <div class="nb-card-header"><i class="bi bi-map"></i> {{ __('app.dashboard.regional_coverage') }}</div>
            <div class="nb-card-body">
                @php
                    $total = array_sum(array_column($regionalCoverage, 'count'));
                @endphp
                @foreach($regionalCoverage as $r)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:24px;height:24px;background:{{ $r['color'] }}20;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-geo-alt-fill" style="color:{{ $r['color'] }};font-size:0.65rem"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.76rem;font-weight:600;color:var(--text-dark)">{{ $r['name'] }}</div>
                        <div class="risk-meter mt-1">
                            <div class="risk-meter-fill" style="width:{{ $total > 0 ? round($r['count'] / $total * 100) : 0 }}%;background:{{ $r['color'] }}"></div>
                        </div>
                    </div>
                    <span style="font-size:0.76rem;font-weight:700;color:var(--text-muted)">{{ $r['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Column 3: Top 10 Highest Risk --}}
    <div class="col-12 col-md-4">
        <div class="nb-card h-100">
            <div class="nb-card-header"><i class="bi bi-exclamation-triangle-fill" style="color:var(--red)"></i> {{ __('Top 10 Negara Paling Berisiko') }}</div>
            <div class="nb-card-body">
                <div style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
                    @foreach($topRiskCountries as $tc)
                    @php
                        $badgeClass = $tc['risk'] < 30 ? 'success' : ($tc['risk'] < 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="d-flex align-items-center justify-content-between py-1" style="border-bottom: 1px solid var(--card-border)">
                        <a href="{{ route('country.show', $tc['iso3']) }}" class="d-flex align-items-center gap-2 text-decoration-none py-1">
                            @if(!empty($tc['iso2']))
                                <img src="https://flagcdn.com/w20/{{ strtolower($tc['iso2']) }}.png" width="16" alt="Flag" style="border-radius: 2px;">
                            @else
                                <span style="font-size: 1rem;">🌐</span>
                            @endif
                            <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-dark)">{{ $tc['name'] }}</span>
                        </a>
                        <span class="nb-badge nb-badge-{{ $badgeClass }}" style="font-size: 0.70rem;">
                            Risk: {{ round($tc['risk']) }}%
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── WATCHLIST ─────────────────────────────────────────────────────── --}}
@auth
@if(count($watchlist) > 0)
<div class="mb-4">
    <div class="nb-section-title"><i class="bi bi-star-fill" style="color:var(--amber)"></i> {{ __('app.dashboard.watchlist_title') }}</div>
    <div class="row g-2">
        @foreach($watchlist as $w)
        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
            <a href="{{ route('country.show', $w['iso3']) }}" class="text-decoration-none">
                <div class="nb-card text-center" style="padding:12px 8px">
                    <div style="margin-bottom: 6px;">
                        @if(!empty($w['iso2']))
                            <img src="https://flagcdn.com/w40/{{ strtolower($w['iso2']) }}.png" width="30" alt="Flag" style="border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                        @else
                            <span style="font-size:1.7rem;line-height:1">🌐</span>
                        @endif
                    </div>
                    <div style="font-weight:700;font-size:0.79rem;margin-top:6px;color:var(--text-dark)">{{ $w['name'] }}</div>
                    <span class="nb-badge nb-badge-info mt-1">{{ $w['iso3'] }}</span>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
@endauth

{{-- ── COUNTRY GRID ──────────────────────────────────────────────────── --}}
<div class="nb-section-title"><i class="bi bi-globe-americas"></i> {{ __('app.dashboard.browse_title') }}</div>
<div class="row g-2 mb-2" id="countryGrid">
    @foreach($countries->take(30) as $country)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <a href="{{ route('country.show', $country->iso3) }}" class="text-decoration-none">
            <div class="nb-card text-center fade-in-up" style="padding:10px 6px">
                <div style="margin-bottom: 6px;">
                    @if(!empty($country->iso2))
                        <img src="https://flagcdn.com/w40/{{ strtolower($country->iso2) }}.png" width="30" alt="Flag" style="border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                    @else
                        <span style="font-size:1.55rem;line-height:1">🌐</span>
                    @endif
                </div>
                <div style="font-weight:600;font-size:0.74rem;margin-top:5px;color:var(--text-dark);line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $country->name }}</div>
                <div style="font-size:0.62rem;color:var(--text-muted);font-weight:600;margin-top:2px">{{ $country->iso3 }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>
<div class="text-center mt-2 mb-2">
    <button class="nb-btn nb-btn-dark" onclick="loadMoreCountries()" id="loadMoreBtn">
        <i class="bi bi-grid-3x3-gap"></i> {{ __('app.dashboard.load_all', ['count' => $countries->count()]) }}
    </button>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const allCountries  = @json($countries);
const mapCountries  = @json($mapCountries);
const weatherCities = @json($weatherCities);
const portsData     = @json($ports);

let riskMap = null, routeMap = null, weatherMap = null, portMap = null;
let mapsInit = { risk: false, route: false, weather: false, ports: false };

function switchMap(tab, btn) {
    document.querySelectorAll('.map-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.map-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    if (btn) btn.classList.add('active');

    if (tab === 'risk'    && !mapsInit.risk)    { initRiskMap();    mapsInit.risk    = true; }
    if (tab === 'route'   && !mapsInit.route)   { initRouteMap();   mapsInit.route   = true; }
    if (tab === 'weather' && !mapsInit.weather) { initWeatherMap(); mapsInit.weather = true; }
    if (tab === 'ports'   && !mapsInit.ports)   { initPortMap();    mapsInit.ports   = true; }

    // Force map to resize correctly after tab reveal
    setTimeout(() => {
        if (tab === 'risk'    && riskMap)    riskMap.invalidateSize();
        if (tab === 'route'   && routeMap)   routeMap.invalidateSize();
        if (tab === 'weather' && weatherMap) weatherMap.invalidateSize();
        if (tab === 'ports'   && portMap)    portMap.invalidateSize();
    }, 100);
}

function riskColor(score) {
    if (score < 30) return '#10b981';
    if (score < 60) return '#f59e0b';
    return '#ef4444';
}

function tempColor(temp) {
    if (temp < 10) return '#3b82f6';
    if (temp < 25) return '#10b981';
    if (temp < 35) return '#f97316';
    return '#ef4444';
}

function tileLayer(map) {
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

function circleMarker(lat, lon, color, radius, popup, map) {
    L.circleMarker([lat, lon], {
        radius: radius,
        fillColor: color,
        color: '#fff',
        weight: 1.5,
        opacity: 1,
        fillOpacity: 0.82,
    }).bindPopup(popup).addTo(map);
}

// ── RISK MAP ──────────────────────────────────────────────────────────
function initRiskMap() {
    riskMap = L.map('riskWorldMap', { zoomControl: true }).setView([20, 10], 2);
    tileLayer(riskMap);

    mapCountries.forEach(c => {
        if (!c.lat || !c.lon) return;
        const color = riskColor(c.risk);
        const radius = 7;
        const popup = `
            <div style="font-family:'Plus Jakarta Sans',sans-serif;min-width:160px">
                <div style="font-weight:700;font-size:0.9rem;margin-bottom:4px"><img src="https://flagcdn.com/w20/${c.iso2.toLowerCase()}.png" width="18" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> ${c.name}</div>
                <div style="font-size:0.78rem;color:#64748b;margin-bottom:6px">${c.region || ''}</div>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <span style="background:${color}20;color:${color};padding:2px 8px;border-radius:999px;font-size:0.72rem;font-weight:700">
                        Risk: ${Math.round(c.risk)}
                    </span>
                    <span style="background:#f1f5f9;padding:2px 8px;border-radius:999px;font-size:0.72rem;font-weight:600;color:#475569">
                        ${c.temp}°C · ${c.label}
                    </span>
                </div>
                <div style="margin-top:8px">
                    <a href="/country/${c.iso3}" style="font-size:0.75rem;font-weight:700;color:#7c3aed;text-decoration:none">
                        View Full Profile →
                    </a>
                </div>
            </div>`;
        circleMarker(c.lat, c.lon, color, radius, popup, riskMap);
    });
}

// ── WEATHER MAP ───────────────────────────────────────────────────────
function initWeatherMap() {
    weatherMap = L.map('weatherWorldMap', { zoomControl: true }).setView([20, 10], 2);
    tileLayer(weatherMap);

    const weatherIcon = code => {
        if (code === 0) return '☀️';
        if (code <= 3)  return '⛅';
        if (code <= 49) return '🌫️';
        if (code <= 59) return '🌦️';
        if (code <= 69) return '🌧️';
        if (code <= 79) return '❄️';
        if (code <= 82) return '🌧️';
        if (code <= 86) return '🌨️';
        if (code <= 99) return '⛈️';
        return '🌡️';
    };

    weatherCities.forEach(city => {
        const color = tempColor(city.temp);
        const popup = `
            <div style="font-family:'Plus Jakarta Sans',sans-serif;min-width:170px">
                <div style="font-weight:700;font-size:0.9rem;margin-bottom:2px"><img src="https://flagcdn.com/w20/${city.iso2.toLowerCase()}.png" width="18" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> ${weatherIcon(city.code)} ${city.name}</div>
                <div style="font-size:0.74rem;color:#64748b;margin-bottom:8px">${city.country}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">
                    <div style="background:#f1f5f9;padding:5px 8px;border-radius:8px;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:${color}">${city.temp}°C</div>
                        <div style="font-size:0.65rem;color:#94a3b8">Temperature</div>
                    </div>
                    <div style="background:#f1f5f9;padding:5px 8px;border-radius:8px;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:#475569">${city.wind}</div>
                        <div style="font-size:0.65rem;color:#94a3b8">km/h Wind</div>
                    </div>
                </div>
                <div style="margin-top:6px;font-size:0.74rem;font-weight:600;color:#475569">
                    ${city.label} ${city.precip > 0 ? `· 🌧 ${city.precip}mm` : ''}
                </div>
                <div style="margin-top:4px">
                    <span style="background:${color}20;color:${color};padding:2px 8px;border-radius:999px;font-size:0.70rem;font-weight:700">
                        Weather Risk: ${Math.round(city.risk)}
                    </span>
                </div>
            </div>`;

        const divIcon = L.divIcon({
            html: `<div style="
                background:${color};
                color:#fff;
                font-family:'Plus Jakarta Sans',sans-serif;
                font-weight:800;
                font-size:0.72rem;
                padding:3px 6px;
                border-radius:8px;
                border:2px solid #fff;
                box-shadow:0 2px 6px rgba(0,0,0,0.18);
                white-space:nowrap;
                line-height:1.2;
                text-align:center;
            ">${city.temp}°C<br><span style="font-size:0.60rem;font-weight:600;opacity:0.9">${weatherIcon(city.code)}</span></div>`,
            iconSize:   null,
            iconAnchor: [20, 20],
            className:  '',
        });

        L.marker([city.lat, city.lon], { icon: divIcon })
            .bindPopup(popup)
            .addTo(weatherMap);
    });
}

// ── PORT MAP ──────────────────────────────────────────────────────────
function initPortMap() {
    portMap = L.map('portWorldMap', { zoomControl: true }).setView([20, 10], 2);
    tileLayer(portMap);

    const portColor = type => {
        if (!type) return '#7c3aed';
        const t = type.toLowerCase();
        if (t.includes('container')) return '#0ea5e9';
        if (t.includes('dry'))       return '#f97316';
        return '#7c3aed';
    };

    portsData.forEach(port => {
        if (!port.latitude || !port.longitude) return;
        const color = portColor(port.type);
        let portIso2 = '';
        if (port.country_code) {
            const match = allCountries.find(c => c.iso3 === port.country_code);
            if (match && match.iso2) portIso2 = match.iso2.toLowerCase();
        }

        const popup = `
            <div style="font-family:'Plus Jakarta Sans',sans-serif;min-width:150px">
                <div style="font-weight:700;font-size:0.88rem;margin-bottom:3px">⚓ ${port.name}</div>
                <div style="font-size:0.76rem;color:#64748b;margin-bottom:6px">
                    ${portIso2 ? `<img src="https://flagcdn.com/w20/${portIso2}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;">` : ''} 
                    ${port.country_name || port.country_code || ''}
                </div>
                ${port.un_locode ? `<span style="background:#f1f5f9;padding:2px 8px;border-radius:999px;font-size:0.70rem;font-weight:700;color:#475569">UN/LOCODE: ${port.un_locode}</span>` : ''}
                ${port.type ? `<div style="margin-top:4px;font-size:0.72rem;color:${color};font-weight:600">${port.type}</div>` : ''}
            </div>`;

        L.circleMarker([parseFloat(port.latitude), parseFloat(port.longitude)], {
            radius:      5,
            fillColor:   color,
            color:       '#fff',
            weight:      1.2,
            opacity:     1,
            fillOpacity: 0.75,
        }).bindPopup(popup).addTo(portMap);
    });
}

// ── ROUTE MAP ──────────────────────────────────────────────────────────
let routePolyline = null;
let originCountry = null;
let destCountry = null;
let routeMarkers = [];

function initRouteMap() {
    routeMap = L.map('routeWorldMap', { zoomControl: true }).setView([20, 10], 2);
    tileLayer(routeMap);

    // Plot all countries as clickable circles
    mapCountries.forEach(c => {
        if (!c.lat || !c.lon) return;
        L.circleMarker([c.lat, c.lon], {
            radius: 6,
            fillColor: '#94a3b8',
            color: '#fff',
            weight: 1.5,
            opacity: 1,
            fillOpacity: 0.5,
        })
        .bindTooltip(c.name)
        .on('click', () => handleRouteClick(c))
        .addTo(routeMap);
    });
}

function resetRoute() {
    originCountry = null;
    destCountry = null;
    if (routePolyline) routeMap.removeLayer(routePolyline);
    routeMarkers.forEach(m => routeMap.removeLayer(m));
    routeMarkers = [];
    document.getElementById('routeStatusText').innerHTML = '{{ __("app.dashboard.step1") }}';
    routeMap.closePopup();
}

function handleRouteClick(c) {
    if (!originCountry) {
        originCountry = c;
        const m = L.circleMarker([c.lat, c.lon], {radius: 8, fillColor: '#10b981', color: '#fff', weight: 2, fillOpacity: 1}).addTo(routeMap).bindPopup(`Origin: <img src="https://flagcdn.com/w20/${(c.iso2 || '').toLowerCase()}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> ${c.name}`).openPopup();
        routeMarkers.push(m);
        document.getElementById('routeStatusText').innerHTML = '{{ __("app.dashboard.step2") }}';
    } else if (!destCountry && c.iso3 !== originCountry.iso3) {
        destCountry = c;
        const m = L.circleMarker([c.lat, c.lon], {radius: 8, fillColor: '#ef4444', color: '#fff', weight: 2, fillOpacity: 1}).addTo(routeMap).bindPopup(`Destination: <img src="https://flagcdn.com/w20/${(c.iso2 || '').toLowerCase()}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:4px;"> ${c.name}`).openPopup();
        routeMarkers.push(m);
        document.getElementById('routeStatusText').innerHTML = '<span class="text-success">{{ __("app.dashboard.route_analyzed") }} ' + originCountry.iso3 + ' ➔ ' + destCountry.iso3 + '</span>';
        drawRoute();
    }
}

function drawRoute() {
    if (!originCountry || !destCountry) return;

    // Calculate simulated transit risk
    const distanceRisk = Math.min(20, Math.abs(originCountry.lat - destCountry.lat) + Math.abs(originCountry.lon - destCountry.lon) / 2);
    const averageRisk = (originCountry.risk + destCountry.risk) / 2;
    const totalRisk = Math.min(100, Math.round(averageRisk + distanceRisk));
    
    let riskLabel = totalRisk < 40 ? 'Safe Route' : (totalRisk < 70 ? 'Moderate Risk' : 'High Risk');
    let color = totalRisk < 40 ? '#10b981' : (totalRisk < 70 ? '#f59e0b' : '#ef4444');

    let latlngs = [];
    const lat1 = originCountry.lat, lon1 = originCountry.lon;
    const lat2 = destCountry.lat, lon2 = destCountry.lon;
    const offsetX = lat2 - lat1, offsetY = lon2 - lon1;
    const midX = lat1 + offsetX / 2, midY = lon1 + offsetY / 2;
    const curveFactor = 0.2;
    const cpX = midX + (offsetY * curveFactor);
    const cpY = midY - (offsetX * curveFactor);
    for (let t = 0; t <= 1; t += 0.05) {
        latlngs.push([(1-t)*(1-t)*lat1 + 2*(1-t)*t*cpX + t*t*lat2, (1-t)*(1-t)*lon1 + 2*(1-t)*t*cpY + t*t*lon2]);
    }
    latlngs.push([lat2, lon2]);

    routePolyline = L.polyline(latlngs, {color: color, weight: 4, dashArray: '10, 10', opacity: 0.9}).addTo(routeMap);
    routeMap.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });

    const compareUrl = `/compare?a=${originCountry.iso3}&b=${destCountry.iso3}`;
    
    const popupHTML = `
        <div style="font-family:'Plus Jakarta Sans',sans-serif; text-align:center;">
            <div style="font-weight:700; margin-bottom:5px">Route Analysis</div>
            <div>
                <img src="https://flagcdn.com/w20/${(originCountry.iso2 || '').toLowerCase()}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:2px;"> ${originCountry.iso3} ➔ 
                <img src="https://flagcdn.com/w20/${(destCountry.iso2 || '').toLowerCase()}.png" width="16" alt="Flag" style="border-radius:2px; vertical-align:middle; margin-right:2px;"> ${destCountry.iso3}
            </div>
            <div style="margin-top:10px;">
                <span style="background:${color}; color:#fff; padding:4px 8px; border-radius:12px; font-weight:bold; font-size:12px;">
                    Transit Risk: ${totalRisk}/100
                </span>
            </div>
            <div style="font-size:11px; margin-top:5px; color:#64748b; margin-bottom:12px;">${riskLabel}</div>
            <a href="${compareUrl}" class="nb-btn nb-btn-primary" style="padding: 4px 10px; font-size: 0.75rem; text-decoration: none; color: #ffffff !important;">
                <i class="bi bi-bar-chart-fill"></i> View Analytics
            </a>
        </div>
    `;
    
    L.popup()
        .setLatLng([(originCountry.lat + destCountry.lat)/2, (originCountry.lon + destCountry.lon)/2])
        .setContent(popupHTML)
        .openOn(routeMap);
}

// ── INIT FIRST MAP ON LOAD ────────────────────────────────────────────
window.addEventListener('load', function() {
    initRiskMap();
    mapsInit.risk = true;
});

// ── COUNTRY SEARCH ────────────────────────────────────────────────────
const searchInput = document.getElementById('countrySearch');
const dropdown    = document.getElementById('countryDropdown');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const val = this.value.trim().toLowerCase();
        if (val.length < 1) { dropdown.classList.remove('show'); return; }
        const matches = allCountries.filter(c => c.name.toLowerCase().includes(val)).slice(0, 10);
        if (!matches.length) { dropdown.classList.remove('show'); return; }
        dropdown.innerHTML = matches.map(c => {
            const flagUrl = c.iso2 ? `https://flagcdn.com/w20/${c.iso2.toLowerCase()}.png` : '';
            const flagHtml = flagUrl ? `<img src="${flagUrl}" width="16" style="border-radius:2px; vertical-align:middle; margin-right:4px;">` : '🌍';
            return `<div class="country-dropdown-item" onclick="window.location='/country/${c.iso3}'">
                <span>${flagHtml}</span>
                <span>${c.name}</span>
                <span class="ms-auto nb-badge nb-badge-info">${c.iso3}</span>
            </div>`;
        }).join('');
        dropdown.classList.add('show');
    });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.nb-search-box') && dropdown) dropdown.classList.remove('show');
});

// ── LOAD MORE COUNTRIES ───────────────────────────────────────────────
function loadMoreCountries() {
    const grid = document.getElementById('countryGrid');
    const btn  = document.getElementById('loadMoreBtn');
    allCountries.slice(30).forEach(c => {
        const d = document.createElement('div');
        d.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
        const flagUrl = c.iso2 ? `https://flagcdn.com/w40/${c.iso2.toLowerCase()}.png` : '';
        const flagHtml = flagUrl ? `<img src="${flagUrl}" width="30" alt="Flag" style="border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">` : '<span style="font-size:1.55rem;line-height:1">🌐</span>';
        d.innerHTML = `<a href="/country/${c.iso3}" class="text-decoration-none">
            <div class="nb-card text-center fade-in-up" style="padding:10px 6px">
                <div style="margin-bottom: 6px;">${flagHtml}</div>
                <div style="font-weight:600;font-size:0.74rem;margin-top:5px;color:var(--text-dark);line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${c.name}</div>
                <div style="font-size:0.62rem;color:var(--text-muted);font-weight:600;margin-top:2px">${c.iso3}</div>
            </div>
        </a>`;
        grid.appendChild(d);
    });
    btn.remove();
}
</script>
@endpush
