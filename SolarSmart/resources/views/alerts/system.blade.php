@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Alerts - {{ $solarSystem->name }}</h1>
    </div>

    @if($alerts->isEmpty())
        <div class="alert alert-info">
            No alerts found for this solar system.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Triggered At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr>
                            <td>{{ $alert->title }}</td>
                            <td>{{ $alert->type }}</td>
                            <td>
                                <span class="badge bg-{{ $alert->severity === 'high' ? 'danger' : ($alert->severity === 'medium' ? 'warning' : 'info') }}">
                                    {{ $alert->severity }}
                                </span>
                            </td>
                            <td>{{ $alert->status }}</td>
                            <td>{{ $alert->triggered_at }}</td>
                            <td>
                                <a href="{{ route('alerts.show', $alert) }}" class="btn btn-sm btn-info">View</a>
                                @if($alert->status === 'active')
                                    <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Acknowledge</button>
                                    </form>
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
