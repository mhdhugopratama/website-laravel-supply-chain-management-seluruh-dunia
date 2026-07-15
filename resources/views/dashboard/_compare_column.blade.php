<div class="mb-3">
    <div class="nb-stat-label">{{ __('app.risk.score') }}</div>
    <div class="risk-score-display" style="font-size:2.5rem;color:{{ $data['risk']['level']['color'] }}">
        {{ $data['risk']['score'] }}
    </div>
    <div class="nb-badge nb-badge-{{ $data['risk']['level']['badge'] }} mt-1">
        @if($data['risk']['level']['badge'] === 'success')
            {{ __('app.risk.low') }}
        @elseif($data['risk']['level']['badge'] === 'warning')
            {{ __('app.risk.medium') }}
        @else
            {{ __('app.risk.high') }}
        @endif
    </div>
</div>

<table class="nb-table mb-3">
    <tr>
        <td><strong>{{ __('app.country.gdp') }}</strong></td>
        <td>{{ $data['economic']['gdp'] ? '$' . number_format($data['economic']['gdp'] / 1e9, 1) . 'B' : __('app.country.no_data') }}</td>
    </tr>
    <tr>
        <td><strong>{{ __('app.country.inflation') }}</strong></td>
        <td>{{ !is_null($data['economic']['inflation']) ? number_format($data['economic']['inflation'], 2) . '%' : __('app.country.no_data') }}</td>
    </tr>
    <tr>
        <td><strong>{{ __('app.country.temperature') }}</strong></td>
        <td>{{ $data['weather']['temperature'] ?? 'N/A' }}°C</td>
    </tr>
    <tr>
        <td><strong>{{ __('app.country.wind_speed') }}</strong></td>
        <td>{{ $data['weather']['wind_speed'] ?? 'N/A' }} km/h</td>
    </tr>
    <tr>
        <td><strong>{{ __('app.country.currency') }}</strong></td>
        <td>{{ $country->currency_code }} ({{ $country->currency_symbol }})</td>
    </tr>
    <tr>
        <td><strong>{{ __('app.country.population') }}</strong></td>
        <td>{{ $country->population ? number_format($country->population) : __('app.country.no_data') }}</td>
    </tr>
</table>

<div class="mb-1" style="font-size:0.75rem;font-weight:700;text-transform:uppercase">{{ __('app.risk.breakdown') }}</div>
@foreach([
    [__('app.risk.weather'), $data['risk']['weather_risk'], 'var(--nb-cyan)'],
    [__('app.risk.inflation'), $data['risk']['inflation_risk'], 'var(--nb-orange)'],
    [__('app.risk.news'), $data['risk']['news_risk'], 'var(--nb-pink)'],
    [__('app.risk.currency'), $data['risk']['currency_risk'], 'var(--nb-purple)'],
] as $r)
<div class="mb-2">
    <div class="d-flex justify-content-between" style="font-size:0.78rem;font-weight:600">
        <span>{{ $r[0] }}</span><span>{{ $r[1] }}</span>
    </div>
    <div class="risk-meter"><div class="risk-meter-fill" style="width:{{ $r[1] }}%;background:{{ $r[2] }}"></div></div>
</div>
@endforeach
