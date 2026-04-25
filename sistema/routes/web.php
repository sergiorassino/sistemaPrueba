<?php

use App\Http\Controllers\ListadoCursoPdfController;
use App\Livewire\Abm\Cursos\CursosIndex;
use App\Livewire\Abm\Curplan\CurplanForm;
use App\Livewire\Abm\Legajos\LegajoForm;
use App\Livewire\Abm\Legajos\LegajosIndex;
use App\Livewire\Abm\MateriasAnio\MateriasAnioIndex;
use App\Livewire\Abm\Curplan\CurplanIndex;
use App\Livewire\Abm\Niveles\NivelesIndex;
use App\Livewire\Abm\Planes\PlanesForm;
use App\Livewire\Abm\Planes\PlanesIndex;
use App\Livewire\Abm\Terlec\TerlecIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Calificaciones\CargaCalificaciones;
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
    Route::get('/abm/cursos', CursosIndex::class)->middleware('permiso:1')->name('abm.cursos');
    Route::get('/abm/planes', PlanesIndex::class)->middleware('permiso:1')->name('abm.planes');
    Route::get('/abm/planes/nuevo', PlanesForm::class)->middleware('permiso:1')->name('abm.planes.create');
    Route::get('/abm/planes/{id}/editar', PlanesForm::class)->middleware('permiso:1')->whereNumber('id')->name('abm.planes.edit');
    Route::get('/abm/curplan', CurplanIndex::class)->middleware('permiso:1')->name('abm.curplan');
    Route::get('/abm/curplan/nuevo', CurplanForm::class)->middleware('permiso:1')->name('abm.curplan.create');
    Route::get('/abm/curplan/{id}/editar', CurplanForm::class)->middleware('permiso:1')->whereNumber('id')->name('abm.curplan.edit');
    Route::get('/abm/materias-anio', MateriasAnioIndex::class)->middleware('permiso:1')->name('abm.materias-anio');
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

    // Carga de calificaciones
    Route::get('/calificaciones/carga', CargaCalificaciones::class)
        ->middleware('permiso:2')
        ->name('calificaciones.carga');
});
