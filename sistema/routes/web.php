<?php

use App\Livewire\Auth\Login;
use App\Livewire\Abm\Terlec\TerlecIndex;
use App\Livewire\Abm\Niveles\NivelesIndex;
use App\Livewire\Abm\Legajos\LegajosIndex;
use App\Livewire\Abm\Legajos\LegajoForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Logout
Route::post('/logout', function () {
    \App\Support\SchoolContext::clear();
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Authenticated + school context routes
Route::middleware(['auth', 'school.context'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ABM routes
    Route::get('/abm/terlec',   TerlecIndex::class)->name('abm.terlec');
    Route::get('/abm/niveles',  NivelesIndex::class)->name('abm.niveles');
    Route::get('/abm/legajos',  LegajosIndex::class)->name('abm.legajos');
    Route::get('/abm/legajos/nuevo', LegajoForm::class)->name('abm.legajos.create');
    Route::get('/abm/legajos/{id}/editar', LegajoForm::class)->whereNumber('id')->name('abm.legajos.edit');
});
