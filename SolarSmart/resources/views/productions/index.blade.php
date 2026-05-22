@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Production Records - {{ $solarSystem->name }}</h1>
        <a href="{{ route('solar-systems.productions.create', $solarSystem) }}" class="btn btn-primary">
            Add Production Record
        </a>
    </div>

    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Produced</h5>
                    <h3>{{ number_format($stats['total_produced'] ?? 0, 2) }} kWh</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Consumed</h5>
                    <h3>{{ number_format($stats['total_consumed'] ?? 0, 2) }} kWh</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Average Production</h5>
                    <h3>{{ number_format($stats['average_production'] ?? 0, 2) }} kWh</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Peak Production</h5>
                    <h3>{{ number_format($stats['peak_production'] ?? 0, 2) }} kWh</h3>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($productions->isEmpty())
        <div class="alert alert-info">
            No production records found.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Energy Produced (kWh)</th>
                            <th>Energy Consumed (kWh)</th>
                            <th>Peak Power (kW)</th>
                            <th>Weather</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productions as $production)
                        <tr>
                            <td>{{ $production->production_date }}</td>
                            <td>{{ number_format($production->energy_produced_kwh, 2) }}</td>
                            <td>{{ number_format($production->energy_consumed_kwh ?? 0, 2) }}</td>
                            <td>{{ number_format($production->peak_power_kw ?? 0, 2) }}</td>
                            <td>{{ $production->weather_condition ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('solar-systems.productions.show', [$solarSystem, $production]) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('solar-systems.productions.edit', [$solarSystem, $production]) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $productions->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
