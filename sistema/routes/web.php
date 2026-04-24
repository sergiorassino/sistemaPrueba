<?php

use App\Http\Controllers\ListadoCursoPdfController;
use App\Livewire\Abm\Legajos\LegajoForm;
use App\Livewire\Abm\Legajos\LegajosIndex;
use App\Livewire\Abm\Niveles\NivelesIndex;
use App\Livewire\Abm\Terlec\TerlecIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Listados\ListadoPorCurso;
use App\Livewire\Parametrizacion\CamposListadoAlumnosIndex;
use App\Support\SchoolContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Logout
Route::post('/logout', function () {
    SchoolContext::clear();
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
    Route::get('/abm/terlec', TerlecIndex::class)->middleware('permiso:1')->name('abm.terlec');
    Route::get('/abm/niveles', NivelesIndex::class)->middleware('permiso:1')->name('abm.niveles');
    Route::get('/parametrizacion/campos-listado-alumnos', CamposListadoAlumnosIndex::class)
        ->middleware('permiso:1')
        ->name('param.campos-listado-alumnos');
    Route::get('/abm/legajos', LegajosIndex::class)->middleware('permiso:2')->name('abm.legajos');
    Route::get('/abm/legajos/nuevo', LegajoForm::class)->middleware('permiso:2')->name('abm.legajos.create');
    Route::get('/abm/legajos/{id}/editar', LegajoForm::class)->middleware('permiso:2')->whereNumber('id')->name('abm.legajos.edit');

    Route::get('/listados/por-curso', ListadoPorCurso::class)->middleware('permiso:2')->name('listados.por-curso');
    // Query `?curso=` (no id en el path): Chrome usa el último segmento de la URL como título de pestaña.
    Route::get('/listados/por-curso/listado', ListadoCursoPdfController::class)
        ->middleware('permiso:2')
        ->name('listados.por-curso.pdf');
});
