@extends('layouts.app')

@section('title', 'Weather Dashboard')

@section('styles')
<style>
    .weather-dashboard {
        position: relative;
        z-index: 1;
    }

    .weather-header {
        margin-bottom: 2rem;
    }

    .weather-header h1 {
        font-weight: 800;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .weather-header .last-update {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    /* Weather Cards */
    .weather-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .weather-card:hover {
        background: var(--surface-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--border-hover);
    }

    /* Main Weather Card */
    .main-weather-card {
        background: linear-gradient(135deg, var(--cyan-accent), var(--cyan-dark));
        border: none;
        color: white;
        text-align: center;
    }
    .main-weather-card:hover { border: none; }

    .main-weather-card .weather-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .main-weather-card .temperature {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .main-weather-card .condition {
        font-size: 1.25rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .main-weather-card .feels-like {
        font-size: 0.9rem;
        opacity: 0.8;
        margin-top: 0.5rem;
    }

    /* Info Cards Grid */
    .info-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .info-card {
        background: var(--midnight);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        text-align: center;
    }

    .info-card:hover { border-color: var(--border-hover); }

    .info-card .icon-wrapper {
        width: 44px; height: 44px;
        background: rgba(6, 182, 212, 0.12);
        color: var(--cyan-accent);
        border: 1px solid rgba(6, 182, 212, 0.2);
        border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.25rem;
    }

    .info-card .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-dim);
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .info-card .value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .info-card .unit {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    /* Solar Production Impact Card */
    .production-impact-card {
        background: var(--surface);
        border: 1px solid var(--border);
    }

    .impact-meter {
        height: 10px;
        background: var(--midnight);
        border-radius: 5px;
        overflow: hidden;
        margin: 1rem 0;
        border: 1px solid var(--border);
    }

    .impact-meter-fill {
        height: 100%;
        border-radius: 5px;
        transition: width 0.5s ease;
    }

    .impact-meter-fill.optimal {
        background: linear-gradient(90deg, #10B981, #059669);
    }

    .impact-meter-fill.moderate {
        background: linear-gradient(90deg, #F59E0B, #D97706);
    }

    .impact-meter-fill.low {
        background: linear-gradient(90deg, #EF4444, #DC2626);
    }

    .production-status {
        display: inline-block;
        padding: 0.35rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .production-status.optimal {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid var(--success-border);
    }

    .production-status.moderate {
        background: var(--warning-bg);
        color: var(--warning);
        border: 1px solid var(--warning-border);
    }

    .production-status.low {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid var(--danger-border);
    }

    /* Sun Schedule Card */
    .sun-schedule {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
    }

    .sun-item {
        text-align: center;
    }

    .sun-item i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--solar-amber);
    }

    .sun-item .time {
        font-size: 1rem;
        font-weight: 700;
    }

    .sun-item .label {
        font-size: 0.7rem;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
    }

    /* Real-time indicator */
    .realtime-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--success-bg);
        border: 1px solid var(--success-border);
        color: var(--success);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .realtime-dot {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    /* UV Index styling */
    .uv-scale {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }

    .uv-bar {
        width: 18px;
        height: 5px;
        border-radius: 3px;
        background: var(--surface-elevated);
    }

    .uv-bar.active { background: var(--solar-amber); }
    .uv-bar.high { background: var(--warning); }
    .uv-bar.very-high { background: var(--danger); }

    /* Back button */
    .back-btn {
        background: var(--surface-hover);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .back-btn:hover {
        background: var(--surface-elevated);
        color: var(--text-primary);
        border-color: var(--border-hover);
    }
</style>
@endsection

@section('content')
<div class="weather-dashboard">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="weather-header">
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route('dashboard') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h1><i class="bi bi-cloud-sun me-2"></i>Weather Dashboard</h1>
            </div>
            <p class="last-update">Last updated: <span id="lastUpdate">{{ $weatherData['last_updated'] }}</span></p>
        </div>
        <div class="realtime-indicator">
            <span class="realtime-dot"></span>
            Live Updates
        </div>
    </div>

    <!-- Main Weather Display -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="weather-card main-weather-card">
                <div class="weather-icon">
                    <i class="bi {{ $weatherData['condition_icon'] }}"></i>
                </div>
                <div class="temperature">{{ $weatherData['temperature'] }}°C</div>
                <div class="condition">{{ $weatherData['condition'] }}</div>
                <div class="feels-like">Feels like {{ $weatherData['feels_like'] }}°C</div>
                
                <!-- Sun Schedule -->
                <div class="sun-schedule">
                    <div class="sun-item">
                        <i class="bi bi-sunrise text-warning"></i>
                        <div class="time">{{ $weatherData['sunrise'] }}</div>
                        <div class="label">Sunrise</div>
                    </div>
                    <div class="sun-item">
                        <i class="bi bi-sunset text-warning"></i>
                        <div class="time">{{ $weatherData['sunset'] }}</div>
                        <div class="label">Sunset</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weather Info Cards -->
        <div class="col-lg-8">
            <div class="info-cards-grid">
                <!-- Humidity -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-droplet"></i>
                    </div>
                    <div class="label">Humidity</div>
                    <div class="value">{{ $weatherData['humidity'] }}<span class="unit">%</span></div>
                </div>

                <!-- Wind Speed -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-wind"></i>
                    </div>
                    <div class="label">Wind Speed</div>
                    <div class="value">{{ $weatherData['wind_speed'] }}<span class="unit"> km/h {{ $weatherData['wind_direction'] }}</span></div>
                </div>

                <!-- Pressure -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-gauge"></i>
                    </div>
                    <div class="label">Pressure</div>
                    <div class="value">{{ $weatherData['pressure'] }}<span class="unit"> hPa</span></div>
                </div>

                <!-- UV Index -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-brightness-high"></i>
                    </div>
                    <div class="label">UV Index</div>
                    <div class="value">{{ $weatherData['uv_index'] }}</div>
                    <div class="uv-scale">
                        @for($i = 1; $i <= 11; $i++)
                            <div class="uv-bar {{ $i <= $weatherData['uv_index'] ? ($weatherData['uv_index'] >= 8 ? 'very-high' : ($weatherData['uv_index'] >= 5 ? 'high' : 'active')) : '' }}"></div>
                        @endfor
                    </div>
                </div>

                <!-- Visibility -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="label">Visibility</div>
                    <div class="value">{{ $weatherData['visibility'] }}</div>
                </div>

                <!-- Cloud Cover -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <div class="label">Cloud Cover</div>
                    <div class="value">{{ $weatherData['cloud_cover'] }}<span class="unit">%</span></div>
                </div>

                <!-- Solar Irradiance -->
                <div class="weather-card info-card">
                    <div class="icon-wrapper">
                        <i class="bi bi-brightness-alt-high"></i>
                    </div>
                    <div class="label">Solar Irradiance</div>
                    <div class="value">{{ $weatherData['solar_irradiance'] }}<span class="unit"> W/m²</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Solar Production Impact -->
    <div class="row g-4">
        <div class="col-12">
            <div class="weather-card production-impact-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="bi bi-solar-panel me-2"></i>Solar Production Impact</h4>
                    <span class="production-status {{ $weatherData['production_impact']['status'] }}">
                        {{ ucfirst($weatherData['production_impact']['status']) }}
                    </span>
                </div>
                
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-2" style="opacity: 0.9;">{{ $weatherData['production_impact']['message'] }}</p>
                        <div class="d-flex gap-4 mt-3">
                            <div>
                                <div class="label" style="font-size: 0.75rem; opacity: 0.7; text-transform: uppercase;">Efficiency</div>
                                <div class="value" style="font-size: 1.5rem;">{{ $weatherData['production_impact']['efficiency'] }}%</div>
                            </div>
                            <div>
                                <div class="label" style="font-size: 0.75rem; opacity: 0.7; text-transform: uppercase;">Expected Output</div>
                                <div class="value" style="font-size: 1.5rem;">{{ $weatherData['production_impact']['expected_kwh'] }} <span style="font-size: 0.9rem;">kWh/hr</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="impact-meter">
                            <div class="impact-meter-fill {{ $weatherData['production_impact']['status'] }}" 
                                 style="width: {{ $weatherData['production_impact']['efficiency'] }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 0.8rem; opacity: 0.7;">
                            <span>0%</span>
                            <span>25%</span>
                            <span>50%</span>
                            <span>75%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh weather data every 30 seconds
    let weatherInterval;
    const REFRESH_INTERVAL = 30000; // 30 seconds

    function fetchWeatherData() {
        fetch('/api/weather', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateWeatherDisplay(data.data);
            }
        })
        .catch(error => console.error('Error fetching weather data:', error));
    }

    function updateWeatherDisplay(data) {
        // Update main weather
        document.querySelector('.main-weather-card .temperature').textContent = data.temperature + '°C';
        document.querySelector('.main-weather-card .condition').textContent = data.condition;
        document.querySelector('.main-weather-card .feels-like').textContent = 'Feels like ' + data.feels_like + '°C';
        document.querySelector('.main-weather-card .weather-icon i').className = 'bi ' + data.condition_icon;

        // Update info cards
        const cards = document.querySelectorAll('.info-card .value');
        if (cards[0]) cards[0].innerHTML = data.humidity + '<span class="unit">%</span>';
        if (cards[1]) cards[1].innerHTML = data.wind_speed + '<span class="unit"> km/h ' + data.wind_direction + '</span>';
        if (cards[2]) cards[2].innerHTML = data.pressure + '<span class="unit"> hPa</span>';
        if (cards[3]) cards[3].textContent = data.uv_index;
        if (cards[4]) cards[4].textContent = data.visibility;
        if (cards[5]) cards[5].innerHTML = data.cloud_cover + '<span class="unit">%</span>';
        if (cards[6]) cards[6].innerHTML = data.solar_irradiance + '<span class="unit"> W/m²</span>';

        // Update production impact
        const impact = data.production_impact;
        const statusElement = document.querySelector('.production-status');
        statusElement.className = 'production-status ' + impact.status;
        statusElement.textContent = impact.status.charAt(0).toUpperCase() + impact.status.slice(1);

        document.querySelector('.production-impact-card p').textContent = impact.message;
        document.querySelector('.impact-meter-fill').className = 'impact-meter-fill ' + impact.status;
        document.querySelector('.impact-meter-fill').style.width = impact.efficiency + '%';

        // Update last update time
        document.getElementById('lastUpdate').textContent = data.last_updated;
    }

    // Start auto-refresh
    document.addEventListener('DOMContentLoaded', function() {
        // Initial fetch
        fetchWeatherData();
        
        // Set up interval
        weatherInterval = setInterval(fetchWeatherData, REFRESH_INTERVAL);
    });

    // Clean up on page leave
    window.addEventListener('beforeunload', function() {
        if (weatherInterval) {
            clearInterval(weatherInterval);
        }
    });
</script>
@endpush
