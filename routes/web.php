<?php

use App\Http\Controllers\CircuitController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\GrandPrixController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StandingsController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// ─── Public landing page ──────────────────────────────────────────────────────
Route::get('/', [PageController::class, 'home'])->name('home');

// ─── Public browsing routes (no auth required) ────────────────────────────────
// IMPORTANT: index always before show so {model} wildcard never captures 'create'
Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
Route::get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');

Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');

Route::get('/circuits', [CircuitController::class, 'index'])->name('circuits.index');
Route::get('/circuits/{circuit}', [CircuitController::class, 'show'])->name('circuits.show');

Route::get('/grand-prix', [GrandPrixController::class, 'index'])->name('grand-prix.index');
Route::get('/grand-prix/{grandPrix}', [GrandPrixController::class, 'show'])->name('grand-prix.show');

Route::get('/standings/drivers', [StandingsController::class, 'drivers'])->name('standings.drivers');
Route::get('/standings/teams', [StandingsController::class, 'teams'])->name('standings.teams');

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Admin-only write routes ──────────────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // DRIVERS — create/store BEFORE {driver} wildcard
        Route::get('/drivers/create', [DriverController::class, 'create'])->name('drivers.create');
        Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
        Route::get('/drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
        Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');

        // TEAMS — create/store BEFORE {team} wildcard
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        // CIRCUITS — create/store BEFORE {circuit} wildcard
        Route::get('/circuits/create', [CircuitController::class, 'create'])->name('circuits.create');
        Route::post('/circuits', [CircuitController::class, 'store'])->name('circuits.store');
        Route::get('/circuits/{circuit}/edit', [CircuitController::class, 'edit'])->name('circuits.edit');
        Route::put('/circuits/{circuit}', [CircuitController::class, 'update'])->name('circuits.update');
        Route::delete('/circuits/{circuit}', [CircuitController::class, 'destroy'])->name('circuits.destroy');

        // GRAND PRIX — create/store BEFORE {grandPrix} wildcard
        Route::get('/grand-prix/create', [GrandPrixController::class, 'create'])->name('grand-prix.create');
        Route::post('/grand-prix', [GrandPrixController::class, 'store'])->name('grand-prix.store');
        Route::get('/grand-prix/{grandPrix}/edit', [GrandPrixController::class, 'edit'])->name('grand-prix.edit');
        Route::put('/grand-prix/{grandPrix}', [GrandPrixController::class, 'update'])->name('grand-prix.update');
        Route::delete('/grand-prix/{grandPrix}', [GrandPrixController::class, 'destroy'])->name('grand-prix.destroy');
    });
});

// ─── Breeze auth routes ───────────────────────────────────────────────────────
require __DIR__.'/auth.php';
