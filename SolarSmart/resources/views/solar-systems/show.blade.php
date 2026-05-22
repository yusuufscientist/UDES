@extends('layouts.app')

@section('title', $solarSystem->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">{{ $solarSystem->name }}</h1>
        <p class="text-muted mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $solarSystem->location }}</p>
    </div>
    <div>
        <a href="{{ route('solar-systems.edit', $solarSystem) }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <a href="{{ route('solar-systems.panels.create', $solarSystem) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Panel
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Today's Production</h6>
                    <h3 class="mb-0">{{ number_format($stats['today_production'], 2) }} <small class="fs-6">kWh</small></h3>
                </div>
                <div class="stat-icon bg-solar">
                    <i class="bi bi-lightning-charge"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Monthly Production</h6>
                    <h3 class="mb-0">{{ number_format($stats['month_production'], 2) }} <small class="fs-6">kWh</small></h3>
                </div>
                <div class="stat-icon bg-info-light">
                    <i class="bi bi-calendar-month"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Active Panels</h6>
                    <h3 class="mb-0">{{ $stats['active_panels'] }} <small class="fs-6">/ {{ $stats['total_panels'] }}</small></h3>
                </div>
                <div class="stat-icon bg-success-light">
                    <i class="bi bi-sun"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Efficiency</h6>
                    <h3 class="mb-0">{{ number_format($stats['efficiency'], 1) }}<small class="fs-6">%</small></h3>
                </div>
                <div class="stat-icon bg-{{ $stats['efficiency'] >= 80 ? 'success' : ($stats['efficiency'] >= 50 ? 'warning' : 'danger') }}-light">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Panels List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-sun me-2"></i>Solar Panels</span>
                <a href="{{ route('solar-systems.panels.index', $solarSystem) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($solarSystem->panels->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Serial Number</th>
                                    <th>Model</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($solarSystem->panels as $panel)
                                    <tr>
                                        <td>{{ $panel->serial_number }}</td>
                                        <td>{{ $panel->model }}</td>
                                        <td>{{ $panel->capacity_watts }} W</td>
                                        <td>
                                            <span class="badge bg-{{ $panel->status === 'active' ? 'success' : ($panel->status === 'faulty' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($panel->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('solar-systems.panels.show', [$solarSystem, $panel]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-sun text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">No panels added yet</p>
                        <a href="{{ route('solar-systems.panels.create', $solarSystem) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Add Panel
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Active Alerts</span>
                @if($stats['active_alerts'] > 0)
                    <span class="badge bg-danger">{{ $stats['active_alerts'] }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($solarSystem->alerts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($solarSystem->alerts as $alert)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-{{ $alert->severityColor() }}">{{ $alert->severity }}</span>
                                    </h6>
                                    <small class="text-muted">{{ $alert->triggered_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $alert->title }}</p>
                                <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Acknowledge</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">No active alerts</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- System Details -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>System Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Installation Date</small>
                        <strong>{{ $solarSystem->installation_date->format('M d, Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Capacity</small>
                        <strong>{{ $solarSystem->total_capacity_kw }} kW</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $solarSystem->status === 'active' ? 'success' : ($solarSystem->status === 'maintenance' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($solarSystem->status) }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Coordinates</small>
                        <strong>{{ $solarSystem->latitude ? $solarSystem->latitude . ', ' . $solarSystem->longitude : 'N/A' }}</strong>
                    </div>
                </div>
                @if($solarSystem->description)
                    <div class="mt-3">
                        <small class="text-muted d-block">Description</small>
                        <p class="mb-0">{{ $solarSystem->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
