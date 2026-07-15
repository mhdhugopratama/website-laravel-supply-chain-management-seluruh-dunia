@extends('layouts.app')
@section('title', __('app.watchlist.title') . ' — SupplyChainIQ')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1><i class="bi bi-star-fill"></i> {{ __('app.watchlist.title') }}</h1>
        <p>{{ __('app.watchlist.subtitle') }}</p>
    </div>
</div>

<div class="container-fluid px-4">
    @if($watchlist->isEmpty())
        <div class="nb-card text-center py-5">
            <div style="font-size:3rem">⭐</div>
            <h3 class="mt-3" style="font-weight:900">{{ __('app.watchlist.empty_title') }}</h3>
            <p style="color:var(--nb-text-muted)">{{ __('app.watchlist.empty_text') }}</p>
            <a href="{{ route('dashboard') }}" class="nb-btn nb-btn-primary mt-2">
                <i class="bi bi-globe2"></i> {{ __('app.watchlist.go_dashboard') }}
            </a>
        </div>
    @else
        <div class="nb-section-title">{{ __('app.watchlist.monitored', ['count' => $watchlist->count()]) }}</div>
        <div class="row g-3">
            @foreach($watchlist as $country)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="nb-card">
                    <div class="nb-card-body d-flex align-items-center gap-3">
                        <div style="font-size:2.5rem">{{ $country->flag_emoji }}</div>
                        <div class="flex-fill">
                            <div style="font-weight:800;font-size:1rem">{{ $country->name }}</div>
                            <div style="color:var(--nb-text-muted);font-size:0.8rem">{{ $country->iso3 }} · {{ $country->currency_code }}</div>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('country.show', $country->iso3) }}" class="nb-btn nb-btn-dark btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="nb-btn nb-btn-danger btn-sm" onclick="removeWatchlist({{ $country->id }}, this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function removeWatchlist(countryId, btn) {
    fetch('/watchlist/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ country_id: countryId })
    })
    .then(r => r.json())
    .then(() => {
        btn.closest('.col-12').remove();
    });
}
</script>
@endpush
