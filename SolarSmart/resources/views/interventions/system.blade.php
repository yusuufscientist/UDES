@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Interventions - {{ $solarSystem->name }}</h1>
        <a href="{{ route('interventions.create', $solarSystem) }}" class="btn btn-primary">
            Schedule Intervention
        </a>
    </div>

    @if($interventions->isEmpty())
        <div class="alert alert-info">
            No interventions found for this solar system.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Scheduled Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interventions as $intervention)
                        <tr>
                            <td>{{ $intervention->title }}</td>
                            <td>{{ $intervention->type }}</td>
                            <td>
                                <span class="badge bg-{{ $intervention->priority === 'urgent' ? 'danger' : ($intervention->priority === 'high' ? 'warning' : 'info') }}">
                                    {{ $intervention->priority }}
                                </span>
                            </td>
                            <td>{{ $intervention->status }}</td>
                            <td>{{ $intervention->scheduled_date }}</td>
                            <td>
                                <a href="{{ route('interventions.show', $intervention) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('interventions.edit', $intervention) }}" class="btn btn-sm btn-warning">Edit</a>
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
