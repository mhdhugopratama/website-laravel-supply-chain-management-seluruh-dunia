@extends('layouts.app')
@section('title', __('app.ports.title') . ' — SupplyChainIQ')
@section('page_title', __('app.ports.title'))
@section('meta_description', 'Explore major world ports on an interactive map with real-time search and filtering.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="nb-page-header">
    <h1><i class="bi bi-anchor"></i> {{ __('app.ports.title') }}</h1>
    <p>{{ __('app.ports.subtitle') }}</p>
</div>

<div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="nb-card mb-3">
                <div class="nb-card-header"><i class="bi bi-map-fill"></i> {{ __('app.ports.map') }}</div>
                <div class="nb-card-body p-0">
                    <div class="nb-map-wrapper">
                        <div id="portMap"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="nb-card mb-3">
                <div class="nb-card-header"><i class="bi bi-search"></i> {{ __('app.ports.search') }}</div>
                <div class="nb-card-body">
                    <form method="GET" action="{{ route('ports.index') }}">
                        <div class="mb-2">
                            <input type="text" name="search" class="nb-input" placeholder="{{ __('app.ports.port_name') }}" value="{{ request('search') }}">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="country" class="nb-input" placeholder="{{ __('app.ports.country') }}" value="{{ request('country') }}">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="nb-btn nb-btn-primary flex-fill">
                                <i class="bi bi-search"></i> {{ __('app.ports.btn_search') }}
                            </button>
                            <a href="{{ route('ports.index') }}" class="nb-btn nb-btn-outline">{{ __('app.ports.btn_clear') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="nb-card">
                <div class="nb-card-header"><i class="bi bi-list-ul"></i> {{ __('app.ports.list') }} ({{ $ports->total() }})</div>
                <div class="nb-card-body p-0">
                    <div style="max-height:400px;overflow-y:auto">
                        @foreach($ports as $port)
                        <div class="country-dropdown-item port-list-item" data-lat="{{ $port->latitude }}" data-lng="{{ $port->longitude }}" data-name="{{ $port->name }}" style="cursor:pointer">
                            <i class="bi bi-geo-alt-fill" style="color:var(--nb-pink)"></i>
                            <div>
                                <div style="font-weight:700;font-size:0.85rem">{{ $port->name }}</div>
                                <div style="font-size:0.72rem;color:var(--nb-text-muted)">{{ $port->country_name }} · {{ $port->un_locode }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="p-2">
                        <div class="nb-pagination">
                            {{ $ports->links('vendor.pagination.nb') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const portMap = L.map('portMap').setView([20, 0], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(portMap);

const portIcon = L.divIcon({
    html: `<div style="width:24px;height:24px;background:var(--nb-pink);border:2px solid #000;box-shadow:3px 3px 0 #000;display:flex;align-items:center;justify-content:center;font-size:0.7rem;border-radius:50%">⚓</div>`,
    className: '', iconSize: [24, 24], iconAnchor: [12, 12]
});

fetch('/api/ports')
    .then(r => r.json())
    .then(ports => {
        ports.forEach(p => {
            if (p.latitude && p.longitude) {
                L.marker([p.latitude, p.longitude], { icon: portIcon })
                    .addTo(portMap)
                    .bindPopup(`<strong>${p.name}</strong><br>${p.country_name}<br><code>${p.un_locode || ''}</code>`);
            }
        });
    });

document.querySelectorAll('.port-list-item').forEach(el => {
    el.addEventListener('click', () => {
        const lat = parseFloat(el.dataset.lat);
        const lng = parseFloat(el.dataset.lng);
        if (!isNaN(lat) && !isNaN(lng)) {
            portMap.setView([lat, lng], 10);
        }
    });
});
</script>
@endpush
