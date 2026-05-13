<?php

use App\Http\Controllers\Alumnos\CalificacionesController;
use App\Http\Controllers\Alumnos\PushApiController;
use App\Http\Controllers\Alumnos\PushController;
use App\Http\Controllers\AntecedentesDisciplinariosPdfController;
use App\Http\Controllers\CalificacionesSecundario\ConsultaCalificacionesSecundarioPdfController;
use App\Http\Controllers\ListadoCursoPdfController;
use App\Http\Controllers\SancionComunicadoPdfController;
use App\Livewire\Abm\Curplan\CurplanForm;
use App\Livewire\Abm\Curplan\CurplanIndex;
use App\Livewire\Abm\Cursos\CursosIndex;
use App\Livewire\Abm\Legajos\LegajoForm;
use App\Livewire\Abm\Legajos\LegajosIndex;
use App\Livewire\Abm\MateriasAnio\MateriasAnioIndex;
use App\Livewire\Abm\Niveles\NivelesIndex;
use App\Livewire\Abm\Planes\PlanesForm;
use App\Livewire\Abm\Planes\PlanesIndex;
use App\Livewire\Abm\Terlec\TerlecIndex;
use App\Livewire\Administracion\Permisos\PermisosUsuariosIndex;
use App\Livewire\Alumnos\Auth\Login as AlumnosLogin;
use App\Livewire\Alumnos\Comunicaciones\BandejaFamilia;
use App\Livewire\Alumnos\Comunicaciones\HiloShowFamilia;
use App\Livewire\Alumnos\Comunicaciones\NuevoComunicadoFamilia;
use App\Livewire\Alumnos\Comunicaciones\PreferenciasMedios;
use App\Livewire\Auth\Login;
use App\Livewire\CalificacionesSecundario\CargaCalificacionesSecundario;
use App\Livewire\CalificacionesSecundario\ConsultaCalificacionesSecundario;
use App\Livewire\Comunicaciones\BandejaGestion;
use App\Livewire\Comunicaciones\BandejaRevision;
use App\Livewire\Comunicaciones\HiloShow;
use App\Livewire\Comunicaciones\InformeEnvioComunicado;
use App\Livewire\Comunicaciones\NuevoComunicado;
use App\Livewire\Listados\ListadoPorCurso;
use App\Livewire\Parametrizacion\CamposLegajoIndex;
use App\Livewire\Parametrizacion\ComCanalesIndex;
use App\Livewire\Parametrizacion\ParametrosSistemaForm;
use App\Livewire\Parametrizacion\SolapaLegajoIndex;
use App\Livewire\Push\EnviarPush;
use App\Livewire\Seguimiento\Disciplinario\AntecedentesIndex;
use App\Livewire\Seguimiento\Disciplinario\DisciplinarioIndex;
use App\Livewire\Seguimiento\Disciplinario\SancionForm;
use App\Support\SchoolContext;
use App\Support\StudentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/loginUsuario', Login::class)->middleware('no-store')->name('login');
});

// Guest routes (alumnos)
Route::middleware('guest:alumno')->group(function () {
    Route::get('/loginEstudiante', AlumnosLogin::class)->middleware('no-store')->name('alumnos.login');
});

// Logout
Route::post('/logout', function () {
    SchoolContext::clear();
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Logout alumnos
Route::post('/alumnos/logout', function () {
    StudentContext::clear();
    Auth::guard('alumno')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('alumnos.login');
})->middleware('auth:alumno')->name('alumnos.logout');

// Área alumnos (autogestión)
Route::middleware(['auth:alumno', 'student.context'])->prefix('alumnos')->group(function () {
    Route::get('/', function () {
        return redirect()->route('alumnos.comunicaciones.index');
    })->name('alumnos.home');

    Route::get('/calificaciones', CalificacionesController::class)->name('alumnos.calificaciones');

    Route::get('/notificaciones', [PushController::class, 'index'])->name('alumnos.push.index');

    Route::get('/comunicaciones', BandejaFamilia::class)->name('alumnos.comunicaciones.index');
    Route::get('/comunicaciones/nuevo', NuevoComunicadoFamilia::class)->name('alumnos.comunicaciones.nuevo');
    Route::get('/comunicaciones/preferencias', PreferenciasMedios::class)->name('alumnos.comunicaciones.preferencias');
    Route::get('/comunicaciones/{id}', HiloShowFamilia::class)->whereNumber('id')->name('alumnos.comunicaciones.hilo');
});

// API Push (misma sesión del alumno; fuera del prefix /alumnos para que el SW tenga scope simple)
Route::middleware(['auth:alumno'])->prefix('notificaciones-push/api')->group(function () {
    Route::post('/subscribe', [PushApiController::class, 'subscribe'])->name('push.api.subscribe');
    Route::post('/unsubscribe', [PushApiController::class, 'unsubscribe'])->name('push.api.unsubscribe');
    Route::post('/send', [PushApiController::class, 'send'])->name('push.api.send');
});

// Authenticated + school context routes
Route::middleware(['auth', 'school.context'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/notificaciones/push/enviar', EnviarPush::class)
        ->middleware('permiso:2')
        ->name('push.enviar');

    Route::get('/comunicaciones', BandejaGestion::class)->middleware('permiso:51')->name('comunicaciones.index');
    Route::get('/comunicaciones/revision', BandejaRevision::class)->middleware(['permiso:51', 'permiso:56'])->name('comunicaciones.revision');
    Route::get('/comunicaciones/nuevo', NuevoComunicado::class)->middleware('permiso:52')->name('comunicaciones.nuevo');
    Route::get('/comunicaciones/informe-envio/{id}', InformeEnvioComunicado::class)
        ->middleware(['permiso:51', 'permiso:52'])
        ->whereNumber('id')
        ->name('comunicaciones.informe-envio');
    Route::get('/comunicaciones/{id}', HiloShow::class)->middleware('permiso:51')->whereNumber('id')->name('comunicaciones.hilo');

    // Administración: permisos de usuarios (orden 0)
    Route::get('/administracion/permisos', PermisosUsuariosIndex::class)
        ->middleware('permiso:0')
        ->name('admin.permisos');

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
    Route::get('/parametrizacion/parametros-sistema', ParametrosSistemaForm::class)
        ->middleware('permiso:1')
        ->name('param.parametros-sistema');
    Route::get('/parametrizacion/campos-legajo', CamposLegajoIndex::class)
        ->middleware('permiso:1')
        ->name('param.campos-listado-alumnos'); // nombre conservado para no romper enlaces existentes
    Route::get('/parametrizacion/solapas-legajo', SolapaLegajoIndex::class)
        ->middleware('permiso:1')
        ->name('param.solapas-legajo');
    Route::get('/parametrizacion/com-canales', ComCanalesIndex::class)
        ->middleware('permiso:53')
        ->name('param.com-canales');
    Route::get('/abm/legajos', LegajosIndex::class)->middleware('permiso:2')->name('abm.legajos');
    Route::get('/abm/legajos/nuevo', LegajoForm::class)->middleware('permiso:2')->name('abm.legajos.create');
    Route::get('/abm/legajos/{id}/editar', LegajoForm::class)->middleware('permiso:2')->whereNumber('id')->name('abm.legajos.edit');

    Route::get('/listados/por-curso', ListadoPorCurso::class)->middleware('permiso:2')->name('listados.por-curso');
    Route::get('/listados/por-curso/listado', ListadoCursoPdfController::class)->middleware('permiso:2')->name('listados.por-curso.pdf');

    // listadoPorCurso_v1.2: rutas registradas por Se\ModuloListadoPorCursoV12\ListadoPorCursoV12ServiceProvider (paquete opcional)

    // Calificaciones (nivel secundario): carga y consulta institucional (mismo PDF que autogestión)
    Route::get('/calificaciones-secundario/carga', CargaCalificacionesSecundario::class)
        ->middleware('permiso:2')
        ->name('calificacionesSecundario.carga');
    Route::get('/calificaciones-secundario/consulta', ConsultaCalificacionesSecundario::class)
        ->middleware('permiso:2')
        ->name('calificacionesSecundario.consulta');
    Route::get('/calificaciones-secundario/consulta/pdf', ConsultaCalificacionesSecundarioPdfController::class)
        ->middleware('permiso:2')
        ->name('calificacionesSecundario.consulta.pdf');

    // Seguimiento disciplinario
    Route::get('/seguimiento/disciplinario', DisciplinarioIndex::class)
        ->middleware('permiso:2')
        ->name('seguimiento.disciplinario');
    Route::get('/seguimiento/disciplinario/nuevo', SancionForm::class)
        ->middleware('permiso:2')
        ->name('seguimiento.disciplinario.create');
    Route::get('/seguimiento/disciplinario/{id}/editar', SancionForm::class)
        ->middleware('permiso:2')
        ->whereNumber('id')
        ->name('seguimiento.disciplinario.edit');

    Route::get('/seguimiento/disciplinario/{id}/imprimir', SancionComunicadoPdfController::class)
        ->middleware('permiso:2')
        ->whereNumber('id')
        ->name('seguimiento.disciplinario.print');

    Route::get('/seguimiento/disciplinario/{idMatricula}/antecedentes', AntecedentesIndex::class)
        ->middleware('permiso:2')
        ->whereNumber('idMatricula')
        ->name('seguimiento.disciplinario.antecedentes');

    Route::get('/seguimiento/disciplinario/{idMatricula}/antecedentes/pdf', AntecedentesDisciplinariosPdfController::class)
        ->middleware('permiso:2')
        ->whereNumber('idMatricula')
        ->name('seguimiento.disciplinario.antecedentes.pdf');
});
