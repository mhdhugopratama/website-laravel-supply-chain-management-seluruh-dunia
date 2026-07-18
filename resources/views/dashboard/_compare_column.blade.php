<div class="text-center py-4 mb-4" style="background: rgba(255, 255, 255, 0.03); border-radius: 12px; border: 1px solid var(--card-border);">
    <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px;">
        {{ __('app.risk.score') }}
    </div>
    <div style="font-size: 4rem; font-weight: 900; line-height: 1; margin: 10px 0; color: {{ $data['risk']['level']['color'] }}; text-shadow: 0 0 20px {{ $data['risk']['level']['color'] }}30;">
        {{ round($data['risk']['score']) }}%
    </div>
    <span class="nb-badge nb-badge-{{ $data['risk']['level']['badge'] }}" style="font-size: 0.85rem; padding: 6px 16px;">
        @if($data['risk']['level']['badge'] === 'success')
            🛡️ {{ __('app.risk.low') }}
        @elseif($data['risk']['level']['badge'] === 'warning')
            ⚠️ {{ __('app.risk.medium') }}
        @else
            🚨 {{ __('app.risk.high') }}
        @endif
    </span>
</div>

<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 24px;">
    {{-- Economic GDP --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-bank" style="color: var(--nb-green);"></i> {{ __('app.country.gdp') }}
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: var(--text-dark);">
            {{ $data['economic']['gdp'] ? '$' . number_format($data['economic']['gdp'] / 1e9, 1) . 'B' : __('app.country.no_data') }}
        </div>
    </div>

    {{-- Economic Inflation --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-graph-up-arrow" style="color: var(--nb-orange);"></i> {{ __('app.country.inflation') }}
        </div>
        @php
            $infl = $data['economic']['inflation'];
            $inflColor = is_null($infl) ? 'var(--text-muted)' : ($infl > 8 ? 'var(--nb-red)' : ($infl > 4 ? 'var(--nb-orange)' : 'var(--nb-green)'));
        @endphp
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: {{ $inflColor }};">
            {{ !is_null($infl) ? number_format($infl, 2) . '%' : __('app.country.no_data') }}
        </div>
    </div>

    {{-- Weather Temp --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-thermometer-half" style="color: var(--nb-cyan);"></i> {{ __('app.country.temperature') }}
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: var(--text-dark);">
            {{ $data['weather']['temperature'] ?? 'N/A' }}°C
        </div>
    </div>

    {{-- Weather Wind --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-wind" style="color: var(--nb-purple);"></i> {{ __('app.country.wind_speed') }}
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: var(--text-dark);">
            {{ $data['weather']['wind_speed'] ?? 'N/A' }} km/h
        </div>
    </div>

    {{-- Currency Code --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-currency-exchange" style="color: var(--nb-pink);"></i> {{ __('app.country.currency') }}
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: var(--text-dark);">
            {{ $country->currency_code }} <span style="font-size: 0.85rem; color: var(--text-muted);">({{ $country->currency_symbol }})</span>
        </div>
    </div>

    {{-- Population --}}
    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); padding: 12px; border-radius: 10px;">
        <div style="font-size: 0.70rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            <i class="bi bi-people" style="color: var(--primary);"></i> {{ __('app.country.population') }}
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; margin-top: 4px; color: var(--text-dark);">
            {{ $country->population ? number_format($country->population) : __('app.country.no_data') }}
        </div>
    </div>
</div>

<div class="mb-2" style="font-size:0.80rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-dark);">
    <i class="bi bi-shield-slash"></i> {{ __('app.risk.breakdown') }}
</div>
@foreach([
    [__('app.risk.weather'), $data['risk']['weather_risk'], 'var(--nb-cyan)', 'bi-cloud-lightning'],
    [__('app.risk.inflation'), $data['risk']['inflation_risk'], 'var(--nb-orange)', 'bi-graph-up-arrow'],
    [__('app.risk.news'), $data['risk']['news_risk'], 'var(--nb-pink)', 'bi-newspaper'],
    [__('app.risk.currency'), $data['risk']['currency_risk'], 'var(--nb-purple)', 'bi-currency-exchange'],
] as $r)
<div class="mb-3" style="background: rgba(255,255,255,0.01); border: 1px solid var(--card-border); padding: 10px; border-radius: 8px;">
    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.78rem;font-weight:600">
        <span style="color: var(--text-dark);"><i class="bi {{ $r[3] }}" style="color: {{ $r[2] }};"></i> {{ $r[0] }}</span>
        <span style="font-weight: 800; color: {{ $r[1] > 60 ? 'var(--nb-red)' : ($r[1] > 30 ? 'var(--nb-orange)' : 'var(--nb-green)') }}">{{ round($r[1]) }}%</span>
    </div>
    <div class="risk-meter"><div class="risk-meter-fill" style="width:{{ $r[1] }}%;background:{{ $r[2] }}"></div></div>
</div>
@endforeach
