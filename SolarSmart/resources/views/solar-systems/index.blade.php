@extends('layouts.app')

@section('title', 'Solar Systems')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Solar Systems</h1>
    <a href="{{ route('solar-systems.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add New System
    </a>
</div>

@if($solarSystems->count() > 0)
    <div class="row g-4">
        @foreach($solarSystems as $system)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $system->name }}</h5>
                        <span class="badge bg-{{ $system->status === 'active' ? 'success' : ($system->status === 'maintenance' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($system->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Location</small>
                            <span>{{ $system->location }}</span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Capacity</small>
                                <strong>{{ $system->total_capacity_kw }} kW</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Panels</small>
                                <strong>{{ $system->panels_count }}</strong>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Today's Production</small>
                                <strong class="text-success">{{ number_format($system->todayProduction(), 2) }} kWh</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Active Alerts</small>
                                <strong class="{{ $system->alerts_count > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $system->alerts_count }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('solar-systems.show', $system) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-sun text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Solar Systems Yet</h4>
            <p class="text-muted">Start by adding your first solar system to monitor</p>
            <a href="{{ route('solar-systems.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Add Solar System
            </a>
        </div>
    </div>
@endif
@endsection
