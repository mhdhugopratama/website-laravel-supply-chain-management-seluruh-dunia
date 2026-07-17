@extends('layouts.app')
@section('title', __('app.currency.title') . ' — SupplyChainIQ')
@section('meta_description', 'Real-time currency exchange rates and cross-currency converter for global trade.')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1><i class="bi bi-currency-exchange"></i> {{ __('app.currency.title') }}</h1>
        <p>{{ __('app.currency.subtitle') }}</p>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <div class="nb-card mb-3">
                <div class="nb-card-header"><i class="bi bi-arrow-left-right"></i> {{ __('app.currency.converter') }}</div>
                <div class="nb-card-body">
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.82rem">{{ __('app.currency.amount') }}</label>
                        <input type="number" id="convAmount" class="nb-input mt-1" value="1" min="0" step="any">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label style="font-weight:700;font-size:0.82rem">{{ __('app.currency.from') }}</label>
                            <select id="convFrom" class="nb-select mt-1">
                                @foreach($rates as $code => $rate)
                                    <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label style="font-weight:700;font-size:0.82rem">{{ __('app.currency.to') }}</label>
                            <select id="convTo" class="nb-select mt-1">
                                @foreach($rates as $code => $rate)
                                    <option value="{{ $code }}" {{ $code === 'EUR' ? 'selected' : '' }}>{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="nb-btn nb-btn-primary w-100" onclick="convert()">
                        <i class="bi bi-calculator"></i> {{ __('app.currency.btn_convert') }}
                    </button>
                    <div id="convResult" class="mt-3" style="display:none">
                        <div class="nb-card" style="background:linear-gradient(135deg,rgba(0,212,255,0.18),rgba(168,85,247,0.18));border:1px solid rgba(0,212,255,0.35)">
                            <div class="nb-card-body text-center">
                                <div id="convResultValue" style="font-size:1.8rem;font-weight:800;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"></div>
                                <div id="convResultRate" style="font-size:0.8rem;font-weight:500;color:var(--text-secondary)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nb-card">
                <div class="nb-card-header"><i class="bi bi-graph-up-arrow"></i> {{ __('app.currency.trend_chart') }}</div>
                <div class="nb-card-body">
                    <div class="chart-wrapper">
                        <canvas id="rateChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="nb-card">
                <div class="nb-card-header"><i class="bi bi-table"></i> {{ __('app.currency.rates_table') }}</div>
                <div class="nb-card-body">
                    <input type="text" id="rateSearch" class="nb-input mb-3" placeholder="{{ __('app.currency.filter') }}">
                    <div style="max-height:600px;overflow-y:auto">
                        <table class="nb-table" id="rateTable">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>{{ __('app.currency.rate') }}</th>
                                    <th>{{ __('app.currency.inverse') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rates as $code => $rate)
                                <tr class="rate-row">
                                    <td><strong>{{ $code }}</strong></td>
                                    <td>{{ number_format($rate, 4) }}</td>
                                    <td>{{ $rate > 0 ? number_format(1 / $rate, 6) : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rates = @json($rates);

function convert() {
    const amount = parseFloat(document.getElementById('convAmount').value) || 1;
    const from   = document.getElementById('convFrom').value;
    const to     = document.getElementById('convTo').value;

    fetch(`/api/currency?from=${from}&to=${to}&amount=${amount}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            document.getElementById('convResult').style.display = 'block';
            document.getElementById('convResultValue').textContent = `${amount} ${from} = ${data.result.toLocaleString(undefined, {maximumFractionDigits:4})} ${to}`;
            document.getElementById('convResultRate').textContent = `1 ${from} = ${data.rate} ${to}`;
            updateChart(from, to);
        });
}

function updateChart(from, to) {
    const topCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'MYR', 'SGD', 'AUD'];
    const labels = topCurrencies.filter(c => c !== from).slice(0, 6);
    const fromRate = rates[from] || 1;
    const data = labels.map(c => rates[c] ? parseFloat((rates[c] / fromRate).toFixed(4)) : 0);

    if (window.rateChartInstance) window.rateChartInstance.destroy();
    
    const ctx = document.getElementById('rateChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0, 'rgba(0, 229, 255, 0.85)');
    grad.addColorStop(1, 'rgba(0, 229, 255, 0.15)');

    window.rateChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: grad,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' },
                    bodyFont: { family: "'Plus Jakarta Sans', sans-serif" },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' } } }
            }
        }
    });
}

document.getElementById('rateSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.rate-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});

convert();
</script>
@endpush
