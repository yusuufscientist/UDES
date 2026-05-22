@extends('layouts.app')

@section('title', 'Edit Intervention')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Intervention</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('interventions.update', $intervention) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="routine_maintenance" {{ $intervention->type === 'routine_maintenance' ? 'selected' : '' }}>Routine Maintenance</option>
                                <option value="repair" {{ $intervention->type === 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="inspection" {{ $intervention->type === 'inspection' ? 'selected' : '' }}>Inspection</option>
                                <option value="cleaning" {{ $intervention->type === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                <option value="emergency_repair" {{ $intervention->type === 'emergency_repair' ? 'selected' : '' }}>Emergency Repair</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="technician_id" class="form-label">Technician *</label>
                            <select class="form-select @error('technician_id') is-invalid @enderror" id="technician_id" name="technician_id" required>
                                @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}" {{ $intervention->technician_id === $technician->id ? 'selected' : '' }}>
                                        {{ $technician->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('technician_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduled_date" class="form-label">Scheduled Date *</label>
                            <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                   id="scheduled_date" name="scheduled_date" value="{{ $intervention->scheduled_date->format('Y-m-d') }}" required>
                            @error('scheduled_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority *</label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="low" {{ $intervention->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $intervention->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $intervention->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $intervention->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="scheduled" {{ $intervention->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ $intervention->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $intervention->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $intervention->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Cost</label>
                            <input type="number" step="0.01" class="form-control @error('cost') is-invalid @enderror"
                                   id="cost" name="cost" value="{{ $intervention->cost }}">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3" required>{{ $intervention->description }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="parts_replaced" class="form-label">Parts Replaced</label>
                        <textarea class="form-control @error('parts_replaced') is-invalid @enderror"
                                  id="parts_replaced" name="parts_replaced" rows="2">{{ $intervention->parts_replaced }}</textarea>
                        @error('parts_replaced')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ $intervention->notes }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('interventions.show', $intervention) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-solar">
                            <i class="bi bi-check-lg me-2"></i>Update Intervention
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
