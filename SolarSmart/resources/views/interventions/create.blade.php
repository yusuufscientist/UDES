@extends('layouts.app')

@section('content')
@php
    $allSystems = \App\Models\SolarSystem::all();
    $allTechnicians = \App\Models\User::where('role', 'technician')->where('is_active', true)->get();
    $allPanels = $solarSystem ? $solarSystem->panels : [];
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ isset($solarSystem) ? 'Create Intervention for ' . $solarSystem->name : 'Create Intervention' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('interventions.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="solar_system_id" class="form-label">Solar System</label>
                            <select class="form-control @error('solar_system_id') is-invalid @enderror"
                                    id="solar_system_id" name="solar_system_id" required>
                                <option value="">Select a solar system</option>
                                @foreach($allSystems as $system)
                                    <option value="{{ $system->id }}" {{ (isset($solarSystem) && $solarSystem->id == $system->id) ? 'selected' : '' }}>{{ $system->name }} - {{ $system->location }}</option>
                                @endforeach
                            </select>
                            @error('solar_system_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="panel_id" class="form-label">Panel (Optional)</label>
                            <select class="form-control @error('panel_id') is-invalid @enderror"
                                    id="panel_id" name="panel_id">
                                <option value="">Select a panel</option>
                                @foreach($allPanels as $panel)
                                    <option value="{{ $panel->id }}">{{ $panel->serial_number }}</option>
                                @endforeach
                            </select>
                            @error('panel_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
<label for="technician_id" class="form-label">Technician</label>
            <select class="form-control @error('technician_id') is-invalid @enderror"
                    id="technician_id" name="technician_id" required>
                <option value="">Select a technician</option>
                @foreach($allTechnicians as $technician)
                    <option value="{{ $technician->id }}">{{ $technician->name }} ({{ $technician->role }})</option>
                @endforeach
                @foreach(\App\Models\User::whereIn('role', ['technician', 'admin'])->where('is_active', true)->get() as $user)
                    @if(!in_array($user->id, $allTechnicians->pluck('id')->toArray()))
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                    @endif
                @endforeach
            </select>
                            @error('technician_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-control @error('type') is-invalid @enderror"
                                    id="type" name="type" required>
                                <option value="routine_maintenance" {{ old('type') === 'routine_maintenance' ? 'selected' : '' }}>Routine Maintenance</option>
                                <option value="repair" {{ old('type') === 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="inspection" {{ old('type') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                                <option value="cleaning" {{ old('type') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                <option value="emergency_repair" {{ old('type') === 'emergency_repair' ? 'selected' : '' }}>Emergency Repair</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control @error('priority') is-invalid @enderror"
                                    id="priority" name="priority" required>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="scheduled_date" class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                   id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date') }}" required>
                            @error('scheduled_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Intervention</button>
                            <a href="{{ route('interventions.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
