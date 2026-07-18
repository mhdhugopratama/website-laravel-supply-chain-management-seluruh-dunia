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
                    @php
                        $flagEmoji = '';
                        if (!empty($c->iso2)) {
                            $chr1 = ord(strtoupper($c->iso2)[0]) - 65 + 127462;
                            $chr2 = ord(strtoupper($c->iso2)[1]) - 65 + 127462;
                            $flagEmoji = mb_chr($chr1, 'UTF-8') . mb_chr($chr2, 'UTF-8') . ' ';
                        }
                    @endphp
                    <option value="{{ $c->iso3 }}">{{ $flagEmoji }}{{ $c->name }} ({{ $c->iso3 }})</option>
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

        {{-- Brand new Intelligence Verdict Block --}}
        <div class="nb-card mb-4" id="analyticsVerdictCard" style="display:none;">
            <div class="nb-card-header" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));">
                <i class="bi bi-shield-fill-check" style="color: var(--nb-green);"></i> Supply Chain Risk Verdict & Insights
            </div>
            <div class="nb-card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4" style="border-right: 1px solid var(--card-border);">
                        <h6 style="font-weight: 800; color: var(--text-dark); margin-bottom: 8px;">📢 Threat Vulnerability Verdict</h6>
                        <div id="verdictText" style="font-size: 0.88rem; line-height: 1.6; margin-top: 10px; color: var(--text-body);"></div>
                    </div>
                    <div class="col-12 col-md-8">
                        <h6 style="font-weight: 800; color: var(--text-dark); margin-bottom: 8px;">Mitigation Recommendations</h6>
                        <ul id="mitigationSteps" style="font-size: 0.84rem; line-height: 1.8; margin-top: 10px; padding-left: 20px; color: var(--text-body);"></ul>
                    </div>
                </div>
            </div>
        </div>

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

        {{-- Live Logistics News Feed --}}
        <div class="nb-card mt-4" id="analyticsNewsCard" style="display:none;">
            <div class="nb-card-header"><i class="bi bi-newspaper"></i> Live Logistics News Intelligence Feed</div>
            <div class="nb-card-body">
                <div class="row g-3" id="analyticsNewsList"></div>
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

// Global Chart Styling Helpers
const getGradient = (ctx, colorStart, colorEnd) => {
    const grad = ctx.createLinearGradient(0, 0, 0, 250);
    grad.addColorStop(0, colorStart);
    grad.addColorStop(1, colorEnd);
    return grad;
};

const fontConfig = { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' };
const gridConfig = { color: 'rgba(0,0,0,0.04)', drawBorder: false };
const tooltipConfig = {
    backgroundColor: 'rgba(15, 23, 42, 0.9)',
    titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' },
    bodyFont: { family: "'Plus Jakarta Sans', sans-serif" },
    padding: 10,
    cornerRadius: 8,
    displayColors: false
};

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
                    <div class="nb-admin-stat-number" style="color:${r.level.color}">${Math.round(r.score)}%</div>
                    <div class="nb-badge nb-badge-${r.level.badge}">${badgeLabel}</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">🌡️ {{ __('app.country.temperature') }}</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-cyan)">${w.temperature ?? 'N/A'}°C</div>
                    <div style="font-size:0.68rem;color:var(--text-muted)">💨 Wind: ${w.wind_speed ?? 'N/A'} km/h</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">💰 {{ __('app.country.gdp') }}</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-green)">
                        ${e.gdp ? '$' + (e.gdp / 1e9).toFixed(1) + 'B' : 'N/A'}
                    </div>
                    <div style="font-size:0.68rem;color:var(--text-muted)">Annual GDP</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">📈 {{ __('app.country.inflation') }}</div>
                    <div class="nb-admin-stat-number" style="color:${(e.inflation||0)>10?'var(--nb-red)':'var(--nb-orange)'}">
                        ${e.inflation !== null ? e.inflation.toFixed(2) + '%' : 'N/A'}
                    </div>
                    <div style="font-size:0.68rem;color:var(--text-muted)">${(e.inflation||0) > 8 ? '⚠️ High inflation risk' : '✅ Manageable rate'}</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">📦 Exports</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-purple)">
                        ${e.exports ? '$' + (e.exports / 1e9).toFixed(1) + 'B' : 'N/A'}
                    </div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">📥 Imports</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-pink)">
                        ${e.imports ? '$' + (e.imports / 1e9).toFixed(1) + 'B' : 'N/A'}
                    </div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">👥 Population</div>
                    <div class="nb-admin-stat-number" style="color:var(--primary)">
                        ${c.population ? Number(c.population).toLocaleString() : 'N/A'}
                    </div>
                </div></div>
                <div class="col-6 col-md-3"><div class="nb-admin-stat">
                    <div class="nb-stat-label">💱 Currency</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-orange)">
                        ${c.currency_code || 'N/A'}
                    </div>
                    <div style="font-size:0.68rem;color:var(--text-muted)">${c.currency_symbol || ''} ${c.currency_name || ''}</div>
                </div></div>
            `;

            // Populate Verdict Card
            document.getElementById('analyticsVerdictCard').style.display = 'block';
            let verdict = '';
            let steps = [];

            if (r.score < 30) {
                verdict = `<strong>${c.name}</strong> is currently categorized as a <strong>Low Risk</strong> destination. Supply chain routes are stable, with optimal weather, low inflation, and positive geopolitical sentiment.`;
                steps.push('Standard operating procedures (SOP) are recommended.');
                steps.push('Maintain regular inventory buffers.');
            } else if (r.score < 60) {
                verdict = `<strong>${c.name}</strong> is currently categorized as a <strong>Medium Risk</strong> destination. Active monitoring is recommended due to moderate vulnerabilities.`;
                if (r.weather_risk > 50) {
                    steps.push('<strong>Weather Vulnerability:</strong> Prepare for minor port delays due to adverse weather. Track incoming weather forecasts.');
                }
                if (r.inflation_risk > 50) {
                    steps.push('<strong>Inflation Threat:</strong> Rising prices may affect domestic freight costs. Consider locking in long-term transport rates.');
                }
                if (r.news_risk > 50) {
                    steps.push('<strong>Geopolitical/Labor Risk:</strong> Sentiment shows moderate friction. Monitor local labor news for potential strike warnings.');
                }
                if (r.currency_risk > 50) {
                    steps.push('<strong>Exchange Rate Volatility:</strong> Monitor currency swings. Use forward contracts to hedge currency exchange risks.');
                }
                if (steps.length === 0) {
                    steps.push('Perform routine supplier health audits.');
                    steps.push('Optimize transit routes to reduce minor lead-time delays.');
                }
            } else {
                verdict = `<strong>${c.name}</strong> is categorized as a <strong>High Risk</strong> zone! Severe disruptions to logistics, port operations, or cargo clearances are highly likely.`;
                steps.push('<strong>Immediate action required:</strong> Consider diversifying or rerouting critical shipments through neighboring low-risk countries.');
                if (r.weather_risk > 60) {
                    steps.push('<strong>Severe Climate Warning:</strong> Extreme weather/storms detected. Suspend non-essential maritime or air freight and activate emergency backup warehouses.');
                }
                if (r.inflation_risk > 60) {
                    steps.push('<strong>Severe Economic Inflation:</strong> Rapidly escalating costs. Re-negotiate contract pricing structures with regional suppliers.');
                }
                if (r.news_risk > 60) {
                    steps.push('<strong>Critical Public Disruption:</strong> Highly negative logistics news sentiment. Labor union strikes, cargo seizures, or port blockades are highly probable.');
                }
            }

            document.getElementById('verdictText').innerHTML = verdict;
            document.getElementById('mitigationSteps').innerHTML = steps.map(s => `<li>${s}</li>`).join('');

            // Populate News Feed Card
            document.getElementById('analyticsNewsCard').style.display = 'block';
            const newsList = document.getElementById('analyticsNewsList');
            if (!data.news || !data.news.articles || data.news.articles.length === 0) {
                newsList.innerHTML = '<div class="col-12 text-muted text-center py-4">No recent logistics news found for this country.</div>';
            } else {
                newsList.innerHTML = data.news.articles.slice(0, 3).map(article => `
                    <div class="col-12 col-md-4">
                        <div class="nb-news-card" style="height: 100%; border: 1px solid var(--card-border); background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px;">
                            <div class="nb-news-title">
                                <a href="${article.url}" target="_blank" class="text-decoration-none" style="color:var(--text-dark); font-weight: 700; font-size: 0.85rem; line-height: 1.3; display: block;">
                                    ${article.title}
                                </a>
                            </div>
                            <div class="nb-news-meta" style="margin-top: 8px; font-size: 0.70rem; color: var(--text-muted);"><i class="bi bi-broadcast"></i> ${article.source} · ${new Date(article.published).toLocaleDateString()}</div>
                        </div>
                    </div>
                `).join('');
            }

            // 1. RISK CHART
            const ctxRisk = document.getElementById('riskChart').getContext('2d');
            charts.risk = new Chart(ctxRisk, {
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
                        data: [r.weather_risk, r.inflation_risk, r.news_risk, r.currency_risk, r.score],
                        backgroundColor: [
                            getGradient(ctxRisk, 'rgba(0, 229, 255, 0.85)', 'rgba(0, 229, 255, 0.15)'),
                            getGradient(ctxRisk, 'rgba(255, 109, 0, 0.85)', 'rgba(255, 109, 0, 0.15)'),
                            getGradient(ctxRisk, 'rgba(255, 45, 120, 0.85)', 'rgba(255, 45, 120, 0.15)'),
                            getGradient(ctxRisk, 'rgba(124, 77, 255, 0.85)', 'rgba(124, 77, 255, 0.15)'),
                            getGradient(ctxRisk, 'rgba(255, 229, 0, 0.85)', 'rgba(255, 229, 0, 0.15)')
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipConfig
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontConfig } },
                        y: { min: 0, max: 100, grid: gridConfig, ticks: { font: fontConfig } }
                    }
                }
            });

            // 2. DOUGHNUT PIE CHART
            const ctxPie = document.getElementById('riskPieChart').getContext('2d');
            charts.pie = new Chart(ctxPie, {
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
                        backgroundColor: [
                            'rgba(0, 229, 255, 0.8)',
                            'rgba(255, 109, 0, 0.8)',
                            'rgba(255, 45, 120, 0.8)',
                            'rgba(124, 77, 255, 0.8)'
                        ],
                        borderWidth: 0,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: fontConfig, boxWidth: 12, padding: 15 }
                        },
                        tooltip: tooltipConfig
                    }
                }
            });

            // 3. WEATHER CHART
            const ctxWeather = document.getElementById('weatherChart').getContext('2d');
            charts.weather = new Chart(ctxWeather, {
                type: 'bar',
                data: {
                    labels: ['{{ __("app.country.temperature") }} (°C)', '{{ __("app.country.precipitation") }} (mm)', '{{ __("app.country.wind_speed") }} (km/h)'],
                    datasets: [{
                        data: [w.temperature || 0, w.precipitation || 0, w.wind_speed || 0],
                        backgroundColor: [
                            getGradient(ctxWeather, 'rgba(0, 230, 118, 0.85)', 'rgba(0, 230, 118, 0.15)'),
                            getGradient(ctxWeather, 'rgba(0, 229, 255, 0.85)', 'rgba(0, 229, 255, 0.15)'),
                            getGradient(ctxWeather, 'rgba(255, 229, 0, 0.85)', 'rgba(255, 229, 0, 0.15)')
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipConfig
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontConfig } },
                        y: { grid: gridConfig, ticks: { font: fontConfig } }
                    }
                }
            });

            // 4. ECON CHART
            const ctxEcon = document.getElementById('econChart').getContext('2d');
            charts.econ = new Chart(ctxEcon, {
                type: 'bar',
                data: {
                    labels: ['{{ __("app.country.inflation") }} (%)', '{{ __("app.country.gdp") }} (Billion USD)'],
                    datasets: [{
                        data: [e.inflation || 0, e.gdp ? (e.gdp / 1e9).toFixed(2) : 0],
                        backgroundColor: [
                            getGradient(ctxEcon, 'rgba(255, 109, 0, 0.85)', 'rgba(255, 109, 0, 0.15)'),
                            getGradient(ctxEcon, 'rgba(124, 77, 255, 0.85)', 'rgba(124, 77, 255, 0.15)')
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipConfig
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontConfig } },
                        y: { grid: gridConfig, ticks: { font: fontConfig } }
                    }
                }
            });
        })
        .catch((e) => {
            console.error(e);
            document.getElementById('analyticsLoading').style.display = 'none';
        });
}
</script>
@endpush
