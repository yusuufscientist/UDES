@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">Solar Energy Dashboard</h1>
            <p class="dashboard-subtitle">Monitor your solar power production in real-time</p>
        </div>
        <div class="header-actions">
            <button id="toggleRealtime" class="btn btn-success me-2">
                <i class="bi bi-play-circle"></i> Start Real-time
            </button>
            <span class="last-update">Last updated: <span id="lastUpdateTime">{{ now()->format('H:i:s') }}</span></span>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-sun-fill"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Today's Production</span>
                <span class="stat-value"><span id="statToday">{{ number_format($todayProduction, 2) }}</span> <small>kWh</small></span>
                <span class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> +12% vs yesterday
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">This Month</span>
                <span class="stat-value">{{ number_format($monthProduction, 2) }} <small>kWh</small></span>
                <span class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> +8% vs last month
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Estimated Savings</span>
                <span class="stat-value">${{ number_format($todayProduction * 0.15, 2) }}</span>
                <span class="stat-change">
                    @ $0.15/kWh
                </span>
            </div>
        </div>

        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active Alerts</span>
                <span class="stat-value">{{ $activeAlerts->count() }}</span>
                <span class="stat-change {{ $activeAlerts->count() > 0 ? 'negative' : '' }}">
                    {{ $activeAlerts->count() > 0 ? 'Needs attention' : 'All systems normal' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="bi bi-graph-up"></i> Energy Production - Last 7 Days</h3>
            </div>
            <div class="chart-body">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="bi bi-calendar-month"></i> Monthly Production - {{ now()->year }}</h3>
            </div>
            <div class="chart-body">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="bottom-grid">
        <!-- Weather Widget -->
        <div class="weather-card">
            <div class="weather-header">
                <h3><i class="bi bi-cloud-sun-fill"></i> Weather Conditions</h3>
            </div>
            <div class="weather-content">
                <div class="weather-main">
                    <div class="weather-icon-large">
                        <i class="bi {{ $weatherData['condition_icon'] ?? 'bi-sun-fill' }}"></i>
                    </div>
                    <div class="weather-temp">{{ $weatherData['temperature'] ?? 25 }}°C</div>
                    <div class="weather-condition">{{ $weatherData['condition'] ?? 'Clear' }}</div>
                </div>
                <div class="weather-details">
                    <div class="weather-detail">
                        <i class="bi bi-droplet"></i>
                        <span>Humidity</span>
                        <strong>{{ $weatherData['humidity'] ?? 45 }}%</strong>
                    </div>
                    <div class="weather-detail">
                        <i class="bi bi-wind"></i>
                        <span>Wind</span>
                        <strong>{{ $weatherData['wind_speed'] ?? 12 }} km/h</strong>
                    </div>
                    <div class="weather-detail">
                        <i class="bi bi-sunrise"></i>
                        <span>UV Index</span>
                        <strong>{{ $weatherData['uv_index'] ?? 5 }}</strong>
                    </div>
                    <div class="weather-detail">
                        <i class="bi bi-clouds"></i>
                        <span>Cloud Cover</span>
                        <strong>{{ $weatherData['cloud_cover'] ?? 15 }}%</strong>
                    </div>
                </div>
                <div class="production-impact">
                    <span>Production Efficiency: <strong>{{ $weatherData['production_impact']['efficiency'] ?? 85 }}%</strong></span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $weatherData['production_impact']['efficiency'] ?? 85 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solar Systems Overview -->
        <div class="systems-card">
            <div class="systems-header">
                <h3><i class="bi bi-grid-3x3-gap-fill"></i> Your Solar Systems</h3>
                <a href="{{ route('solar-systems.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Add
                </a>
            </div>
            <div class="systems-list">
                @forelse($solarSystems as $system)
                <div class="system-item">
                    <div class="system-info">
                        <div class="system-name">{{ $system->name }}</div>
                        <div class="system-location">{{ $system->location }}</div>
                    </div>
                    <div class="system-stats">
                        <div class="system-capacity">{{ $system->total_capacity_kw }} kW</div>
                        <div class="system-production">{{ number_format($system->todayProduction(), 2) }} kWh today</div>
                    </div>
                    <div class="system-status">
                        <span class="badge bg-{{ $system->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($system->status) }}
                        </span>
                    </div>
                    <a href="{{ route('solar-systems.show', $system) }}" class="btn btn-sm btn-outline">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @empty
                <div class="empty-state">
                    <i class="bi bi-sun"></i>
                    <p>No solar systems yet</p>
                    <a href="{{ route('solar-systems.create') }}" class="btn btn-primary">Add Your First System</a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Alerts -->
        <div class="alerts-card">
            <div class="alerts-header">
                <h3><i class="bi bi-bell-fill"></i> Recent Alerts</h3>
                <span class="badge bg-danger">{{ $activeAlerts->count() }}</span>
            </div>
            <div class="alerts-list">
                @forelse($activeAlerts as $alert)
                <div class="alert-item alert-{{ $alert->severity }}">
                    <div class="alert-icon">
                        <i class="bi bi-{{ $alert->severity === 'critical' ? 'exclamation-circle-fill' : 'exclamation-triangle-fill' }}"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-type">{{ $alert->type }}</div>
                        <div class="alert-message">{{ $alert->message }}</div>
                        <div class="alert-time">{{ $alert->triggered_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="no-alerts">
                    <i class="bi bi-check-circle-fill"></i>
                    <p>No active alerts</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    console.log('Dashboard script loading...');
    
    var weeklyChart = null;
    var monthlyChart = null;
    var realtimeInterval = null;
    
    var chartTextColor = '#94A3B8';
    var chartGridColor = 'rgba(148, 163, 184, 0.15)';
    
    // Initialize charts immediately
    console.log('Initializing charts...');
    var weeklyCtx = document.getElementById('weeklyChart');
    var monthlyCtx = document.getElementById('monthlyChart');
    console.log('weeklyCtx:', weeklyCtx);
    console.log('monthlyCtx:', monthlyCtx);
    
    if (weeklyCtx) {
        weeklyChart = new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($productionChartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Energy Production (kWh)',
                    data: {!! json_encode($productionChartData['data'] ?? []) !!},
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FBBF24',
                    pointBorderColor: '#1E293B',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: chartGridColor }, ticks: { color: chartTextColor } },
                    x: { grid: { color: chartGridColor }, ticks: { color: chartTextColor } }
                }
            }
        });
        console.log('Weekly chart created');
    }
    
    if (monthlyCtx) {
        monthlyChart = new Chart(monthlyCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyChartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Monthly Production (kWh)',
                    data: {!! json_encode($monthlyChartData['data'] ?? []) !!},
                    backgroundColor: 'rgba(6, 182, 212, 0.8)',
                    borderColor: '#22D3EE',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: chartGridColor }, ticks: { color: chartTextColor } },
                    x: { grid: { display: false }, ticks: { color: chartTextColor } }
                }
            }
        });
        console.log('Monthly chart created');
    }
    
    // Attach click handler
    var toggleBtn = document.getElementById('toggleRealtime');
    console.log('toggleBtn:', toggleBtn);
    
    if (toggleBtn) {
        toggleBtn.onclick = function() {
            console.log('Button clicked!');
            var btn = this;
            
            if (realtimeInterval) {
                clearInterval(realtimeInterval);
                realtimeInterval = null;
                btn.innerHTML = '<i class="bi bi-play-circle"></i> Start Real-time';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-success');
            } else {
                btn.innerHTML = '<i class="bi bi-stop-circle"></i> Stop Real-time';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-danger');
                
                fetchRealtimeData();
                realtimeInterval = setInterval(fetchRealtimeData, 1000);
            }
        };
        console.log('Click handler attached');
    }
    
    function fetchRealtimeData() {
        console.log('Fetching realtime data...');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF Token element:', csrfToken);
        
        if (!csrfToken) {
            console.error('CSRF token not found!');
            alert('CSRF token not found. Please refresh the page.');
            return;
        }
        
        console.log('CSRF Token:', csrfToken.content);
        
        fetch('/api/realtime/generate', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            credentials: 'include'
        })
        .then(function(response) {
            console.log('Response status:', response.status);
            if (!response.ok) {
                console.error('Response not OK:', response.statusText);
                return response.text().then(function(text) {
                    console.error('Error response body:', text);
                    throw new Error('HTTP error: ' + response.status);
                });
            }
            return response.json();
        })
        .then(function(data) {
            console.log('Data received:', JSON.stringify(data, null, 2));
            
            document.getElementById('lastUpdateTime').textContent = new Date().toLocaleTimeString();
            
            if (data.data) {
                console.log('Updating charts with data.data.production:', data.data.production);
                console.log('Updating charts with data.data.monthly_production:', data.data.monthly_production);
                
                if (weeklyChart) {
                    weeklyChart.data.labels = data.data.labels;
                    weeklyChart.data.datasets[0].data = data.data.production;
                    weeklyChart.update('none');
                    console.log('Weekly chart updated');
                } else {
                    console.log('ERROR: weeklyChart is null');
                }
                
                if (monthlyChart) {
                    monthlyChart.data.labels = data.data.monthly_labels;
                    monthlyChart.data.datasets[0].data = data.data.monthly_production;
                    monthlyChart.update('none');
                    console.log('Monthly chart updated');
                } else {
                    console.log('ERROR: monthlyChart is null');
                }
                
                var todayEl = document.getElementById('statToday');
                if (todayEl && data.data.total_today) {
                    todayEl.textContent = parseFloat(data.data.total_today).toFixed(2);
                }
            } else {
                console.log('ERROR: No data.data in response');
            }
        })
        .catch(function(error) {
            console.error('Fetch error:', error);
            alert('Error fetching data: ' + error.message);
        });
    }
    
    console.log('Script initialization complete');
</script>
@endpush

<style>
.dashboard-container {
    padding: 24px;
    position: relative;
    z-index: 1;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.dashboard-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    letter-spacing: -0.02em;
}

.dashboard-subtitle {
    color: var(--text-secondary);
    margin: 4px 0 0;
    font-size: 0.95rem;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.last-update {
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-hover);
}

.stat-card-primary { position: relative; overflow: hidden; }
.stat-card-primary::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--blue-accent);
}

.stat-card-success { position: relative; overflow: hidden; }
.stat-card-success::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--success);
}

.stat-card-warning { position: relative; overflow: hidden; }
.stat-card-warning::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--warning);
}

.stat-card-info { position: relative; overflow: hidden; }
.stat-card-info::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--danger);
}

.stat-card-primary .stat-icon { background: var(--info-bg); color: var(--blue-accent); border: 1px solid var(--info-border); }
.stat-card-success .stat-icon { background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border); }
.stat-card-warning .stat-icon { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning-border); }
.stat-card-info .stat-icon { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); }

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stat-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.stat-value small {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-muted);
}

.stat-change {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
}

.stat-change.positive { color: var(--success); }
.stat-change.negative { color: var(--danger); }

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.chart-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    background: var(--info-bg);
}

.chart-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-body {
    padding: 1.25rem;
    height: 280px;
}

/* Bottom Grid */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
}

/* Weather Card */
.weather-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.weather-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    background: var(--info-bg);
}

.weather-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.weather-content {
    padding: 1.25rem;
}

.weather-main {
    text-align: center;
    margin-bottom: 20px;
}

.weather-icon-large {
    font-size: 3rem;
    color: var(--blue-accent);
    margin-bottom: 8px;
}

.weather-temp {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.03em;
}

.weather-condition {
    font-size: 1rem;
    color: var(--text-secondary);
}

.weather-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}

.weather-detail {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    background: var(--bg);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
}

.weather-detail i {
    font-size: 1.25rem;
    color: var(--blue-accent);
    margin-bottom: 4px;
}

.weather-detail span {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.weather-detail strong {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.production-impact {
    text-align: center;
}

.production-impact span {
    font-size: 0.85rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.production-impact strong {
    color: var(--success);
    font-weight: 700;
}

.progress-bar {
    height: 6px;
    background: var(--bg);
    border-radius: 3px;
    margin-top: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10B981, #059669);
    border-radius: 3px;
    transition: width 0.3s;
}

/* Systems Card */
.systems-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.systems-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--info-bg);
}

.systems-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.systems-list {
    padding: 12px;
    max-height: 350px;
    overflow-y: auto;
}

.system-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: var(--radius-md);
    gap: 12px;
    transition: background 0.15s;
}

.system-item:hover {
    background: var(--surface-hover);
}

.system-info {
    flex: 1;
    min-width: 0;
}

.system-name {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.system-location {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.system-stats {
    text-align: right;
    flex-shrink: 0;
}

.system-capacity {
    font-weight: 700;
    color: var(--success);
    font-size: 0.95rem;
}

.system-production {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.system-status .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

.btn-outline {
    padding: 6px 10px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    background: transparent;
    transition: all 0.2s;
    font-size: 0.8rem;
}

.btn-outline:hover {
    background: var(--surface-hover);
    color: var(--text-primary);
    border-color: var(--border-hover);
}

/* Alerts Card */
.alerts-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.alerts-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--danger-bg);
}

.alerts-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alerts-list {
    padding: 12px;
    max-height: 350px;
    overflow-y: auto;
}

.alert-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: var(--radius-md);
    margin-bottom: 8px;
    border: 1px solid transparent;
}

.alert-critical {
    background: var(--danger-bg);
    border-color: var(--danger-border);
}

.alert-warning {
    background: var(--warning-bg);
    border-color: var(--warning-border);
}

.alert-info {
    background: var(--info-bg);
    border-color: var(--info-border);
}

.alert-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
    padding-top: 2px;
}

.alert-critical .alert-icon { color: var(--danger); }
.alert-warning .alert-icon { color: var(--warning); }
.alert-info .alert-icon { color: var(--info); }

.alert-content {
    flex: 1;
    min-width: 0;
}

.alert-type {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.85rem;
}

.alert-message {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin: 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.alert-time {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.no-alerts {
    text-align: center;
    padding: 40px 20px;
    color: var(--success);
}

.no-alerts i {
    font-size: 3rem;
    margin-bottom: 12px;
}

.no-alerts p {
    margin: 0;
    color: var(--text-muted);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .bottom-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .charts-grid { grid-template-columns: 1fr; }
    .dashboard-header { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>
@endsection
