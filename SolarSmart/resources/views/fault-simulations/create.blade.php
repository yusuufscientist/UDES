@extends('layouts.app')

@section('title', 'Trigger Fault Simulation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2"><i class="bi bi-lightning-charge-fill me-2 text-danger"></i>Trigger Fault Simulation</h1>
    <a href="{{ route('fault-simulations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Simulations
    </a>
</div>

<div class="card">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle me-2"></i>Fault Simulation for {{ $solarSystem->name }}
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Warning:</strong> Triggering a fault simulation will:
            <ul class="mb-0 mt-2">
                <li>Change the selected panel's status from <strong>Active</strong> to <strong>Inactive</strong></li>
                <li>Generate a high-priority alert in the Alerts Management module</li>
                <li>Automatically schedule a technical intervention ticket</li>
            </ul>
        </div>

        <form action="{{ route('solar-systems.fault-simulations.store', $solarSystem) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="panel_id" class="form-label fw-bold">
                    <i class="bi bi-grid-3x3 me-1"></i>Select Panel
                </label>
                @if($activePanels->count() > 0)
                    <select name="panel_id" id="panel_id" class="form-select @error('panel_id') is-invalid @enderror" required>
                        <option value="">-- Select Active Panel --</option>
                        @foreach($activePanels as $panel)
                            <option value="{{ $panel->id }}">
                                {{ $panel->serial_number }} - {{ $panel->model }} ({{ $panel->manufacturer }}, {{ $panel->capacity_watts }}W)
                            </option>
                        @endforeach
                    </select>
                    @error('panel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @else
                    <p class="text-muted">No active panels available in this solar system.</p>
                @endif
            </div>

            <div class="mb-4">
                <label for="fault_type" class="form-label fw-bold">
                    <i class="bi bi-bug me-1"></i>Select Fault Type
                </label>
                <select name="fault_type" id="fault_type" class="form-select @error('fault_type') is-invalid @enderror" required>
                    <option value="">-- Choose Fault Type --</option>
                    @foreach($faultTypes as $type)
                        <option value="{{ $type['key'] }}">
                            {{ $type['label'] }} [{{ ucfirst($type['severity']) }}]
                        </option>
                    @endforeach
                </select>
                @error('fault_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="faultDescription" class="mb-4" style="display: none;">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">Fault Description Preview</h6>
                        <p id="faultDescText" class="card-text text-muted mb-0"></p>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger btn-lg" id="submitBtn" disabled>
                    <i class="bi bi-lightning-charge-fill me-2"></i>Trigger Fault Simulation
                </button>
                <a href="{{ route('fault-simulations.index') }}" class="btn btn-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faultTypeSelect = document.getElementById('fault_type');
    const submitBtn = document.getElementById('submitBtn');
    const panelSelect = document.getElementById('panel_id');
    const faultDescription = document.getElementById('faultDescription');
    const faultDescText = document.getElementById('faultDescText');

    const faultDescriptions = @json(collect($faultTypes)->pluck('description', 'key'));

    function updateButton() {
        const hasPanel = panelSelect && panelSelect.value !== '';
        const hasFault = faultTypeSelect.value !== '';
        submitBtn.disabled = !(hasPanel && hasFault);

        if (hasFault && faultDescriptions[faultTypeSelect.value]) {
            faultDescText.textContent = faultDescriptions[faultTypeSelect.value];
            faultDescription.style.display = 'block';
        } else {
            faultDescription.style.display = 'none';
        }
    }

    faultTypeSelect.addEventListener('change', updateButton);
    if (panelSelect) {
        panelSelect.addEventListener('change', updateButton);
    }
});
</script>
@endpush
