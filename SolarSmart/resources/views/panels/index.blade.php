@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Panels - {{ $solarSystem->name }}</h1>
        <a href="{{ route('solar-systems.panels.create', $solarSystem) }}" class="btn btn-primary">
            Add Panel
        </a>
    </div>

    @if($panels->isEmpty())
        <div class="alert alert-info">
            No panels found. Add your first panel to start monitoring.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Model</th>
                            <th>Manufacturer</th>
                            <th>Capacity (Watts)</th>
                            <th>Status</th>
                            <th>Alerts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($panels as $panel)
                        <tr>
                            <td>{{ $panel->serial_number }}</td>
                            <td>{{ $panel->model }}</td>
                            <td>{{ $panel->manufacturer }}</td>
                            <td>{{ $panel->capacity_watts }}W</td>
                            <td>
                                <span class="badge bg-{{ $panel->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $panel->status }}
                                </span>
                            </td>
                            <td>{{ $panel->alerts_count }}</td>
                            <td>
                                <a href="{{ route('solar-systems.panels.show', [$solarSystem, $panel]) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('solar-systems.panels.edit', [$solarSystem, $panel]) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('solar-systems.panels.destroy', [$solarSystem, $panel]) }}" method="POST" class="d-inline panel-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                                @if($panel->status === 'active')
                                    <button class="btn btn-sm btn-outline-danger fault-trigger-btn" data-panel-id="{{ $panel->id }}" data-serial="{{ $panel->serial_number }}">
                                        <i class="bi bi-bug"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
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
                alert('Fault triggered for ' + data.panel.serial_number + '! Alert #' + data.alert_id + ' and Intervention #' + data.intervention_id + ' created.');
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

    document.querySelectorAll('.panel-delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Delete this panel?')) return;
            var formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    'Accept': 'text/html'
                },
                body: formData,
                credentials: 'same-origin'
            }).then(function(response) {
                if (response.ok || response.status === 302) {
                    window.location.reload();
                } else {
                    return response.text().then(function(text) {
                        throw new Error('Delete failed: ' + response.status + ' - ' + text.substring(0, 200));
                    });
                }
            }).catch(function(err) {
                alert(err.message);
            });
        });
    });
});
</script>
@endpush
