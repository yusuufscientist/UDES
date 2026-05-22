@extends('layouts.app')

@section('title', 'Alert Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Alert Details</h1>
    <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Alert Information</span>
                <span class="badge bg-{{ $alert->statusBadge() }}">{{ ucfirst($alert->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Title</small>
                        <h5 class="mb-0">{{ $alert->title }}</h5>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Type</small>
                        <strong>{{ $alert->typeLabel() }}</strong>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Severity</small>
                        <span class="badge bg-{{ $alert->severityColor() }}">{{ ucfirst($alert->severity) }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Triggered At</small>
                        <strong>{{ $alert->triggered_at->format('M d, Y H:i') }}</strong>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Solar System</small>
                        <a href="{{ route('solar-systems.show', $alert->solarSystem) }}">
                            {{ $alert->solarSystem->name }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Panel</small>
                        @if($alert->panel)
                            <a href="{{ route('solar-systems.panels.show', [$alert->solarSystem, $alert->panel]) }}">
                                {{ $alert->panel->serial_number }}
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Message</small>
                    <p class="mb-0">{{ $alert->message }}</p>
                </div>

                @if($alert->acknowledged_at)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Acknowledged At</small>
                            <strong>{{ $alert->acknowledged_at->format('M d, Y H:i') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Acknowledged By</small>
                            <strong>{{ $alert->acknowledgedBy->name }}</strong>
                        </div>
                    </div>
                @endif

                @if($alert->resolved_at)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Resolved At</small>
                            <strong>{{ $alert->resolved_at->format('M d, Y H:i') }}</strong>
                        </div>
                    </div>
                    @if($alert->resolution_notes)
                        <div class="mb-3">
                            <small class="text-muted d-block">Resolution Notes</small>
                            <p class="mb-0">{{ $alert->resolution_notes }}</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Actions
            </div>
            <div class="card-body">
                @if($alert->status === 'active')
                    <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-check me-2"></i>Acknowledge
                        </button>
                    </form>
                @endif

                @if($alert->status !== 'resolved')
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolveModal">
                        <i class="bi bi-check-circle me-2"></i>Resolve
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
@if($alert->status !== 'resolved')
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('alerts.resolve', $alert) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="3" placeholder="Describe how this alert was resolved..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Resolve Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
