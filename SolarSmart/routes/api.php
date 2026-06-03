<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\FaultSimulationController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\SolarSystemController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// جلب بيانات المستخدم الحالي (إذا كنت تستعمل التوكنز مستقبلاً)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); 


/*
|--------------------------------------------------------------------------
| SolarSmart API Endpoints (تم حذف الـ web middleware لمنع خطأ 419)
|--------------------------------------------------------------------------
*/

// Solar Systems API
Route::get('/solar-systems', [SolarSystemController::class, 'index']);
Route::post('/solar-systems', [SolarSystemController::class, 'store']);
Route::get('/solar-systems/{solar_system}', [SolarSystemController::class, 'show']);
Route::put('/solar-systems/{solar_system}', [SolarSystemController::class, 'update']);
Route::delete('/solar-systems/{solar_system}', [SolarSystemController::class, 'destroy']);
Route::get('/solar-systems/{solar_system}/production-summary', [SolarSystemController::class, 'productionSummary']);
Route::get('/solar-systems/{solar_system}/production-trend', [SolarSystemController::class, 'productionTrend']);

// Productions API
Route::get('/solar-systems/{solar_system}/productions', [ProductionController::class, 'index']);
Route::post('/solar-systems/{solar_system}/productions', [ProductionController::class, 'store']);
Route::get('/solar-systems/{solar_system}/productions/{production}', [ProductionController::class, 'show']);
Route::put('/solar-systems/{solar_system}/productions/{production}', [ProductionController::class, 'update']);
Route::delete('/solar-systems/{solar_system}/productions/{production}', [ProductionController::class, 'destroy']);
Route::get('/solar-systems/{solar_system}/productions/statistics/summary', [ProductionController::class, 'statistics']);
Route::get('/solar-systems/{solar_system}/productions/chart/data', [ProductionController::class, 'chartData']);

// Alerts API
Route::get('/alerts', [AlertController::class, 'index']);
Route::post('/solar-systems/{solar_system}/alerts', [AlertController::class, 'store']);
Route::get('/alerts/{alert}', [AlertController::class, 'show']);
Route::put('/alerts/{alert}', [AlertController::class, 'update']);
Route::delete('/alerts/{alert}', [AlertController::class, 'destroy']);
Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);
Route::get('/alerts/active/count', [AlertController::class, 'activeCount']);
Route::get('/alerts/summary', [AlertController::class, 'summary']);

// Real-time API Endpoints
Route::get('/realtime/production', [\App\Http\Controllers\Api\RealtimeController::class, 'realtimeProduction']);
Route::get('/realtime/generate', [\App\Http\Controllers\Api\RealtimeController::class, 'generateAndGetRealtimeData']);
Route::get('/realtime/panels', [\App\Http\Controllers\Api\RealtimeController::class, 'livePanelReadings']);
Route::get('/realtime/status', [\App\Http\Controllers\Api\RealtimeController::class, 'systemStatus']);
Route::post('/realtime/simulate', [\App\Http\Controllers\Api\RealtimeController::class, 'simulateRealtimeData']);

// Weather API
Route::get('/weather', [WeatherController::class, 'getWeather']);

// Fault Simulation API
Route::get('/fault-simulations/fault-types', [FaultSimulationController::class, 'faultTypes']);
Route::get('/solar-systems/{solar_system}/fault-simulations/statistics', [FaultSimulationController::class, 'statistics']);
Route::get('/fault-simulations', [FaultSimulationController::class, 'index']);
Route::post('/fault-simulations', [FaultSimulationController::class, 'store']);
Route::get('/fault-simulations/{fault_simulation}', [FaultSimulationController::class, 'show']);
Route::post('/fault-simulations/{fault_simulation}/resolve', [FaultSimulationController::class, 'resolve']);