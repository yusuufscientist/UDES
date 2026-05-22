@extends('layouts.app')

@section('title', 'Technician Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Technician Dashboard</h1>
    <a href="{{ route('technician.maintenance') }}" class="btn btn-solar">
        <i class="bi bi-wrench me-2"></i>Maintenance Needed
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-warning mx-auto mb-2">
                <i class="bi bi-calendar-day"></i>
            </div>
            <h3 class="mb-1">{{ $stats['today_count'] }}</h3>
            <small class="text-muted">Today's Tasks</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-info mx-auto mb-2">
                <i class="bi bi-calendar-week"></i>
            </div>
            <h3 class="mb-1">{{ $stats['upcoming_count'] }}</h3>
            <small class="text-muted">Upcoming</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-success mx-auto mb-2">
                <i class="bi bi-check-circle"></i>
            </div>
            <h3 class="mb-1">{{ $stats['completed_today'] }}</h3>
            <small class="text-muted">Completed Today</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-primary mx-auto mb-2">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <h3 class="mb-1">{{ $stats['completed_total'] }}</h3>
            <small class="text-muted">Total Completed</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Interventions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-day me-2"></i>Today's Tasks</span>
                <span class="badge bg-warning">{{ $todayInterventions->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($todayInterventions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($todayInterventions as $intervention)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $intervention->typeLabel() }}</h6>
                                    <span class="badge bg-{{ $intervention->priorityColor() }}">
                                        {{ ucfirst($intervention->priority) }}
                                    </span>
                                </div>
                                <p class="mb-1">{{ $intervention->solarSystem->name }}</p>
                                @if($intervention->panel)
                                    <small class="text-muted">Panel: {{ $intervention->panel->serial_number }}</small>
                                @endif
                                <div class="mt-2">
                                    @if($intervention->status === 'scheduled')
                                        <form action="{{ route('interventions.start', $intervention) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Start</button>
                                        </form>
                                    @elseif($intervention->status === 'in_progress')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $intervention->id }}">
                                            Complete
                                        </button>
                                    @endif
                                    <a href="{{ route('interventions.show', $intervention) }}" class="btn btn-sm btn-outline-secondary">Details</a>
                                </div>
                            </div>

                            <!-- Complete Modal -->
                            <div class="modal fade" id="completeModal{{ $intervention->id }}" tabindex="-1">
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
                                                    <label class="form-label">Duration (minutes)</label>
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
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">No tasks scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Interventions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-week me-2"></i>Upcoming Tasks</span>
                <a href="{{ route('technician.interventions') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($upcomingInterventions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcomingInterventions as $intervention)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $intervention->typeLabel() }}</h6>
                                    <span class="badge bg-{{ $intervention->priorityColor() }}">
                                        {{ ucfirst($intervention->priority) }}
                                    </span>
                                </div>
                                <p class="mb-1">{{ $intervention->solarSystem->name }}</p>
                                <small class="text-muted">
                                    Scheduled: {{ $intervention->scheduled_date->format('M d, Y') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">No upcoming tasks</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
