@extends('layouts.app')
@section('title', __('app.ports.title') . ' | GoSupply')
@section('page_title', __('app.ports.title'))
@section('meta_description', 'Explore major world ports on an interactive map with real-time search and filtering.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="nb-page-header">
    <h1>{{ __('app.ports.title') }}</h1>
    <p>{{ __('app.ports.subtitle') }}</p>
</div>

<div class="row g-4">
        <div class="col-12 col-lg-8 d-flex flex-column">
            <div class="nb-card mb-3 flex-grow-1 d-flex flex-column" style="min-height: 600px;">
                <div class="nb-card-header">{{ __('app.ports.map') }}</div>
                <div class="nb-card-body p-0 flex-grow-1 position-relative">
                    <div id="portMap" style="position:absolute; top:0; left:0; width:100%; height:100%; border-bottom-left-radius: var(--r-lg); border-bottom-right-radius: var(--r-lg);"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="nb-card mb-3">
                <div class="nb-card-header">{{ __('app.ports.search') }}</div>
                <div class="nb-card-body">
                    <form id="portsSearchForm" method="GET" action="{{ route('ports.index') }}">
                        <div class="mb-2">
                            <input type="text" name="search" class="nb-input" placeholder="{{ __('app.ports.port_name') }}" value="{{ request('search') }}">
                        </div>
                        <div class="mb-2">
                            <select name="country" class="nb-select nb-select-country">
                                <option value="">{{ __('app.ports.country') }}</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->iso3 }}" data-src="{{ !empty($c->iso2) ? 'https://flagcdn.com/w20/'.strtolower($c->iso2).'.png' : '' }}" {{ request('country') === $c->iso3 ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ports.index') }}" class="nb-btn nb-btn-outline flex-fill">{{ __('app.ports.btn_clear') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="nb-card" id="portListContainer">
                <div class="nb-card-header">{{ __('app.ports.list') }} ({{ $ports->total() }})</div>
                <div class="nb-card-body p-0">
                    <div style="max-height:400px;overflow-y:auto">
                        @foreach($ports as $port)
                        <div class="country-dropdown-item port-list-item" data-lat="{{ $port->latitude }}" data-lng="{{ $port->longitude }}" data-name="{{ $port->name }}" data-iso2="{{ $port->country->iso2 ?? '' }}" style="cursor:pointer; display:flex; gap:12px; align-items:center; border-bottom:1px solid var(--border); padding: 12px 16px;">
                            <div style="width:24px;height:24px;background:var(--nb-pink);border:2px solid #000;box-shadow:2px 2px 0 #000;display:flex;align-items:center;justify-content:center;font-size:0.7rem;border-radius:50%;color:#fff"><i class="bi bi-geo-alt-fill"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight:700;font-size:0.88rem; color:var(--text-dark);">{{ $port->name }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted); display:flex; align-items:center; gap:6px; margin-top:2px;">
                                    @if($port->country && $port->country->iso2)
                                        <img src="https://flagcdn.com/w20/{{ strtolower($port->country->iso2) }}.png" width="16" alt="Flag" style="border-radius:2px;">
                                    @endif
                                    {{ $port->country_name }} &bull; {{ $port->un_locode }}
                                </div>
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
    html: `<div style="width:24px;height:24px;background:var(--nb-pink);border:2px solid #000;box-shadow:3px 3px 0 #000;display:flex;align-items:center;justify-content:center;font-size:0.7rem;border-radius:50%"><i class="bi bi-geo-alt-fill"></i></div>`,
    className: '', iconSize: [24, 24], iconAnchor: [12, 12]
});

let mapMarkers = [];
let isInitialLoad = true;

function updateMapMarkers(searchParams) {
    fetch('/api/ports?' + searchParams)
        .then(r => r.json())
        .then(ports => {
            mapMarkers.forEach(m => portMap.removeLayer(m));
            mapMarkers = [];
            let bounds = L.latLngBounds();
            
            ports.forEach(p => {
                if (p.latitude && p.longitude) {
                    const iso2 = p.country?.iso2 || '';
                    const flagHtml = iso2 ? `<img src="https://flagcdn.com/w20/${iso2.toLowerCase()}.png" width="16" style="border-radius:2px; vertical-align:middle; margin-right:4px;">` : '';
                    const marker = L.marker([p.latitude, p.longitude], { icon: portIcon })
                        .addTo(portMap)
                        .bindPopup(`<div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">${flagHtml}${p.name}</div><div style="font-size:0.75rem">${p.country_name}<br><code>${p.un_locode || ''}</code></div>`);
                    mapMarkers.push(marker);
                    bounds.extend([p.latitude, p.longitude]);
                }
            });
            
            if (!isInitialLoad) {
                if (mapMarkers.length === 1 && bounds.isValid()) {
                    portMap.flyTo(bounds.getCenter(), 8, { duration: 1.5 });
                } else if (mapMarkers.length > 1 && bounds.isValid()) {
                    portMap.flyToBounds(bounds, { padding: [50, 50], maxZoom: 8, duration: 1.5 });
                } else {
                    portMap.flyTo([20, 0], 2, { duration: 1.5 });
                }
            } else {
                if (mapMarkers.length > 0 && bounds.isValid()) {
                    portMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 8 });
                }
                isInitialLoad = false;
            }
        });
}

function bindListEvents() {
    document.querySelectorAll('.port-list-item').forEach(el => {
        el.addEventListener('click', () => {
            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);
            const name = el.dataset.name;
            const iso2 = el.dataset.iso2;
            
            if (!isNaN(lat) && !isNaN(lng)) {
                portMap.flyTo([lat, lng], 10, { duration: 1.5 });
                const flagHtml = iso2 ? `<img src="https://flagcdn.com/w20/${iso2.toLowerCase()}.png" width="16" style="border-radius:2px; vertical-align:middle; margin-right:4px;">` : '';
                
                // Use setTimeout to wait for the flight to finish before opening popup, or open immediately
                L.popup()
                    .setLatLng([lat, lng])
                    .setContent(`<div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">${flagHtml}${name}</div>`)
                    .openOn(portMap);
            }
        });
    });
}

// Initial load
updateMapMarkers('');
bindListEvents();

const searchForm = document.getElementById('portsSearchForm');
const searchInput = searchForm.querySelector('input[name="search"]');
const countrySelect = searchForm.querySelector('select[name="country"]');

function performSearch() {
    const params = new URLSearchParams(new FormData(searchForm)).toString();
    
    // Update map markers
    updateMapMarkers(params);

    // Update list via HTML fetch
    fetch('{{ route('ports.index') }}?' + params)
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newList = doc.getElementById('portListContainer');
            if (newList) {
                document.getElementById('portListContainer').innerHTML = newList.innerHTML;
                bindListEvents();
            }
        });
}

searchForm.addEventListener('submit', (e) => {
    e.preventDefault();
    performSearch();
});

let debounceTimeout;
searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        performSearch();
    }, 500);
});

countrySelect.addEventListener('change', () => {
    performSearch();
});
</script>
@endpush
