<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaultSimulationController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\SolarSystemController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/solar-systems', [SolarSystemController::class, 'index'])->name('solar-systems.index');
Route::get('/solar-systems/create', [SolarSystemController::class, 'create'])->name('solar-systems.create');
Route::post('/solar-systems', [SolarSystemController::class, 'store'])->name('solar-systems.store');
Route::get('/solar-systems/{solar_system}', [SolarSystemController::class, 'show'])->name('solar-systems.show');
Route::get('/solar-systems/{solar_system}/edit', [SolarSystemController::class, 'edit'])->name('solar-systems.edit');
Route::put('/solar-systems/{solar_system}', [SolarSystemController::class, 'update'])->name('solar-systems.update');
Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

Route::get('/panels', function () {
    $system = App\Models\SolarSystem::first();
    return $system
        ? redirect()->route('solar-systems.panels.index', $system)
        : redirect()->route('solar-systems.index');
})->name('panels.index');

Route::get('/productions', function () {
    $system = App\Models\SolarSystem::first();
    return $system
        ? redirect()->route('solar-systems.productions.index', $system)
        : redirect()->route('solar-systems.index');
})->name('productions.index');

Route::get('/production', function () {
    $system = App\Models\SolarSystem::first();
    return $system
        ? redirect()->route('solar-systems.productions.index', $system)
        : redirect()->route('solar-systems.index');
})->name('production.index');

Route::get('/interventions/create', function () {
    $system = App\Models\SolarSystem::first();
    return $system
        ? redirect()->route('solar-systems.interventions.create', $system)
        : 'No solar system available';
})->name('interventions.create');

Route::post('/interventions', function (Illuminate\Http\Request $request) {
    $solarSystem = App\Models\SolarSystem::first();
    if (!$solarSystem) {
        return redirect()->route('solar-systems.index');
    }

    $validated = $request->validate([
        'panel_id' => ['nullable', 'exists:panels,id'],
        'technician_id' => ['required', 'exists:users,id'],
        'alert_id' => ['nullable', 'exists:alerts,id'],
        'type' => ['required', 'in:routine_maintenance,repair,inspection,cleaning,emergency_repair'],
        'description' => ['required', 'string', 'max:1000'],
        'scheduled_date' => ['required', 'date'],
        'priority' => ['required', 'in:low,medium,high,urgent'],
    ]);

    $validated['solar_system_id'] = $solarSystem->id;
    $validated['status'] = 'scheduled';

    App\Models\Intervention::create($validated);

    return redirect()->route('interventions.index')->with('success', 'Intervention created successfully.');
})->name('interventions.store');

Route::prefix('solar-systems/{solar_system}')->name('solar-systems.')->group(function () {
    Route::resource('panels', PanelController::class);
    Route::post('panels/{panel}/readings', [PanelController::class, 'updateReadings'])->name('panels.readings');
    Route::resource('productions', ProductionController::class);
    Route::get('productions/chart/data', [ProductionController::class, 'chartData'])->name('productions.chart.data');
    Route::resource('interventions', InterventionController::class);
});

Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
Route::put('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
Route::get('/alerts/active/count', [AlertController::class, 'activeCount'])->name('alerts.active.count');
Route::get('/alerts/recent/list', [AlertController::class, 'recent'])->name('alerts.recent');

Route::post('/generate-demo-data', [App\Http\Controllers\Api\RealtimeController::class, 'simulateRealtimeData'])->name('generate-demo-data');

Route::get('/api/realtime/generate', [App\Http\Controllers\Api\RealtimeController::class, 'generateAndGetRealtimeData'])->name('realtime.generate');
Route::get('/api/realtime/production', [App\Http\Controllers\Api\RealtimeController::class, 'realtimeProduction'])->name('realtime.production');

Route::get('/interventions', [InterventionController::class, 'index'])->name('interventions.index');
Route::get('/interventions/{intervention}', [InterventionController::class, 'show'])->name('interventions.show');
Route::get('/interventions/{intervention}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
Route::put('/interventions/{intervention}', [InterventionController::class, 'update'])->name('interventions.update');
Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');
Route::post('/interventions/{intervention}/start', [InterventionController::class, 'start'])->name('interventions.start');
Route::post('/interventions/{intervention}/complete', [InterventionController::class, 'complete'])->name('interventions.complete');

Route::get('/fault-simulations', [FaultSimulationController::class, 'index'])->name('fault-simulations.index');
Route::get('/fault-simulations/{faultSimulation}', [FaultSimulationController::class, 'show'])->name('fault-simulations.show');
Route::get('/fault-simulations/create', [FaultSimulationController::class, 'createAll'])->name('fault-simulations.createAll');
Route::post('/fault-simulations/quick-trigger', [FaultSimulationController::class, 'quickTrigger'])->name('fault-simulations.quick-trigger');
Route::post('/fault-simulations/{faultSimulation}/resolve', [FaultSimulationController::class, 'resolve'])->name('fault-simulations.resolve');
Route::get('/solar-systems/{solar_system}/fault-simulations/create', [FaultSimulationController::class, 'create'])->name('solar-systems.fault-simulations.create');
Route::post('/solar-systems/{solar_system}/fault-simulations', [FaultSimulationController::class, 'store'])->name('solar-systems.fault-simulations.store');

Route::prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [TechnicianController::class, 'dashboard'])->name('dashboard');
    Route::get('/interventions', [TechnicianController::class, 'interventions'])->name('interventions');
    Route::get('/maintenance', [TechnicianController::class, 'maintenanceNeeded'])->name('maintenance');
    Route::post('/panels/{panel}/status', [TechnicianController::class, 'updatePanelStatus'])->name('panels.status');
    Route::post('/interventions/{intervention}/complete', [TechnicianController::class, 'completeIntervention'])->name('interventions.complete');
});