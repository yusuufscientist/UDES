@extends('layouts.app')

@section('title', 'Intervention Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Intervention Details</h1>
    <a href="{{ route('interventions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tools me-2"></i>Intervention Information</span>
                <span class="badge bg-{{ $intervention->statusBadge() }}">{{ ucfirst($intervention->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Type</small>
                        <strong>{{ $intervention->typeLabel() }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Priority</small>
                        <span class="badge bg-{{ $intervention->priorityColor() }}">{{ ucfirst($intervention->priority) }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Solar System</small>
                        <a href="{{ route('solar-systems.show', $intervention->solarSystem) }}">
                            {{ $intervention->solarSystem->name }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Panel</small>
                        @if($intervention->panel)
                            {{ $intervention->panel->serial_number }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Technician</small>
                        <strong>{{ $intervention->technician->name }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Scheduled Date</small>
                        <strong>{{ $intervention->scheduled_date->format('M d, Y') }}</strong>
                    </div>
                </div>

                @if($intervention->completed_date)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Completed Date</small>
                            <strong>{{ $intervention->completed_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Duration</small>
                            <strong>{{ $intervention->duration_minutes }} minutes</strong>
                        </div>
                    </div>
                @endif

                @if($intervention->cost)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Cost</small>
                            <strong>${{ number_format($intervention->cost, 2) }}</strong>
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block">Description</small>
                    <p class="mb-0">{{ $intervention->description }}</p>
                </div>

                @if($intervention->parts_replaced)
                    <div class="mb-3">
                        <small class="text-muted d-block">Parts Replaced</small>
                        <p class="mb-0">{{ $intervention->parts_replaced }}</p>
                    </div>
                @endif

                @if($intervention->notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Notes</small>
                        <p class="mb-0">{{ $intervention->notes }}</p>
                    </div>
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
                @if($intervention->status === 'scheduled')
                    <form action="{{ route('interventions.start', $intervention) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-fill me-2"></i>Start Intervention
                        </button>
                    </form>
                @endif

                @if($intervention->status === 'in_progress')
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="bi bi-check-lg me-2"></i>Complete
                    </button>
                @endif

                <a href="{{ route('interventions.edit', $intervention) }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>

                <form action="{{ route('interventions.destroy', $intervention) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this intervention?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>

        @if($intervention->alert)
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-exclamation-triangle me-2"></i>Related Alert
                </div>
                <div class="card-body">
                    <h6>{{ $intervention->alert->title }}</h6>
                    <p class="text-muted small">{{ $intervention->alert->message }}</p>
                    <a href="{{ route('alerts.show', $intervention->alert) }}" class="btn btn-sm btn-outline-warning">View Alert</a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Complete Modal -->
@if($intervention->status === 'in_progress')
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('interventions.complete', $intervention) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Intervention</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Duration (minutes) *</label>
                        <input type="number" name="duration_minutes" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parts Replaced</label>
                        <textarea name="parts_replaced" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cost</label>
                        <input type="number" step="0.01" name="cost" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
