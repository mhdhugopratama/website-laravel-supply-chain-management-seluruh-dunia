@extends('layouts.app')
@section('title', __('app.compare.title') . ' — GoSupply')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1><i class="bi bi-arrows-angle-expand"></i> {{ __('app.compare.title') }}</h1>
        <p>{{ __('app.compare.subtitle') }}</p>
    </div>
</div>

<div class="container-fluid px-4">
    <form method="GET" action="{{ route('compare') }}" class="mb-4">
        <div class="nb-card">
            <div class="nb-card-header"><i class="bi bi-sliders"></i> {{ __('app.compare.select') }}</div>
            <div class="nb-card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label style="font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px">{{ __('app.compare.country_a') }}</label>
                        <select name="a" class="nb-select mt-1 nb-select-country" required>
                            <option value="">{{ __('app.compare.placeholder_a') }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->iso3 }}" data-src="{{ !empty($c->iso2) ? 'https://flagcdn.com/w20/'.strtolower($c->iso2).'.png' : '' }}" {{ request('a') === $c->iso3 ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->iso3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 text-center">
                        <div style="font-size:2rem;font-weight:800;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">VS</div>
                    </div>
                    <div class="col-12 col-md-5">
                        <label style="font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px">{{ __('app.compare.country_b') }}</label>
                        <select name="b" class="nb-select mt-1 nb-select-country" required>
                            <option value="">{{ __('app.compare.placeholder_b') }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->iso3 }}" data-src="{{ !empty($c->iso2) ? 'https://flagcdn.com/w20/'.strtolower($c->iso2).'.png' : '' }}" {{ request('b') === $c->iso3 ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->iso3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="nb-btn nb-btn-primary" style="min-width:200px">
                            <i class="bi bi-bar-chart-line"></i> {{ __('app.compare.btn') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($countryA && $countryB && $dataA && $dataB)

    <!-- Quick Glance Live Data Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;"><i class="bi bi-thermometer-half" style="color:var(--nb-cyan)"></i> {{ $countryA->name }}</div>
                <div style="font-size:1.6rem; font-weight:900; color:var(--text-dark); margin-top:4px;">{{ $dataA['weather']['temperature'] ?? 'N/A' }}°C</div>
                <div style="font-size:0.65rem; color:var(--text-muted);">Live Temperature</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;"><i class="bi bi-thermometer-half" style="color:var(--nb-pink)"></i> {{ $countryB->name }}</div>
                <div style="font-size:1.6rem; font-weight:900; color:var(--text-dark); margin-top:4px;">{{ $dataB['weather']['temperature'] ?? 'N/A' }}°C</div>
                <div style="font-size:0.65rem; color:var(--text-muted);">Live Temperature</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;"><i class="bi bi-currency-exchange" style="color:var(--nb-cyan)"></i> {{ $countryA->currency_code }}</div>
                <div style="font-size:1.4rem; font-weight:900; color:var(--text-dark); margin-top:4px;">{{ $countryA->currency_symbol ?? $countryA->currency_code }} {{ is_numeric($dataA['exchange'] ?? null) ? number_format($dataA['exchange'], 2) : 'N/A' }}</div>
                <div style="font-size:0.65rem; color:var(--text-muted);">Exchange Rate (USD Base)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="nb-card text-center" style="padding: 24px;">
                <div style="font-size:0.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;"><i class="bi bi-currency-exchange" style="color:var(--nb-pink)"></i> {{ $countryB->currency_code }}</div>
                <div style="font-size:1.4rem; font-weight:900; color:var(--text-dark); margin-top:4px;">{{ $countryB->currency_symbol ?? $countryB->currency_code }} {{ is_numeric($dataB['exchange'] ?? null) ? number_format($dataB['exchange'], 2) : 'N/A' }}</div>
                <div style="font-size:0.65rem; color:var(--text-muted);">Exchange Rate (USD Base)</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="nb-compare-side nb-card">
                <div class="nb-card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,rgba(14,165,233,0.15),rgba(14,165,233,0.06));color:var(--teal);font-size:1.1rem;padding:1rem">
                    @if(!empty($countryA->iso2))
                        <img src="https://flagcdn.com/w40/{{ strtolower($countryA->iso2) }}.png" width="24" alt="Flag" style="border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                    @endif
                    {{ $countryA->name }}
                </div>
                <div class="nb-card-body">
                    @include('dashboard._compare_column', ['country' => $countryA, 'data' => $dataA, 'side' => 'A'])
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="nb-compare-side nb-card">
                <div class="nb-card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,rgba(236,72,153,0.15),rgba(236,72,153,0.06));color:var(--nb-pink);font-size:1.1rem;padding:1rem">
                    @if(!empty($countryB->iso2))
                        <img src="https://flagcdn.com/w40/{{ strtolower($countryB->iso2) }}.png" width="24" alt="Flag" style="border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                    @endif
                    {{ $countryB->name }}
                </div>
                <div class="nb-card-body">
                    @include('dashboard._compare_column', ['country' => $countryB, 'data' => $dataB, 'side' => 'B'])
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 d-flex flex-column">
            <div class="nb-card mb-3">
                <div class="nb-card-header"><i class="bi bi-bar-chart"></i> {{ __('app.compare.chart') }}</div>
                <div class="nb-card-body">
                    <div class="chart-wrapper">
                        <canvas id="compareChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            <div class="nb-card flex-grow-1 d-flex flex-column">
                <div class="nb-card-header" style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05));">
                    <i class="bi bi-lightbulb-fill" style="color: var(--nb-orange);"></i> Supply Chain Intelligence Analysis & Recommendations
                </div>
                <div class="nb-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6" style="border-right: 1px solid var(--card-border);">
                            <h5 style="font-weight: 800; color: var(--text-dark); margin-bottom: 12px;"> Comparative Risk Verdict</h5>
                            <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text-body);">
                                Based on current supply chain parameters, 
                                @if($dataA['risk']['score'] < $dataB['risk']['score'])
                                    <strong>{{ $countryA->name }}</strong> presents a more stable shipping environment with an overall risk of <strong>{{ round($dataA['risk']['score']) }}%</strong>, compared to <strong>{{ $countryB->name }}</strong>'s risk of <strong>{{ round($dataB['risk']['score']) }}%</strong>.
                                @elseif($dataB['risk']['score'] < $dataA['risk']['score'])
                                    <strong>{{ $countryB->name }}</strong> presents a more stable shipping environment with an overall risk of <strong>{{ round($dataB['risk']['score']) }}%</strong>, compared to <strong>{{ $countryA->name }}</strong>'s risk of <strong>{{ round($dataA['risk']['score']) }}%</strong>.
                                @else
                                    Both countries present identical overall risks of <strong>{{ round($dataA['risk']['score']) }}%</strong>.
                                @endif
                            </p>
                            <div style="background: rgba(255,255,255,0.02);  border-radius: 8px; border: 1px solid var(--card-border); margin-top: 15px;">
                                <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;"> Logistics Advice</div>
                                <span style="font-size: 0.84rem; color: var(--text-dark);">
                                    @if($dataA['risk']['score'] < 40 && $dataB['risk']['score'] < 40)
                                        Both countries are in the green (safe) zone. You can select either route depending on cost and shipping carrier availability.
                                    @elseif(abs($dataA['risk']['score'] - $dataB['risk']['score']) < 10)
                                        The risk margin between both countries is tight. Pay close attention to secondary factors like shipping rates, port delays, and custom clearance procedures.
                                    @else
                                        We strongly advise routing shipments through the lower-risk country to avoid potential supply chain disruptions.
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <h5 style="font-weight: 800; color: var(--text-dark); margin-bottom: 12px;"> Segmented Comparison Insights</h5>
                            <ul style="font-size: 0.85rem; line-height: 1.8; color: var(--text-body); padding-left: 20px;">
                                <li>
                                    <strong>Economic Stability:</strong> Inflation in 
                                    @if(is_null($dataA['economic']['inflation']))
                                        {{ $countryB->name }} ({{ number_format($dataB['economic']['inflation'], 1) }}%)
                                    @elseif(is_null($dataB['economic']['inflation']))
                                        {{ $countryA->name }} ({{ number_format($dataA['economic']['inflation'], 1) }}%)
                                    @elseif($dataA['economic']['inflation'] < $dataB['economic']['inflation'])
                                        <strong>{{ $countryA->name }}</strong> ({{ number_format($dataA['economic']['inflation'], 1) }}%) is more stable than {{ $countryB->name }} ({{ number_format($dataB['economic']['inflation'], 1) }}%).
                                    @else
                                        <strong>{{ $countryB->name }}</strong> ({{ number_format($dataB['economic']['inflation'], 1) }}%) is more stable than {{ $countryA->name }} ({{ number_format($dataA['economic']['inflation'], 1) }}%).
                                    @endif
                                </li>
                                <li>
                                    <strong>Climate Risk:</strong> 
                                    @if($dataA['risk']['weather_risk'] < $dataB['risk']['weather_risk'])
                                        <strong>{{ $countryA->name }}</strong> is less prone to sudden weather interruptions (Weather Risk: {{ round($dataA['risk']['weather_risk']) }}%).
                                    @elseif($dataB['risk']['weather_risk'] < $dataA['risk']['weather_risk'])
                                        <strong>{{ $countryB->name }}</strong> is less prone to sudden weather interruptions (Weather Risk: {{ round($dataB['risk']['weather_risk']) }}%).
                                    @else
                                        Both share similar weather risks ({{ round($dataA['risk']['weather_risk']) }}%).
                                    @endif
                                </li>
                                <li>
                                    <strong>Public Sentiment:</strong> Logistics news sentiment is more positive for 
                                    @if($dataA['risk']['news_risk'] < $dataB['risk']['news_risk'])
                                        <strong>{{ $countryA->name }}</strong> (Negative Sentiment: {{ round($dataA['risk']['news_risk']) }}%).
                                    @else
                                        <strong>{{ $countryB->name }}</strong> (Negative Sentiment: {{ round($dataB['risk']['news_risk']) }}%).
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            {{-- News Country A --}}
            <div class="nb-card mb-3">
                <div class="nb-card-header" style="background: linear-gradient(135deg, rgba(14,165,233,0.1), rgba(14,165,233,0.02)); color: var(--teal); font-size: 0.90rem;">
                    <i class="bi bi-newspaper"></i> Live Logistics News: {{ $countryA->name }}
                </div>
                <div class="nb-card-body" style="max-height: 290px; overflow-y: auto; ">
                    @if(empty($dataA['news']['articles']))
                        <div style="color: var(--text-muted); text-align: center; padding: 20px; font-size: 0.82rem;">No recent logistics news found.</div>
                    @else
                        @foreach(array_slice($dataA['news']['articles'], 0, 4) as $article)
                        <div class="py-2" style="border-bottom: 1px solid var(--card-border);">
                            <a href="{{ $article['url'] }}" target="_blank" class="text-decoration-none" style="font-weight: 700; font-size: 0.80rem; color: var(--text-dark); display: block; line-height: 1.3;">
                                {{ Str::limit($article['title'], 75) }}
                            </a>
                            <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.68rem; color: var(--text-muted);">
                                <span><i class="bi bi-broadcast"></i> {{ $article['source'] }}</span>
                                <span>{{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- News Country B --}}
            <div class="nb-card">
                <div class="nb-card-header" style="background: linear-gradient(135deg, rgba(236,72,153,0.1), rgba(236,72,153,0.02)); color: var(--nb-pink); font-size: 0.90rem;">
                    <i class="bi bi-newspaper"></i> Live Logistics News: {{ $countryB->name }}
                </div>
                <div class="nb-card-body" style="max-height: 290px; overflow-y: auto; ">
                    @if(empty($dataB['news']['articles']))
                        <div style="color: var(--text-muted); text-align: center; padding: 20px; font-size: 0.82rem;">No recent logistics news found.</div>
                    @else
                        @foreach(array_slice($dataB['news']['articles'], 0, 4) as $article)
                        <div class="py-2" style="border-bottom: 1px solid var(--card-border);">
                            <a href="{{ $article['url'] }}" target="_blank" class="text-decoration-none" style="font-weight: 700; font-size: 0.80rem; color: var(--text-dark); display: block; line-height: 1.3;">
                                {{ Str::limit($article['title'], 75) }}
                            </a>
                            <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.68rem; color: var(--text-muted);">
                                <span><i class="bi bi-broadcast"></i> {{ $article['source'] }}</span>
                                <span>{{ \Carbon\Carbon::parse($article['published'])->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($countryA && $dataA && $countryB && $dataB)
<script>
new Chart(document.getElementById('compareChart'), {
    type: 'bar',
    data: {
        labels: ['{{ __("app.risk.score") }}', '{{ __("app.risk.weather") }}', '{{ __("app.risk.inflation") }}', '{{ __("app.risk.news") }}', '{{ __("app.risk.currency") }}'],
        datasets: [
            {
                label: '{{ $countryA->name }}',
                data: [{{ $dataA['risk']['score'] }},{{ $dataA['risk']['weather_risk'] }},{{ $dataA['risk']['inflation_risk'] }},{{ $dataA['risk']['news_risk'] }},{{ $dataA['risk']['currency_risk'] }}],
                backgroundColor: '#00E5FF', borderColor: '#000', borderWidth: 2
            },
            {
                label: '{{ $countryB->name }}',
                data: [{{ $dataB['risk']['score'] }},{{ $dataB['risk']['weather_risk'] }},{{ $dataB['risk']['inflation_risk'] }},{{ $dataB['risk']['news_risk'] }},{{ $dataB['risk']['currency_risk'] }}],
                backgroundColor: '#FF2D78', borderColor: '#000', borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { font: { family: 'Poppins', weight: '700' }, boxWidth: 16 } } },
        scales: {
            y: { min: 0, max: 100, ticks: { font: { family: 'Poppins' } } },
            x: { ticks: { font: { family: 'Poppins', weight: '600' } } }
        }
    }
});
</script>
@endif
@endpush
