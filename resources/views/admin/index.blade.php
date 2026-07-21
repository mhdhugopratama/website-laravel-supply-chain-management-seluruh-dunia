@extends('layouts.app')
@section('title', 'Admin Dashboard | GoSupply')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1>Admin Control Panel</h1>
            <p class="text-muted mb-0">Manage users, sea ports, and editorial content.</p>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.users') }}" class="text-decoration-none">
                <div class="nb-admin-stat">
                    <div class="nb-stat-label">Total Users</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-cyan)">{{ $userCount }}</div>
                    <div class="nb-badge nb-badge-info mt-1">Manage</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.ports') }}" class="text-decoration-none">
                <div class="nb-admin-stat">
                    <div class="nb-stat-label"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi me-1" viewBox="0 0 16 16"><path d="M7.5 11.5a.5.5 0 0 0 1 0v-4h-1v4z"/><path d="M8 0a.5.5 0 0 0-.5.5v1.077c-1.396.195-2.5 1.4-2.5 2.923a3 3 0 0 0 2.5 2.923V9h-3V7.5a.5.5 0 0 0-1 0v3.5a1.5 1.5 0 0 0 1.5 1.5h2.5v1.577C5.104 14.2 3 12.33 3 10a.5.5 0 0 0-1 0c0 3 2.686 5.5 6 5.5s6-2.5 6-5.5a.5.5 0 0 0-1 0c0 2.33-2.104 4.2-4.5 4.077V12.5h2.5A1.5 1.5 0 0 0 12 11V7.5a.5.5 0 0 0-1 0V9h-3V7.423A3 3 0 0 0 10.5 4.5c0-1.523-1.104-2.728-2.5-2.923V.5A.5.5 0 0 0 8 0zm0 2.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg> Total Sea Ports</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-pink)">{{ $portCount }}</div>
                    <div class="nb-badge nb-badge-danger mt-1">Manage</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.articles') }}" class="text-decoration-none">
                <div class="nb-admin-stat">
                    <div class="nb-stat-label">Articles</div>
                    <div class="nb-admin-stat-number" style="color:var(--nb-green)">{{ $articleCount }}</div>
                    <div class="nb-badge nb-badge-success mt-1">Manage</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="nb-card">
                <div class="nb-card-header">Quick Actions</div>
                <div class="nb-card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.users') }}" class="nb-btn nb-btn-cyan"><span style="width:20px; display:inline-flex; justify-content:center; align-items:center; flex-shrink:0;"><i class="bi bi-people"></i></span> <span>Manage Users</span></a>
                    <a href="{{ route('admin.ports') }}" class="nb-btn nb-btn-purple"><span style="width:20px; display:inline-flex; justify-content:center; align-items:center; flex-shrink:0;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M7.5 11.5a.5.5 0 0 0 1 0v-4h-1v4z"/><path d="M8 0a.5.5 0 0 0-.5.5v1.077c-1.396.195-2.5 1.4-2.5 2.923a3 3 0 0 0 2.5 2.923V9h-3V7.5a.5.5 0 0 0-1 0v3.5a1.5 1.5 0 0 0 1.5 1.5h2.5v1.577C5.104 14.2 3 12.33 3 10a.5.5 0 0 0-1 0c0 3 2.686 5.5 6 5.5s6-2.5 6-5.5a.5.5 0 0 0-1 0c0 2.33-2.104 4.2-4.5 4.077V12.5h2.5A1.5 1.5 0 0 0 12 11V7.5a.5.5 0 0 0-1 0V9h-3V7.423A3 3 0 0 0 10.5 4.5c0-1.523-1.104-2.728-2.5-2.923V.5A.5.5 0 0 0 8 0zm0 2.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg></span> <span>Manage Sea Ports</span></a>
                    <a href="{{ route('admin.articles') }}" class="nb-btn nb-btn-success"><span style="width:20px; display:inline-flex; justify-content:center; align-items:center; flex-shrink:0;"><i class="bi bi-journal-plus"></i></span> <span>Manage Articles</span></a>
                    <a href="{{ route('admin.articles.create') }}" class="nb-btn nb-btn-primary"><span style="width:20px; display:inline-flex; justify-content:center; align-items:center; flex-shrink:0;"><i class="bi bi-pencil-square"></i></span> <span>Write New Article</span></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <div class="nb-card">
                <div class="nb-card-header">System Overview</div>
                <div class="nb-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <table class="nb-table mb-0">
                                <tr><td>Laravel Version</td><td><strong>12.x</strong></td></tr>
                                <tr><td>Database</td><td><strong>MySQL</strong></td></tr>
                                <tr><td>Weather API</td><td><span class="nb-badge nb-badge-success">Open-Meteo API</span></td></tr>
                                <tr><td>Economic Data</td><td><span class="nb-badge nb-badge-info">World Bank API</span></td></tr>
                                <tr><td>Exchange Rates</td><td><span class="nb-badge nb-badge-info">ExchangeRate API</span></td></tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table class="nb-table mb-0">
                                <tr><td>News API</td><td><span class="nb-badge nb-badge-purple">GNews + DB Cache</span></td></tr>
                                <tr><td>Maps & Geolocation</td><td><span class="nb-badge nb-badge-success">Leaflet + OpenStreetMap</span></td></tr>
                                <tr><td>Country Data</td><td><span class="nb-badge nb-badge-warning">REST Countries API</span></td></tr>
                                <tr><td>Marine Traffic / Ports</td><td><span class="nb-badge nb-badge-danger">World Port Index Dataset</span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
