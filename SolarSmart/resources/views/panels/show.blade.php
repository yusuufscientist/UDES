@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Panel: {{ $panel->serial_number }}</h1>
        <div>
            @if($panel->status === 'active')
            <button class="btn btn-danger fault-trigger-btn" data-panel-id="{{ $panel->id }}" data-serial="{{ $panel->serial_number }}">
                <i class="bi bi-bug me-1"></i>Simulate Fault
            </button>
            @endif
            <a href="{{ route('solar-systems.panels.edit', [$solarSystem, $panel]) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('solar-systems.panels.index', $solarSystem) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Panel Details</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Serial Number:</th>
                            <td>{{ $panel->serial_number }}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>{{ $panel->model }}</td>
                        </tr>
                        <tr>
                            <th>Manufacturer:</th>
                            <td>{{ $panel->manufacturer }}</td>
                        </tr>
                        <tr>
                            <th>Capacity:</th>
                            <td>{{ $panel->capacity_watts }}W</td>
                        </tr>
                        <tr>
                            <th>Efficiency Rating:</th>
                            <td>{{ $panel->efficiency_rating ?? 'N/A' }}%</td>
                        </tr>
                        <tr>
                            <th>Installation Date:</th>
                            <td>{{ $panel->installation_date }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $panel->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $panel->status }}
                                </span>
                            </td>
                        </tr>
                        @if($panel->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $panel->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Statistics</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4>{{ $stats['today_production'] ?? 0 }} kWh</h4>
                            <p class="text-muted">Today's Production</p>
                        </div>
                        <div class="col-6">
                            <h4>{{ $stats['efficiency'] ?? 0 }}%</h4>
                            <p class="text-muted">Efficiency</p>
                        </div>
                    </div>
                    <div class="alert alert-{{ $stats['is_producing_normally'] ? 'success' : 'warning' }} mt-3">
                        {{ $stats['is_producing_normally'] ? 'Panel is producing normally' : 'Panel is underperforming' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($panel->alerts->count() > 0)
    <div class="card mt-4">
        <div class="card-header">Recent Alerts</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Triggered</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($panel->alerts as $alert)
                    <tr>
                        <td>{{ $alert->title }}</td>
                        <td>{{ $alert->type }}</td>
                        <td>
                            <span class="badge bg-{{ $alert->severity === 'high' ? 'danger' : 'warning' }}">
                                {{ $alert->severity }}
                            </span>
                        </td>
                        <td>{{ $alert->status }}</td>
                        <td>{{ $alert->triggered_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Fault Simulation Modal -->
<div class="modal fade" id="faultSimulationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-bug-fill text-danger me-2"></i>Simulate Fault</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Trigger a fault for panel: <strong id="modalPanelSerial"></strong></p>
                <input type="hidden" id="modalPanelId">
                <div class="mb-3">
                    <label class="form-label">Fault Type</label>
                    <select id="modalFaultType" class="form-select bg-dark text-light border-secondary">
                        <option value="">-- Select Fault --</option>
                        <option value="inverter_failure">Inverter Failure (Critical)</option>
                        <option value="panel_crack">Panel Crack (High)</option>
                        <option value="wiring_fault">Wiring Fault (Critical)</option>
                        <option value="sensor_malfunction">Sensor Malfunction (Medium)</option>
                        <option value="hot_spot">Hot Spot Detection (High)</option>
                        <option value="delamination">Delamination (Medium)</option>
                        <option value="connection_failure">Connection Failure (High)</option>
                        <option value="soiling_severe">Severe Soiling (Low)</option>
                        <option value="shading_issue">Shading Issue (Low)</option>
                        <option value="ground_fault">Ground Fault (Critical)</option>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This will set the panel to inactive, create an alert, and schedule an intervention.
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmFaultBtn">Trigger Fault</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faultModal = new bootstrap.Modal(document.getElementById('faultSimulationModal'));
    const confirmBtn = document.getElementById('confirmFaultBtn');

    document.querySelectorAll('.fault-trigger-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('modalPanelId').value = this.dataset.panelId;
            document.getElementById('modalPanelSerial').textContent = this.dataset.serial;
            document.getElementById('modalFaultType').value = '';
            faultModal.show();
        });
    });

    confirmBtn.addEventListener('click', function() {
        const panelId = document.getElementById('modalPanelId').value;
        const faultType = document.getElementById('modalFaultType').value;

        if (!panelId || !faultType) {
            alert('Please select a fault type');
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Triggering...';

        fetch("{{ route('fault-simulations.quick-trigger') }}", {
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
                faultModal.hide();
                alert('Fault triggered! Alert #' + data.alert_id + ' and Intervention #' + data.intervention_id + ' created.');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Trigger Fault';
            }
        })
        .catch(function(err) {
            alert('Request failed: ' + err.message);
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Trigger Fault';
        });
    });

    document.getElementById('faultSimulationModal').addEventListener('hidden.bs.modal', function() {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Trigger Fault';
    });
});
</script>
@endpush
@endsection
