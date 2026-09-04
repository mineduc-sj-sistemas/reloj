<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Rutas de Interfaz Web / Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/live-data', [DashboardController::class, 'liveData'])->name('api.live-data');
Route::post('/departments', [DashboardController::class, 'saveDepartment'])->name('departments.save');
Route::post('/devices/{id}/assign', [DashboardController::class, 'assignDevice'])->name('devices.assign');
Route::post('/commands/queue', [DashboardController::class, 'queueCommand'])->name('commands.queue');
Route::post('/employees', [DashboardController::class, 'saveEmployee'])->name('employees.save');

// Protocolo ADMS / PUSH de ZKTeco (MB20-VL y compatibles)
Route::prefix('iclock')->middleware('throttle:adms')->group(function () {
    Route::get('/cdata', [AdmsController::class, 'handshake']);
    Route::post('/cdata', [AdmsController::class, 'receiveData']);
    Route::get('/getrequest', [AdmsController::class, 'getCommands']);
    Route::post('/devicecmd', [AdmsController::class, 'deviceCmdResponse']);
    Route::any('/fdata', [AdmsController::class, 'fallback']);
    Route::any('/{any?}', [AdmsController::class, 'fallback'])->where('any', '.*');
});
