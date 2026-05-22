@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Production Record - {{ $production->production_date }}</h1>
        <div>
            <a href="{{ route('solar-systems.productions.edit', [$solarSystem, $production]) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('solar-systems.productions.index', $solarSystem) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Production Details</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $production->production_date }}</td>
                        </tr>
                        @if($production->production_time)
                        <tr>
                            <th>Time:</th>
                            <td>{{ $production->production_time }}</td>
                        </tr>
                        @endif
                        @if($production->panel)
                        <tr>
                            <th>Panel:</th>
                            <td>{{ $production->panel->serial_number }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Energy Produced:</th>
                            <td>{{ number_format($production->energy_produced_kwh, 2) }} kWh</td>
                        </tr>
                        <tr>
                            <th>Energy Consumed:</th>
                            <td>{{ number_format($production->energy_consumed_kwh ?? 0, 2) }} kWh</td>
                        </tr>
                        <tr>
                            <th>Peak Power:</th>
                            <td>{{ number_format($production->peak_power_kw ?? 0, 2) }} kW</td>
                        </tr>
                        <tr>
                            <th>Average Power:</th>
                            <td>{{ number_format($production->average_power_kw ?? 0, 2) }} kW</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Environmental Conditions</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Irradiance:</th>
                            <td>{{ $production->irradiance_wm2 ?? 'N/A' }} W/m²</td>
                        </tr>
                        <tr>
                            <th>Temperature:</th>
                            <td>{{ $production->temperature_celsius ?? 'N/A' }} °C</td>
                        </tr>
                        <tr>
                            <th>Weather:</th>
                            <td>{{ $production->weather_condition ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
