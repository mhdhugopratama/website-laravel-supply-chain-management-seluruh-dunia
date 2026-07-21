@extends('layouts.app')
@section('title', 'Manage Ports | Admin')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <div>
            <h1>Port Dataset Management</h1>
            <p>{{ $ports->total() }} ports in database</p>
        </div>
        <a href="{{ route('admin.index') }}" class="nb-btn nb-btn-outline">Back</a>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="nb-card mb-4">
        <div class="nb-card-header">Add New Port</div>
        <div class="nb-card-body">
            <form method="POST" action="{{ route('admin.ports.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-12 col-md-4"><input type="text" name="name" class="nb-input" placeholder="Port name *" required></div>
                    <div class="col-6 col-md-2"><input type="text" name="country_code" class="nb-input" placeholder="ISO3 (e.g. SGP)"></div>
                    <div class="col-6 col-md-2"><input type="text" name="country_name" class="nb-input" placeholder="Country name"></div>
                    <div class="col-6 col-md-2"><input type="number" name="latitude" class="nb-input" placeholder="Latitude" step="any"></div>
                    <div class="col-6 col-md-2"><input type="number" name="longitude" class="nb-input" placeholder="Longitude" step="any"></div>
                    <div class="col-6 col-md-2"><input type="text" name="un_locode" class="nb-input" placeholder="UN/LOCODE"></div>
                    <div class="col-6 col-md-2"><input type="text" name="type" class="nb-input" placeholder="Type"></div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="nb-btn nb-btn-success w-100"><i class="bi bi-plus"></i> Add Port</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="nb-card">
        <div class="nb-card-body p-0">
            <table class="nb-table">
                <thead>
                    <tr><th>Name</th><th>Country</th><th>Coords</th><th>LOCODE</th><th>Type</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach($ports as $port)
                    <tr>
                        <td><strong>{{ $port->name }}</strong></td>
                        <td>{{ $port->country_name }} ({{ $port->country_code }})</td>
                        <td style="font-size:0.78rem">{{ $port->latitude }}, {{ $port->longitude }}</td>
                        <td><code>{{ $port->un_locode }}</code></td>
                        <td>{{ $port->type }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.ports.delete', $port) }}" onsubmit="return confirm('Delete this port?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="nb-btn nb-btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="nb-card-body pt-0">
            <div class="nb-pagination">{{ $ports->links('vendor.pagination.nb') }}</div>
        </div>
    </div>
</div>
@endsection
