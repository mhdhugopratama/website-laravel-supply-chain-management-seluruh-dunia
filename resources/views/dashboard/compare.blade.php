@extends('layouts.app')
@section('title', __('app.compare.title') . ' — SupplyChainIQ')

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
                        <select name="a" class="nb-select mt-1" required>
                            <option value="">{{ __('app.compare.placeholder_a') }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->iso3 }}" {{ request('a') === $c->iso3 ? 'selected' : '' }}>
                                    {{ $c->flag_emoji }} {{ $c->name }} ({{ $c->iso3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 text-center">
                        <div style="font-size:2rem;font-weight:800;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">VS</div>
                    </div>
                    <div class="col-12 col-md-5">
                        <label style="font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px">{{ __('app.compare.country_b') }}</label>
                        <select name="b" class="nb-select mt-1" required>
                            <option value="">{{ __('app.compare.placeholder_b') }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->iso3 }}" {{ request('b') === $c->iso3 ? 'selected' : '' }}>
                                    {{ $c->flag_emoji }} {{ $c->name }} ({{ $c->iso3 }})
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
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="nb-compare-side nb-card">
                <div class="nb-card-header" style="background:linear-gradient(135deg,rgba(14,165,233,0.15),rgba(14,165,233,0.06));color:var(--teal);font-size:1.1rem;padding:1rem">
                    {{ $countryA->flag_emoji }} {{ $countryA->name }}
                </div>
                <div class="nb-card-body">
                    @include('dashboard._compare_column', ['country' => $countryA, 'data' => $dataA, 'side' => 'A'])
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="nb-compare-side nb-card">
                <div class="nb-card-header" style="background:linear-gradient(135deg,rgba(236,72,153,0.15),rgba(236,72,153,0.06));color:var(--nb-pink);font-size:1.1rem;padding:1rem">
                    {{ $countryB->flag_emoji }} {{ $countryB->name }}
                </div>
                <div class="nb-card-body">
                    @include('dashboard._compare_column', ['country' => $countryB, 'data' => $dataB, 'side' => 'B'])
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="nb-card">
                <div class="nb-card-header"><i class="bi bi-bar-chart"></i> {{ __('app.compare.chart') }}</div>
                <div class="nb-card-body">
                    <div class="chart-wrapper">
                        <canvas id="compareChart" height="100"></canvas>
                    </div>
                </div>
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
