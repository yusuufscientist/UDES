@extends('layouts.app')

@section('title', 'Fault Simulation Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2"><i class="bi bi-bug-fill me-2"></i>Fault Simulation Details</h1>
    <a href="{{ route('fault-simulations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Simulations
    </a>
</div>

<div class="row g-4">
    <!-- Fault Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-info-circle me-2"></i>Fault Information</strong>
                <span class="badge bg-{{ $faultSimulation->severityColor() }} fs-6">
                    {{ ucfirst($faultSimulation->severity) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block">Fault Type</strong>
                        <span class="fs-5">{{ $faultSimulation->faultTypeLabel() }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block">Triggered At</strong>
                        <span>{{ $faultSimulation->triggered_at->format('M d, Y H:i:s') }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong class="text-muted d-block">Description</strong>
                    <p class="mb-0">{{ $faultSimulation->fault_description }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <strong class="text-muted d-block">Status</strong>
                        <span class="badge bg-{{ $faultSimulation->statusBadge() }}">
                            {{ $faultSimulation->resolved ? 'Resolved' : 'Active' }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block">Previous Panel Status</strong>
                        <span class="badge bg-secondary">{{ ucfirst($faultSimulation->previous_status) }}</span>
                    </div>
                </div>

                @if($faultSimulation->resolved)
                <div class="mt-3 p-3 bg-success bg-opacity-10 rounded">
                    <strong class="text-success"><i class="bi bi-check-circle me-1"></i>Resolved</strong>
                    <span class="text-muted ms-2">at {{ $faultSimulation->resolved_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Linked Alert -->
        @if($faultSimulation->generatedAlert)
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Generated Alert
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">{{ $faultSimulation->generatedAlert->title }}</h5>
                        <p class="text-muted mb-2">{{ $faultSimulation->generatedAlert->message }}</p>
                        <div class="d-flex gap-3">
                            <span class="badge bg-{{ $faultSimulation->generatedAlert->severityColor() }}">
                                {{ ucfirst($faultSimulation->generatedAlert->severity) }}
                            </span>
                            <span class="badge bg-{{ $faultSimulation->generatedAlert->statusBadge() }}">
                                {{ ucfirst($faultSimulation->generatedAlert->status) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('alerts.show', $faultSimulation->generatedAlert) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View Alert
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Linked Intervention -->
        @if($faultSimulation->generatedIntervention)
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-tools me-2 text-info"></i>Generated Intervention Ticket
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-1">Intervention #{{ $faultSimulation->generatedIntervention->id }}</h5>
                        <p class="text-muted mb-2">{{ Str::limit($faultSimulation->generatedIntervention->description, 150) }}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-info">{{ $faultSimulation->generatedIntervention->typeLabel() }}</span>
                            <span class="badge bg-{{ $faultSimulation->generatedIntervention->priorityColor() }}">
                                {{ ucfirst($faultSimulation->generatedIntervention->priority) }}
                            </span>
                            <span class="badge bg-{{ $faultSimulation->generatedIntervention->statusBadge() }}">
                                {{ ucfirst(str_replace('_', ' ', $faultSimulation->generatedIntervention->status)) }}
                            </span>
                        </div>
                        <div class="mt-2 text-muted">
                            <small>
                                <i class="bi bi-calendar me-1"></i>Scheduled: {{ $faultSimulation->generatedIntervention->scheduled_date->format('M d, Y') }}
                                @if($faultSimulation->generatedIntervention->technician)
                                <span class="ms-3"><i class="bi bi-person me-1"></i>Technician: {{ $faultSimulation->generatedIntervention->technician->name }}</span>
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('interventions.show', $faultSimulation->generatedIntervention) }}" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Panel & System Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-grid-3x3 me-2"></i>Panel Information
            </div>
            <div class="card-body">
                <h5 class="mb-1">Serial: <code>{{ $faultSimulation->panel->serial_number }}</code></h5>
                <p class="text-muted mb-3">{{ $faultSimulation->panel->model }} by {{ $faultSimulation->panel->manufacturer }}</p>
                
                <div class="mb-2">
                    <strong class="text-muted">Current Status:</strong>
                    <span class="badge bg-{{ $faultSimulation->panel->status === 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($faultSimulation->panel->status) }}
                    </span>
                </div>
                <div class="mb-2">
                    <strong class="text-muted">Capacity:</strong>
                    <span>{{ $faultSimulation->panel->capacity_watts }}W</span>
                </div>
                @if($faultSimulation->panel->efficiency_rating)
                <div class="mb-2">
                    <strong class="text-muted">Efficiency:</strong>
                    <span>{{ $faultSimulation->panel->efficiency_rating }}%</span>
                </div>
                @endif
                <div>
                    <a href="{{ route('panels.show', [$faultSimulation->solarSystem, $faultSimulation->panel]) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-eye me-1"></i>View Panel Details
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-sun me-2"></i>Solar System
            </div>
            <div class="card-body">
                <h5 class="mb-1">{{ $faultSimulation->solarSystem->name }}</h5>
                <p class="text-muted mb-2">{{ $faultSimulation->solarSystem->location }}</p>
                <div>
                    <a href="{{ route('solar-systems.show', $faultSimulation->solarSystem) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-eye me-1"></i>View System
                    </a>
                </div>
            </div>
        </div>

        @if($faultSimulation->creator)
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Triggered By
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $faultSimulation->creator->name }}</p>
                <small class="text-muted">{{ $faultSimulation->creator->email }}</small>
            </div>
        </div>
        @endif

        @if(!$faultSimulation->resolved)
        <div class="card mt-3 border-danger">
            <div class="card-body text-center">
                <form action="{{ route('fault-simulations.resolve', $faultSimulation) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle me-2"></i>Resolve This Fault
                    </button>
                </form>
                <small class="text-muted mt-2 d-block">This will restore the panel to active status and resolve linked alerts/interventions</small>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
