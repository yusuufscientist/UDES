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

// Welcome / Home
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Weather Dashboard
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

    // Solar Systems
    Route::resource('solar-systems', SolarSystemController::class);

    // Standalone Panels route - redirects to first solar system's panels
    Route::get('/panels', function () {
        $solarSystem = auth()->user()->solarSystems()->first();
        if ($solarSystem) {
            return redirect()->route('solar-systems.panels.index', $solarSystem);
        }

        return redirect()->route('dashboard')->with('error', 'No solar system found.');
    })->name('panels.index');

    // Standalone Productions route - redirects to first solar system's productions
    Route::get('/productions', function () {
        $solarSystem = auth()->user()->solarSystems()->first();
        if ($solarSystem) {
            return redirect()->route('solar-systems.productions.index', $solarSystem);
        }

        return redirect()->route('dashboard')->with('error', 'No solar system found.');
    })->name('productions.index');

    // Standalone Interventions route - redirects to first solar system's interventions
    Route::get('/interventions/create', function () {
        $solarSystem = auth()->user()->solarSystems()->first();
        if ($solarSystem) {
            return redirect()->route('solar-systems.interventions.create', $solarSystem);
        }

        return redirect()->route('dashboard')->with('error', 'No solar system found to create intervention.');
    })->name('interventions.create');

    // Standalone Production route
    Route::get('/production', function () {
        $solarSystem = auth()->user()->solarSystems()->first();
        if ($solarSystem) {
            return redirect()->route('solar-systems.productions.index', $solarSystem);
        }

        return redirect()->route('dashboard')->with('error', 'No solar system found.');
    })->name('production.index');

    // Panels (nested under solar systems)
    Route::prefix('solar-systems/{solar_system}')->name('solar-systems.')->group(function () {
        Route::resource('panels', PanelController::class);
        Route::post('panels/{panel}/readings', [PanelController::class, 'updateReadings'])->name('panels.readings');

        // Productions (nested under solar systems)
        Route::resource('productions', ProductionController::class);
        Route::get('productions/chart/data', [ProductionController::class, 'chartData'])->name('productions.chart.data');

        // Interventions (nested under solar systems)
        Route::resource('interventions', InterventionController::class);
    });

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::put('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::get('/alerts/active/count', [AlertController::class, 'activeCount'])->name('alerts.active.count');
    Route::get('/alerts/recent/list', [AlertController::class, 'recent'])->name('alerts.recent');

    // Generate Demo Data
    Route::post('/generate-demo-data', [\App\Http\Controllers\Api\RealtimeController::class, 'simulateRealtimeData'])->name('generate-demo-data');

    // Interventions
    Route::get('/interventions', [InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/{intervention}', [InterventionController::class, 'show'])->name('interventions.show');
    Route::get('/interventions/{intervention}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
    Route::put('/interventions/{intervention}', [InterventionController::class, 'update'])->name('interventions.update');
    Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');
    Route::post('/interventions/{intervention}/start', [InterventionController::class, 'start'])->name('interventions.start');
    Route::post('/interventions/{intervention}/complete', [InterventionController::class, 'complete'])->name('interventions.complete');

    // Fault Simulations
    Route::get('/fault-simulations', [FaultSimulationController::class, 'index'])->name('fault-simulations.index');
    Route::get('/fault-simulations/{faultSimulation}', [FaultSimulationController::class, 'show'])->name('fault-simulations.show');
    Route::post('/fault-simulations/quick-trigger', [FaultSimulationController::class, 'quickTrigger'])->name('fault-simulations.quick-trigger');
    Route::post('/fault-simulations/{faultSimulation}/resolve', [FaultSimulationController::class, 'resolve'])->name('fault-simulations.resolve');
    Route::get('/solar-systems/{solar_system}/fault-simulations/create', [FaultSimulationController::class, 'create'])->name('solar-systems.fault-simulations.create');
    Route::post('/solar-systems/{solar_system}/fault-simulations', [FaultSimulationController::class, 'store'])->name('solar-systems.fault-simulations.store');

    // Technician Routes
    Route::prefix('technician')->name('technician.')->middleware('role:technician')->group(function () {
        Route::get('/dashboard', [TechnicianController::class, 'dashboard'])->name('dashboard');
        Route::get('/interventions', [TechnicianController::class, 'interventions'])->name('interventions');
        Route::get('/maintenance', [TechnicianController::class, 'maintenanceNeeded'])->name('maintenance');
        Route::post('/panels/{panel}/status', [TechnicianController::class, 'updatePanelStatus'])->name('panels.status');
        Route::post('/interventions/{intervention}/complete', [TechnicianController::class, 'completeIntervention'])->name('interventions.complete');
    });
});
