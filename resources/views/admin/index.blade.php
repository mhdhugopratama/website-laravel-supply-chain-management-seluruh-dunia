@extends('layouts.app')
@section('title', 'Admin Dashboard | GoSupply')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4">
        <h1>Admin Control Panel</h1>
        <p>Manage users, ports, and editorial content</p>
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
                    <div class="nb-stat-label"><i class="bi bi-anchor"></i> Total Ports</div>
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
                    <a href="{{ route('admin.users') }}" class="nb-btn nb-btn-cyan"><i class="bi bi-people"></i> Manage Users</a>
                    <a href="{{ route('admin.ports') }}" class="nb-btn nb-btn-purple"><i class="bi bi-anchor"></i> Manage Ports</a>
                    <a href="{{ route('admin.articles') }}" class="nb-btn nb-btn-success"><i class="bi bi-journal-plus"></i> Manage Articles</a>
                    <a href="{{ route('admin.articles.create') }}" class="nb-btn nb-btn-primary"><i class="bi bi-pencil-square"></i> Write New Article</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <div class="nb-card">
                <div class="nb-card-header">System Overview</div>
                <div class="nb-card-body">
                    <table class="nb-table">
                        <tr><td>Laravel Version</td><td><strong>12.x</strong></td></tr>
                        <tr><td>Database</td><td><strong>MySQL</strong></td></tr>
                        <tr><td>Weather API</td><td><span class="nb-badge nb-badge-success">Open-Meteo (Free)</span></td></tr>
                        <tr><td>Economic Data</td><td><span class="nb-badge nb-badge-info">World Bank API</span></td></tr>
                        <tr><td>Exchange Rates</td><td><span class="nb-badge nb-badge-info">ExchangeRate API</span></td></tr>
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
@endsection
