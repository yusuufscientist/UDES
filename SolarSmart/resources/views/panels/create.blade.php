@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add Panel to {{ $solarSystem->name }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('solar-systems.panels.store', $solarSystem) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control @error('serial_number') is-invalid @enderror" 
                                   id="serial_number" name="serial_number" value="{{ old('serial_number') }}" required>
                            @error('serial_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                   id="model" name="model" value="{{ old('model') }}" required>
                            @error('model')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" 
                                   id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" required>
                            @error('manufacturer')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="capacity_watts" class="form-label">Capacity (Watts)</label>
                            <input type="number" class="form-control @error('capacity_watts') is-invalid @enderror" 
                                   id="capacity_watts" name="capacity_watts" value="{{ old('capacity_watts') }}" required min="1">
                            @error('capacity_watts')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="efficiency_rating" class="form-label">Efficiency Rating (%)</label>
                            <input type="number" class="form-control @error('efficiency_rating') is-invalid @enderror" 
                                   id="efficiency_rating" name="efficiency_rating" value="{{ old('efficiency_rating') }}" step="0.01" min="0" max="100">
                            @error('efficiency_rating')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="installation_date" class="form-label">Installation Date</label>
                            <input type="date" class="form-control @error('installation_date') is-invalid @enderror" 
                                   id="installation_date" name="installation_date" value="{{ old('installation_date') }}" required>
                            @error('installation_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Panel</button>
                            <a href="{{ route('solar-systems.panels.index', $solarSystem) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
