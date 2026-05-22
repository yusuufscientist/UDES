@extends('layouts.app')

@section('title', 'Fault Simulations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2"><i class="bi bi-bug-fill me-2"></i>Fault Simulation & Maintenance</h1>
    <p class="text-muted mb-0">Simulate technical failures and trigger automated maintenance workflows</p>
</div>

<!-- Statistics Overview -->
<div class="row g-4 mb-4">
    @foreach($systems as $system)
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-sun me-2"></i>{{ $system->name }}</strong>
                <a href="{{ route('solar-systems.fault-simulations.create', $system) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-lightning-charge me-1"></i>Trigger Fault
                </a>
            </div>
            <div class="card-body">
                @php $sysStats = $stats[$system->id] ?? []; @endphp
                <div class="row text-center g-3">
                    <div class="col-6">
                        <h4 class="mb-0">{{ $sysStats['total_simulations'] ?? 0 }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-danger">{{ $sysStats['active_faults'] ?? 0 }}</h4>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-success">{{ $sysStats['resolved_faults'] ?? 0 }}</h4>
                        <small class="text-muted">Resolved</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-warning">{{ $sysStats['critical_count'] ?? 0 }}</h4>
                        <small class="text-muted">Critical</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Quick Trigger Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-lightning-charge-fill me-2"></i>Quick Fault Trigger
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Select a panel and fault type to simulate a technical failure. This will automatically set the panel to inactive, create a high-priority alert, and schedule an intervention ticket.</p>
        <form id="quickTriggerForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Select Solar System</label>
                    <select class="form-select" id="systemSelect">
                        <option value="">-- Choose System --</option>
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}" data-panels='@json($system->panels)'>{{ $system->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Panel</label>
                    <select class="form-select" id="panelSelect" disabled>
                        <option value="">-- Select System First --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fault Type</label>
                    <select class="form-select" id="faultTypeSelect">
                        <option value="">-- Choose Fault Type --</option>
                        @foreach($faultTypes as $type)
                            <option value="{{ $type['key'] }}" data-severity="{{ $type['severity'] }}">
                                {{ $type['label'] }} ({{ ucfirst($type['severity']) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-danger btn-lg" id="triggerBtn" disabled>
                    <i class="bi bi-bug-fill me-2"></i>Trigger Fault Simulation
                </button>
                <span class="text-muted ms-3" id="statusText">Select panel and fault type to proceed</span>
            </div>
        </form>
    </div>
</div>

<!-- Simulation History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Simulation History</span>
    </div>
    <div class="card-body p-0">
        @if($simulations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Triggered At</th>
                            <th>Panel Serial</th>
                            <th>System</th>
                            <th>Fault Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Alert</th>
                            <th>Intervention</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($simulations as $sim)
                        <tr>
                            <td>{{ $sim->triggered_at->format('M d, Y H:i') }}</td>
                            <td><code>{{ $sim->panel->serial_number }}</code></td>
                            <td>{{ $sim->solarSystem->name }}</td>
                            <td>{{ $sim->faultTypeLabel() }}</td>
                            <td>
                                <span class="badge bg-{{ $sim->severityColor() }}">
                                    {{ ucfirst($sim->severity) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $sim->statusBadge() }}">
                                    {{ $sim->resolved ? 'Resolved' : 'Active' }}
                                </span>
                            </td>
                            <td>
                                @if($sim->generatedAlert)
                                    <a href="{{ route('alerts.show', $sim->generatedAlert) }}" class="btn btn-sm btn-outline-warning" title="View Alert">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sim->generatedIntervention)
                                    <a href="{{ route('interventions.show', $sim->generatedIntervention) }}" class="btn btn-sm btn-outline-info" title="View Intervention">
                                        <i class="bi bi-tools"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(!$sim->resolved)
                                    <form action="{{ route('fault-simulations.resolve', $sim) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Resolve Fault">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('fault-simulations.show', $sim) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $simulations->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Fault Simulations</h5>
                <p class="text-muted">Trigger a fault simulation above to test the automated maintenance workflow</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const systemSelect = document.getElementById('systemSelect');
    const panelSelect = document.getElementById('panelSelect');
    const faultTypeSelect = document.getElementById('faultTypeSelect');
    const triggerBtn = document.getElementById('triggerBtn');
    const statusText = document.getElementById('statusText');
    const form = document.getElementById('quickTriggerForm');

    systemSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        panelSelect.innerHTML = '<option value="">-- Choose Panel --</option>';
        
        if (this.value) {
            const panels = JSON.parse(option.dataset.panels || '[]');
            panels.forEach(function(panel) {
                const opt = document.createElement('option');
                opt.value = panel.id;
                opt.textContent = panel.serial_number + ' - ' + panel.model + ' (' + panel.capacity_watts + 'W)';
                panelSelect.appendChild(opt);
            });
            panelSelect.disabled = false;
        } else {
            panelSelect.disabled = true;
        }
        updateTriggerButton();
    });

    panelSelect.addEventListener('change', updateTriggerButton);
    faultTypeSelect.addEventListener('change', updateTriggerButton);

    function updateTriggerButton() {
        const hasPanel = panelSelect.value !== '';
        const hasFault = faultTypeSelect.value !== '';
        triggerBtn.disabled = !(hasPanel && hasFault);
        
        if (hasPanel && hasFault) {
            const severity = faultTypeSelect.options[faultTypeSelect.selectedIndex].dataset.severity;
            const colors = { critical: 'text-danger', high: 'text-warning', medium: 'text-info', low: 'text-success' };
            statusText.innerHTML = 'Ready to trigger: <span class="' + (colors[severity] || '') + '">' + faultTypeSelect.options[faultTypeSelect.selectedIndex].text + '</span>';
        } else {
            statusText.textContent = 'Select panel and fault type to proceed';
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const panelId = panelSelect.value;
        const faultType = faultTypeSelect.value;
        
        triggerBtn.disabled = true;
        triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Triggering...';

        fetch('{{ route('fault-simulations.quick-trigger') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ panel_id: panelId, fault_type: faultType }),
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Fault Simulation Successful!\n\nPanel: ' + data.panel.serial_number + '\nFault: ' + data.fault.label + '\nSeverity: ' + data.fault.severity + '\n\nAlert #' + data.alert_id + ' and Intervention #' + data.intervention_id + ' have been automatically created.');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = '<i class="bi bi-bug-fill me-2"></i>Trigger Fault Simulation';
            }
        })
        .catch(function(err) {
            alert('Request failed: ' + err.message);
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = '<i class="bi bi-bug-fill me-2"></i>Trigger Fault Simulation';
        });
    });
});
</script>
@endpush
