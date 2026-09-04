<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Front-end (Vue 3)
|--------------------------------------------------------------------------
| Rutas consumidas por la SPA frontend para control de relojes,
| dependencias, agentes y visualización de fichadas en tiempo real.
*/

Route::get('/live-data', [DashboardController::class, 'liveData'])->name('api.live-data');
Route::get('/departments', [DashboardController::class, 'departments'])->name('api.departments.list');
Route::post('/departments', [DashboardController::class, 'saveDepartment'])->name('api.departments.save');
Route::get('/employees', [DashboardController::class, 'employees'])->name('api.employees.list');
Route::post('/employees', [DashboardController::class, 'saveEmployee'])->name('api.employees.save');
Route::get('/commands', [DashboardController::class, 'commands'])->name('api.commands.list');
Route::post('/commands/queue', [DashboardController::class, 'queueCommand'])->name('api.commands.queue');
Route::post('/devices/{id}/assign', [DashboardController::class, 'assignDevice'])->name('api.devices.assign');
Route::post('/simulate-punch', [DashboardController::class, 'simulatePunch'])->name('api.simulate-punch');
