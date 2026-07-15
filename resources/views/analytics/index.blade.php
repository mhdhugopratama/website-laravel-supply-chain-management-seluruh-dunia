@extends('layouts.app')
@section('title', __('app.analytics.title') . ' — SupplyChainIQ')
@section('meta_description', 'Interactive analytics with GDP, inflation, currency, and risk trend charts for any country.')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1><i class="bi bi-bar-chart-fill"></i> {{ __('app.analytics.title') }}</h1>
        <p>{{ __('app.analytics.subtitle') }}</p>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="nb-card mb-4">
        <div class="nb-card-body d-flex gap-3 align-items-center flex-wrap">
            <label style="font-weight:700;font-size:0.85rem;white-space:nowrap">{{ __('app.analytics.select') }}</label>
            <select id="analyticsCountry" class="nb-select" style="max-width:350px">
                <option value="">{{ __('app.analytics.placeholder') }}</option>
                @foreach($countries as $c)
                    <option value="{{ $c->iso3 }}">{{ $c->flag_emoji }} {{ $c->name }}</option>
                @endforeach
            </select>
            <button class="nb-btn nb-btn-primary" onclick="loadAnalytics()">
                <i class="bi bi-graph-up-arrow"></i> {{ __('app.analytics.btn_load') }}
            </button>
        </div>
    </div>

    <div id="analyticsLoading" class="nb-loading" style="display:none">
        <div class="nb-spinner"></div> {{ __('app.analytics.loading') }}
    </div>

    <div id="analyticsContent" style="display:none">
        <div class="row g-3 mb-4" id="analyticsStats"></div>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="chart-wrapper nb-card">
                    <div class="nb-card-header"><i class="bi bi-graph-up"></i> {{ __('app.analytics.risk_chart') }}</div>
                    <div class="nb-card-body">
                        <canvas id="riskChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="chart-wrapper nb-card">
                    <div class="nb-card-header"><i class="bi bi-pie-chart"></i> {{ __('app.analytics.pie_chart') }}</div>
                    <div class="nb-card-body">
                        <canvas id="riskPieChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="chart-wrapper nb-card">
                    <div class="nb-card-header"><i class="bi bi-thermometer"></i> {{ __('app.analytics.weather_chart') }}</div>
                    <div class="nb-card-body">
                        <canvas id="weatherChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="chart-wrapper nb-card">
                    <div class="nb-card-header"><i class="bi bi-bank"></i> {{ __('app.analytics.econ_chart') }}</div>
                    <div class="nb-card-body">
                        <canvas id="econChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const charts = {};

function destroyAll() {
    Object.values(charts).forEach(c => { if (c) c.destroy(); });
}

function loadAnalytics() {
    const iso3 = document.getElementById('analyticsCountry').value;
    if (!iso3) return;

    document.getElementById('analyticsLoading').style.display = 'flex';
    document.getElementById('analyticsContent').style.display = 'none';

    fetch(`/analytics/data/${iso3}`)
        .then(r => r.json())
        .then(data => {
            destroyAll();
            document.getElementById('analyticsLoading').style.display = 'none';
            document.getElementById('analyticsContent').style.display = 'block';

            const c = data.country;
            const w = data.weather;
            const e = data.economic;
            const r = data.risk;

            // Translate badge label dynamic
            let badgeLabel = r.level.label;
            if (r.level.badge === 'success') badgeLabel = "{{ __('app.risk.low') }}";
            else if (r.level.badge === 'warning') badgeLabel = "{{ __('app.risk.medium') }}";
            else badgeLabel = "{{ __('app.risk.high') }}";

            document.getElementById('analyticsStats').innerHTML = `
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">{{ __('app.risk.score') }}</div>
                    <div class="nb-admin-stat-number" style="color:${r.level.color}">${r.score}</div>
                    <div class="nb-badge nb-badge-${r.level.badge}">${badgeLabel}</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">{{ __('app.country.temperature') }}</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-cyan)">${w.temperature ?? 'N/A'}°C</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">{{ __('app.country.gdp') }} (Billion)</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-green)">
                        ${e.gdp ? '$' + (e.gdp / 1e9).toFixed(1) + 'B' : 'N/A'}
                    </div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">{{ __('app.country.inflation') }}</div>
                    <div class="nb-admin-stat-number" style="color:${(e.inflation||0)>10?'var(--nb-red)':'var(--nb-orange)'}">
                        ${e.inflation !== null ? e.inflation.toFixed(2) + '%' : 'N/A'}
                    </div>
                </div></div>
            `;

            charts.risk = new Chart(document.getElementById('riskChart'), {
                type: 'bar',
                data: {
                    labels: [
                        '{{ __("app.risk.weather") }}',
                        '{{ __("app.risk.inflation") }}',
                        '{{ __("app.risk.news") }}',
                        '{{ __("app.risk.currency") }}',
                        '{{ __("app.risk.overall") }}'
                    ],
                    datasets: [{
                        label: '{{ __("app.risk.score") }}',
                        data: [r.weather_risk, r.inflation_risk, r.news_risk, r.currency_risk, r.score],
                        backgroundColor: ['#00E5FF','#FF6D00','#FF2D78','#7C4DFF','#FFE500'],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100 } } }
            });

            charts.pie = new Chart(document.getElementById('riskPieChart'), {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("app.risk.weather") }} (30%)',
                        '{{ __("app.risk.inflation") }} (20%)',
                        '{{ __("app.risk.news") }} (40%)',
                        '{{ __("app.risk.currency") }} (10%)'
                    ],
                    datasets: [{
                        data: [
                            (r.weather_risk * 0.30).toFixed(2),
                            (r.inflation_risk * 0.20).toFixed(2),
                            (r.news_risk * 0.40).toFixed(2),
                            (r.currency_risk * 0.10).toFixed(2)
                        ],
                        backgroundColor: ['#00E5FF','#FF6D00','#FF2D78','#7C4DFF'],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, plugins: { legend: { labels: { font: { family: 'Poppins', weight: '700' } } } } }
            });

            charts.weather = new Chart(document.getElementById('weatherChart'), {
                type: 'bar',
                data: {
                    labels: ['{{ __("app.country.temperature") }} (°C)', '{{ __("app.country.precipitation") }} (mm)', '{{ __("app.country.wind_speed") }} (km/h)'],
                    datasets: [{
                        label: 'Weather Data',
                        data: [w.temperature || 0, w.precipitation || 0, w.wind_speed || 0],
                        backgroundColor: ['#00E676','#00E5FF','#FFE500'],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            charts.econ = new Chart(document.getElementById('econChart'), {
                type: 'bar',
                data: {
                    labels: ['{{ __("app.country.inflation") }} (%)', '{{ __("app.country.gdp") }} (Billion USD)'],
                    datasets: [{
                        label: 'Economic Indicators',
                        data: [e.inflation || 0, e.gdp ? (e.gdp / 1e9).toFixed(2) : 0],
                        backgroundColor: ['#FF6D00', '#7C4DFF'],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        })
        .catch(() => {
            document.getElementById('analyticsLoading').style.display = 'none';
        });
}
</script>
@endpush
