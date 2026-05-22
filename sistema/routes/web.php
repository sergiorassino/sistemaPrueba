<?php

use App\Http\Controllers\Alumnos\CalificacionesController;
use App\Http\Controllers\Alumnos\InformeInasistenciasController;
use App\Http\Controllers\Alumnos\PushApiController;
use App\Http\Controllers\Alumnos\PushController;
use App\Http\Controllers\AntecedentesDisciplinariosPdfController;
use App\Http\Controllers\BoletinesSecundario\BoletinSecundarioLotePdfController;
use App\Http\Controllers\BoletinesSecundario\BoletinSecundarioPdfController;
use App\Http\Controllers\CalificacionesSecundario\ConsultaCalificacionesSecundarioPdfController;
use App\Http\Controllers\CalificacionesSecundario\PlanillaCalificacionesPdfController;
use App\Http\Controllers\CalificacionesSecundario\ActaVolanteColoquiosPdfController;
use App\Http\Controllers\CalificacionesSecundario\PlanillaResumenCalificacionesPdfController;
use App\Http\Controllers\EstudiantesExcelController;
use App\Http\Controllers\InformeInasistenciasPdfController;
use App\Http\Controllers\ParteDiarioPreceptorPdfController;
use App\Http\Controllers\LibroMatriculaPdfController;
use App\Http\Controllers\ListadoCursoPdfController;
use App\Http\Controllers\Push\SuscribirController;
use App\Http\Controllers\Horarios\HorarioCursoPdfController;
use App\Http\Controllers\Horarios\HorarioProfesorPdfController;
use App\Http\Controllers\Examenes\ActaVolantePreviosPdfController;
use App\Http\Controllers\Examenes\MateriasAdeudadasEntradaController;
use App\Http\Controllers\Examenes\MateriasAdeudadasPdfController;
use App\Http\Controllers\Examenes\PermisoExamenPdfController;
use App\Livewire\Examenes\ActaVolantePreviosIndex;
use App\Livewire\Examenes\PermisoExamenIndex;
use App\Livewire\Examenes\BorrarInscripcionesExamenIndex;
use App\Livewire\Examenes\MateriasAdeudadasCargaManualIndex;
use App\Livewire\Examenes\MateriasAdeudadasInscripcionIndex;
use App\Livewire\Examenes\MateriasAdeudadasNotasIndex;
use App\Livewire\Examenes\HistorialExamenesIndex;
use App\Livewire\Examenes\MateriasAdeudadasGestionIndex;
use App\Livewire\Examenes\MateriasAdeudadasListadoIndex;
use App\Http\Controllers\SancionComunicadoPdfController;
use App\Livewire\Abm\Curplan\CurplanForm;
use App\Livewire\Abm\Curplan\CurplanIndex;
use App\Livewire\Abm\Cursos\CursosIndex;
use App\Livewire\Abm\Legajos\LegajoForm;
use App\Livewire\Abm\Legajos\LegajosIndex;
use App\Livewire\Abm\LegajosProfesor\LegajoProfesorForm;
use App\Livewire\Abm\LegajosProfesor\LegajosProfesorIndex;
use App\Http\Controllers\Docentes\InformeInasistenciasDocentePdfController;
use App\Livewire\Docentes\Inasistencias\CargosDocenteIndex;
use App\Livewire\Docentes\Inasistencias\InformeBimestreShow;
use App\Livewire\Docentes\Inasistencias\InasistenciaDocenteForm;
use App\Livewire\Docentes\Inasistencias\InasistenciasDocenteShow;
use App\Livewire\Docentes\Inasistencias\EnvioMasivoInasistenciasDocentes;
use App\Livewire\Docentes\Inasistencias\InasistenciasDocentesIndex;
use App\Livewire\Docentes\Inasistencias\RankingInasistenciasMateriasCursos;
use App\Http\Controllers\Docentes\RankingInasistenciasMateriasCursosCsvController;
use App\Support\InasistenciasDocentes;
use App\Livewire\Abm\MateriasAnio\MateriasAnioIndex;
use App\Livewire\Abm\ProfesoresPorMateria\ProfesoresPorMateriaIndex;
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
use App\Livewire\CalificacionesSecundario\CargaColoquiosSecundario;
use App\Livewire\CalificacionesSecundario\PlanillaCalificacionesSecundario;
use App\Livewire\CalificacionesSecundario\ActaVolanteColoquiosSecundario;
use App\Livewire\CalificacionesSecundario\PlanillaResumenCalificacionesSecundario;
use App\Livewire\CalificacionesSecundario\ConsultaCalificacionesSecundario;
use App\Livewire\CalificacionesSecundario\CierreAnualIndex;
use App\Livewire\CalificacionesSecundario\CierreAnualHistorial;
use App\Http\Controllers\Certificados\CertificadoAlumnoRegularPdfController;
use App\Http\Controllers\Certificados\CertificadoEstudiosTramitePdfController;
use App\Http\Controllers\Certificados\CertificadoAsistenciaProfesorPdfController;
use App\Http\Controllers\Certificados\ConstanciaDocumentosPdfController;
use App\Http\Controllers\Certificados\PaseParcialPdfController;
use App\Http\Controllers\Certificados\SolicitudDePasePdfController;
use App\Http\Controllers\MatrizAnaliticos\AnaliticoFrentePdfController;
use App\Http\Controllers\MatrizAnaliticos\AnaliticoReversoPdfController;
use App\Livewire\Certificados\CertificadoAlumnoRegularIndex;
use App\Livewire\Certificados\CertificadoEstudiosTramiteIndex;
use App\Livewire\Certificados\CertificadoAsistenciaProfesorIndex;
use App\Livewire\Certificados\ConstanciaDocumentosIndex;
use App\Livewire\Certificados\PaseParcialIndex;
use App\Livewire\Certificados\SolicitudDePaseIndex;
use App\Livewire\MatrizAnaliticos\LibroMatrizDatosAdicionales;
use App\Livewire\MatrizAnaliticos\LibroMatrizEditar;
use App\Livewire\MatrizAnaliticos\LibroMatrizIndex;
use App\Livewire\BoletinesSecundario\BoletinesSecundarioIndex;
use App\Livewire\CalificacionesSecundario\SincroGe;
use App\Livewire\Comunicaciones\BandejaGestion;
use App\Livewire\Comunicaciones\BandejaRevision;
use App\Livewire\Comunicaciones\HiloShow;
use App\Livewire\Comunicaciones\InformeEnvioComunicado;
use App\Livewire\Comunicaciones\NuevoComunicado;
use App\Livewire\Listados\LibroMatricula;
use App\Livewire\Listados\ListadoPorCurso;
use App\Livewire\Parametrizacion\CamposLegajoIndex;
use App\Livewire\Parametrizacion\CamposProfesorIndex;
use App\Livewire\Parametrizacion\ComCanalesIndex;
use App\Livewire\Parametrizacion\ParametrosSistemaForm;
use App\Livewire\Parametrizacion\SolapaLegajoIndex;
use App\Livewire\Parametrizacion\SolapaLegajoProfesorIndex;
use App\Livewire\Seguimiento\Disciplinario\AntecedentesIndex;
use App\Livewire\Seguimiento\Disciplinario\DisciplinarioIndex;
use App\Livewire\Seguimiento\Disciplinario\SancionForm;
use App\Livewire\Seguimiento\Inasistencias\InasistenciaForm;
use App\Livewire\Horarios\HorariosCargaIndex;
use App\Livewire\Horarios\HorariosConfigIndex;
use App\Livewire\Horarios\HorariosImpresionIndex;
use App\Livewire\Seguimiento\Inasistencias\InasistenciasIndex;
use App\Livewire\Seguimiento\Inasistencias\PartesDiariosIndex;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManualSistemaPdfController;
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
    Route::get('/inasistencias/informe', InformeInasistenciasController::class)->name('alumnos.inasistencias.informe');

    Route::get('/notificaciones', [PushController::class, 'index'])->name('alumnos.push.index');

    Route::get('/comunicaciones', BandejaFamilia::class)->name('alumnos.comunicaciones.index');
    Route::get('/comunicaciones/nuevo', NuevoComunicadoFamilia::class)->name('alumnos.comunicaciones.nuevo');
    Route::get('/comunicaciones/preferencias', PreferenciasMedios::class)->name('alumnos.comunicaciones.preferencias');
    Route::get('/comunicaciones/{id}', HiloShowFamilia::class)->whereNumber('id')->name('alumnos.comunicaciones.hilo');
});

// API Push (sesión alumno o docente; fuera del prefix /alumnos para que el SW tenga scope simple)
Route::middleware(['auth:web,alumno'])->prefix('notificaciones-push/api')->group(function () {
    Route::post('/subscribe', [PushApiController::class, 'subscribe'])->name('push.api.subscribe');
    Route::post('/unsubscribe', [PushApiController::class, 'unsubscribe'])->name('push.api.unsubscribe');
    Route::post('/send', [PushApiController::class, 'send'])->name('push.api.send');
});

// Authenticated + school context routes
Route::middleware(['auth', 'school.context'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/manual-sistema.pdf', ManualSistemaPdfController::class)->name('manual.sistema.pdf');

    Route::get('/comunicaciones', BandejaGestion::class)->middleware('permiso:3')->name('comunicaciones.index');
    Route::get('/comunicaciones/revision', BandejaRevision::class)->middleware(['permiso:3', 'permiso:8'])->name('comunicaciones.revision');
    Route::get('/comunicaciones/nuevo', NuevoComunicado::class)->middleware('permiso:4')->name('comunicaciones.nuevo');
    Route::get('/comunicaciones/informe-envio/{id}', InformeEnvioComunicado::class)
        ->middleware(['permiso:3', 'permiso:4'])
        ->whereNumber('id')
        ->name('comunicaciones.informe-envio');
    Route::get('/comunicaciones/{id}', HiloShow::class)->middleware('permiso:3')->whereNumber('id')->name('comunicaciones.hilo');

    // Administración: permisos de usuarios (menú Configuración + orden 0)
    Route::get('/administracion/permisos', PermisosUsuariosIndex::class)
        ->middleware(['permiso:14', 'permiso:0'])
        ->name('admin.permisos');

    Route::middleware('permiso:14')->group(function () {
        Route::get('/notificaciones/push', SuscribirController::class)->name('push.suscribir');
        Route::get('/abm/terlec', TerlecIndex::class)->name('abm.terlec');
        Route::get('/abm/niveles', NivelesIndex::class)->name('abm.niveles');
        Route::get('/abm/cursos', CursosIndex::class)->name('abm.cursos');
        Route::get('/abm/planes', PlanesIndex::class)->name('abm.planes');
        Route::get('/abm/planes/nuevo', PlanesForm::class)->name('abm.planes.create');
        Route::get('/abm/planes/{id}/editar', PlanesForm::class)->whereNumber('id')->name('abm.planes.edit');
        Route::get('/abm/curplan', CurplanIndex::class)->name('abm.curplan');
        Route::get('/abm/curplan/nuevo', CurplanForm::class)->name('abm.curplan.create');
        Route::get('/abm/curplan/{id}/editar', CurplanForm::class)->whereNumber('id')->name('abm.curplan.edit');
        Route::get('/abm/materias-anio', MateriasAnioIndex::class)->name('abm.materias-anio');
        Route::get('/parametrizacion/parametros-sistema', ParametrosSistemaForm::class)
            ->name('param.parametros-sistema');
        Route::get('/parametrizacion/campos-legajo', CamposLegajoIndex::class)
            ->name('param.campos-listado-alumnos'); // nombre conservado para no romper enlaces existentes
        Route::get('/parametrizacion/solapas-legajo', SolapaLegajoIndex::class)
            ->name('param.solapas-legajo');
        Route::get('/parametrizacion/campos-legajo-profesor', CamposProfesorIndex::class)
            ->name('param.campos-legajo-profesor');
        Route::get('/parametrizacion/solapas-legajo-profesor', SolapaLegajoProfesorIndex::class)
            ->name('param.solapas-legajo-profesor');
    });

    Route::get('/abm/profesores-por-materia', ProfesoresPorMateriaIndex::class)->middleware('permiso:1')->name('abm.profesores-por-materia');
    Route::get('/horarios/configuracion', HorariosConfigIndex::class)
        ->middleware('permiso:13')
        ->name('horarios.config');
    Route::get('/parametrizacion/com-canales', ComCanalesIndex::class)
        ->middleware(['permiso:14', 'permiso:5'])
        ->name('param.com-canales');
    Route::get('/abm/legajos', LegajosIndex::class)->name('abm.legajos');
    Route::get('/abm/legajos/nuevo', LegajoForm::class)->middleware('permiso:2')->name('abm.legajos.create');
    Route::get('/abm/legajos/{id}/editar', LegajoForm::class)->whereNumber('id')->name('abm.legajos.edit');

    Route::get('/abm/legajos-profesor', LegajosProfesorIndex::class)->middleware('permiso:1')->name('abm.legajos-profesor');
    Route::get('/abm/legajos-profesor/nuevo', LegajoProfesorForm::class)->middleware('permiso:11')->name('abm.legajos-profesor.create');
    Route::get('/abm/legajos-profesor/{id}/editar', LegajoProfesorForm::class)->whereNumber('id')->name('abm.legajos-profesor.edit');

    Route::middleware('permiso:'.InasistenciasDocentes::PERMISO_ORDEN)->prefix('docentes/inasistencias')->group(function () {
        Route::get('/', InasistenciasDocentesIndex::class)->name('docentes.inasistencias');
        Route::get('/envio-masivo', EnvioMasivoInasistenciasDocentes::class)->name('docentes.inasistencias.envio-masivo');
        Route::get('/ranking', RankingInasistenciasMateriasCursos::class)->name('docentes.inasistencias.ranking');
        Route::get('/ranking/exportar-csv', RankingInasistenciasMateriasCursosCsvController::class)->name('docentes.inasistencias.ranking.csv');
        Route::get('/{idProfesor}', InasistenciasDocenteShow::class)->whereNumber('idProfesor')->name('docentes.inasistencias.show');
        Route::get('/{idProfesor}/nuevo', InasistenciaDocenteForm::class)->whereNumber('idProfesor')->name('docentes.inasistencias.create');
        Route::get('/{idProfesor}/{id}/editar', InasistenciaDocenteForm::class)->whereNumber(['idProfesor', 'id'])->name('docentes.inasistencias.edit');
        Route::get('/{idProfesor}/cargos/{idCxp?}', CargosDocenteIndex::class)->whereNumber(['idProfesor', 'idCxp'])->name('docentes.inasistencias.cargos');
        Route::get('/{idProfesor}/informe/{bimestre}', InformeBimestreShow::class)->whereNumber(['idProfesor', 'bimestre'])->name('docentes.inasistencias.informe');
        Route::get('/{idProfesor}/informe/{bimestre}/pdf', InformeInasistenciasDocentePdfController::class)
            ->whereNumber(['idProfesor', 'bimestre'])
            ->name('docentes.inasistencias.informe.pdf');
    });

    Route::get('/listados/por-curso', ListadoPorCurso::class)->name('listados.por-curso');
    Route::get('/listados/por-curso/listado', ListadoCursoPdfController::class)->name('listados.por-curso.pdf');
    Route::get('/listados/exportar-excel', EstudiantesExcelController::class)
        ->name('listados.exportar-excel');
    Route::get('/listados/libro-matricula', LibroMatricula::class)->name('listados.libro-matricula');
    Route::get('/listados/libro-matricula/pdf', LibroMatriculaPdfController::class)
        ->name('listados.libro-matricula.pdf');

    Route::middleware('permiso:12')->group(function () {
        Route::get('/examenes/materias-adeudadas/entrar', [MateriasAdeudadasEntradaController::class, 'listado'])
            ->name('examenes.materias-adeudadas.entrar');
        Route::get('/examenes/materias-adeudadas', MateriasAdeudadasListadoIndex::class)
            ->name('examenes.materias-adeudadas');
        Route::get('/examenes/materias-adeudadas/pdf', MateriasAdeudadasPdfController::class)
            ->name('examenes.materias-adeudadas.pdf');
        Route::get('/examenes/materias-adeudadas/gestion/entrar', [MateriasAdeudadasEntradaController::class, 'gestion'])
            ->name('examenes.materias-adeudadas.gestion.entrar');
        Route::get('/examenes/materias-adeudadas/gestion', MateriasAdeudadasGestionIndex::class)
            ->name('examenes.materias-adeudadas.gestion');
        Route::get('/examenes/materias-adeudadas/gestion/carga/{idLegajos}', MateriasAdeudadasCargaManualIndex::class)
            ->whereNumber('idLegajos')
            ->name('examenes.materias-adeudadas.gestion.carga');
        Route::get('/examenes/materias-adeudadas/gestion/inscribir/{idLegajos}', MateriasAdeudadasInscripcionIndex::class)
            ->whereNumber('idLegajos')
            ->name('examenes.materias-adeudadas.gestion.inscribir');
        Route::get('/examenes/materias-adeudadas/gestion/notas/{idLegajos}', MateriasAdeudadasNotasIndex::class)
            ->whereNumber('idLegajos')
            ->name('examenes.materias-adeudadas.gestion.notas');
        Route::get('/examenes/materias-adeudadas/gestion/historial/{idLegajos}', HistorialExamenesIndex::class)
            ->whereNumber('idLegajos')
            ->name('examenes.materias-adeudadas.gestion.historial');
        Route::get('/examenes/borrar-inscripciones', BorrarInscripcionesExamenIndex::class)
            ->name('examenes.borrar-inscripciones');
        Route::get('/examenes/actas-volantes-previos/entrar', [MateriasAdeudadasEntradaController::class, 'actaVolante'])
            ->name('examenes.acta-volante-previos.entrar');
        Route::get('/examenes/actas-volantes-previos', ActaVolantePreviosIndex::class)
            ->name('examenes.acta-volante-previos');
        Route::get('/examenes/actas-volantes-previos/pdf', ActaVolantePreviosPdfController::class)
            ->name('examenes.acta-volante-previos.pdf');
        Route::get('/examenes/permiso-examen/entrar', [MateriasAdeudadasEntradaController::class, 'permisoExamen'])
            ->name('examenes.permiso-examen.entrar');
        Route::get('/examenes/permiso-examen', PermisoExamenIndex::class)
            ->name('examenes.permiso-examen');
        Route::post('/examenes/permiso-examen/pdf', [PermisoExamenPdfController::class, 'preparar'])
            ->name('examenes.permiso-examen.pdf.preparar');
        Route::get('/examenes/permiso-examen/pdf', PermisoExamenPdfController::class)
            ->name('examenes.permiso-examen.pdf');
    });

    Route::get('/horarios/carga', HorariosCargaIndex::class)
        ->middleware('permiso:13')
        ->name('horarios.carga');
    Route::get('/horarios/impresion', HorariosImpresionIndex::class)
        ->name('horarios.impresion');
    Route::get('/horarios/pdf/curso', HorarioCursoPdfController::class)
        ->name('horarios.pdf.curso');
    Route::get('/horarios/pdf/profesor', HorarioProfesorPdfController::class)
        ->name('horarios.pdf.profesor');

    // Calificaciones (nivel secundario): sincro GE/CIDI, carga y consulta institucional
    Route::get('/calificaciones-secundario/sincro-ge', SincroGe::class)
        ->middleware('permiso:9')
        ->name('calificacionesSecundario.sincroGe');
    Route::get('/calificaciones-secundario/carga', CargaCalificacionesSecundario::class)
        ->middleware('permiso:9')
        ->name('calificacionesSecundario.carga');
    Route::get('/calificaciones-secundario/coloquios', CargaColoquiosSecundario::class)
        ->middleware('permiso:10')
        ->name('calificacionesSecundario.coloquios');
    Route::get('/calificaciones-secundario/actas-volantes-coloquio', ActaVolanteColoquiosSecundario::class)
        ->name('calificacionesSecundario.actaVolanteColoquios');
    Route::get('/calificaciones-secundario/actas-volantes-coloquio/pdf', ActaVolanteColoquiosPdfController::class)
        ->name('calificacionesSecundario.actaVolanteColoquios.pdf');
    Route::get('/calificaciones-secundario/planilla', PlanillaCalificacionesSecundario::class)
        ->name('calificacionesSecundario.planilla');
    Route::get('/calificaciones-secundario/planilla/pdf', PlanillaCalificacionesPdfController::class)
        ->name('calificacionesSecundario.planilla.pdf');
    Route::get('/calificaciones-secundario/planilla-resumen', PlanillaResumenCalificacionesSecundario::class)
        ->name('calificacionesSecundario.planillaResumen');
    Route::get('/calificaciones-secundario/planilla-resumen/pdf', PlanillaResumenCalificacionesPdfController::class)
        ->name('calificacionesSecundario.planillaResumen.pdf');
    Route::get('/calificaciones-secundario/consulta', ConsultaCalificacionesSecundario::class)
        ->name('calificacionesSecundario.consulta');
    Route::get('/calificaciones-secundario/consulta/pdf', ConsultaCalificacionesSecundarioPdfController::class)
        ->name('calificacionesSecundario.consulta.pdf');
    Route::get('/calificaciones-secundario/cierre-anual', CierreAnualIndex::class)
        ->middleware('permiso:15')
        ->name('calificacionesSecundario.cierreAnual');
    Route::get('/calificaciones-secundario/cierre-anual/{idLegajos}/historial', CierreAnualHistorial::class)
        ->middleware('permiso:15')
        ->whereNumber('idLegajos')
        ->name('calificacionesSecundario.cierreAnual.historial');

    // Libro matriz / pase / analítico
    Route::get('/matriz-analiticos/libro-matriz', LibroMatrizIndex::class)
        ->middleware('permiso:16')
        ->name('matrizAnaliticos.libroMatriz');
    Route::get('/matriz-analiticos/libro-matriz/{idLegajos}/editar', LibroMatrizEditar::class)
        ->middleware('permiso:16')
        ->whereNumber('idLegajos')
        ->name('matrizAnaliticos.libroMatriz.editar');
    Route::get('/matriz-analiticos/libro-matriz/{idLegajos}/datos-adicionales', LibroMatrizDatosAdicionales::class)
        ->middleware('permiso:16')
        ->whereNumber('idLegajos')
        ->name('matrizAnaliticos.libroMatriz.datosAdicionales');
    Route::get('/matriz-analiticos/libro-matriz/{idLegajos}/pdf-frente', AnaliticoFrentePdfController::class)
        ->middleware('permiso:16')
        ->whereNumber('idLegajos')
        ->name('matrizAnaliticos.libroMatriz.pdfFrente');
    Route::get('/matriz-analiticos/libro-matriz/{idLegajos}/pdf-reverso', AnaliticoReversoPdfController::class)
        ->middleware('permiso:16')
        ->whereNumber('idLegajos')
        ->name('matrizAnaliticos.libroMatriz.pdfReverso');

    // Certificados — alumno regular
    Route::get('/certificados/alumno-regular', CertificadoAlumnoRegularIndex::class)
        ->middleware('permiso:17')
        ->name('certificados.alumnoRegular');
    Route::get('/certificados/alumno-regular/{idLegajos}/pdf', CertificadoAlumnoRegularPdfController::class)
        ->middleware('permiso:17')
        ->whereNumber('idLegajos')
        ->name('certificados.alumnoRegular.pdf');

    // Certificados — estudios en trámite
    Route::get('/certificados/estudios-tramite', CertificadoEstudiosTramiteIndex::class)
        ->middleware('permiso:18')
        ->name('certificados.estudiosTramite');
    Route::get('/certificados/estudios-tramite/{idLegajos}/pdf', CertificadoEstudiosTramitePdfController::class)
        ->middleware('permiso:18')
        ->whereNumber('idLegajos')
        ->name('certificados.estudiosTramite.pdf');

    // Certificados — constancia de documentos
    Route::get('/certificados/constancia-documentos', ConstanciaDocumentosIndex::class)
        ->middleware('permiso:19')
        ->name('certificados.constanciaDocumentos');
    Route::get('/certificados/constancia-documentos/{idLegajos}/pdf', ConstanciaDocumentosPdfController::class)
        ->middleware('permiso:19')
        ->whereNumber('idLegajos')
        ->name('certificados.constanciaDocumentos.pdf');

    // Certificados — asistencia del profesor
    Route::get('/certificados/asistencia-profesor', CertificadoAsistenciaProfesorIndex::class)
        ->middleware('permiso:20')
        ->name('certificados.asistenciaProfesor');
    Route::get('/certificados/asistencia-profesor/{idProfesores}/pdf', CertificadoAsistenciaProfesorPdfController::class)
        ->middleware('permiso:20')
        ->whereNumber('idProfesores')
        ->name('certificados.asistenciaProfesor.pdf');

    // Certificados — pase parcial
    Route::get('/certificados/pase-parcial', PaseParcialIndex::class)
        ->middleware('permiso:21')
        ->name('certificados.paseParcial');
    Route::get('/certificados/pase-parcial/{idLegajos}/pdf', PaseParcialPdfController::class)
        ->middleware('permiso:21')
        ->whereNumber('idLegajos')
        ->name('certificados.paseParcial.pdf');

    // Certificados — solicitud de pase
    Route::get('/certificados/solicitud-de-pase', SolicitudDePaseIndex::class)
        ->middleware('permiso:22')
        ->name('certificados.solicitudDePase');
    Route::get('/certificados/solicitud-de-pase/{idLegajos}/pdf', SolicitudDePasePdfController::class)
        ->middleware('permiso:22')
        ->whereNumber('idLegajos')
        ->name('certificados.solicitudDePase.pdf');

    // Boletines / informe de progreso escolar (nivel secundario)
    Route::get('/boletines-secundario', BoletinesSecundarioIndex::class)
        ->name('boletinesSecundario.index');
    Route::get('/boletines-secundario/pdf', BoletinSecundarioPdfController::class)
        ->name('boletinesSecundario.pdf');
    Route::get('/boletines-secundario/pdf-lote', BoletinSecundarioLotePdfController::class)
        ->name('boletinesSecundario.pdfLote');

    // Seguimiento disciplinario
    Route::get('/seguimiento/disciplinario', DisciplinarioIndex::class)
        ->name('seguimiento.disciplinario');
    Route::get('/seguimiento/disciplinario/nuevo', SancionForm::class)
        ->name('seguimiento.disciplinario.create');
    Route::get('/seguimiento/disciplinario/{id}/editar', SancionForm::class)
        ->whereNumber('id')
        ->name('seguimiento.disciplinario.edit');

    Route::get('/seguimiento/disciplinario/{id}/imprimir', SancionComunicadoPdfController::class)
        ->whereNumber('id')
        ->name('seguimiento.disciplinario.print');

    Route::get('/seguimiento/disciplinario/{idMatricula}/antecedentes', AntecedentesIndex::class)
        ->whereNumber('idMatricula')
        ->name('seguimiento.disciplinario.antecedentes');

    Route::get('/seguimiento/disciplinario/{idMatricula}/antecedentes/pdf', AntecedentesDisciplinariosPdfController::class)
        ->whereNumber('idMatricula')
        ->name('seguimiento.disciplinario.antecedentes.pdf');

    // Gestión de inasistencias
    Route::get('/seguimiento/inasistencias', InasistenciasIndex::class)
        ->name('seguimiento.inasistencias');
    Route::get('/seguimiento/inasistencias/nuevo', InasistenciaForm::class)
        ->name('seguimiento.inasistencias.create');
    Route::get('/seguimiento/inasistencias/{id}/editar', InasistenciaForm::class)
        ->whereNumber('id')
        ->name('seguimiento.inasistencias.edit');
    Route::get('/seguimiento/inasistencias/{idMatricula}/informe/pdf', InformeInasistenciasPdfController::class)
        ->whereNumber('idMatricula')
        ->name('seguimiento.inasistencias.informe.pdf');

    Route::get('/seguimiento/partes-diarios', PartesDiariosIndex::class)
        ->name('seguimiento.partes-diarios');
    Route::get('/seguimiento/partes-diarios/pdf', ParteDiarioPreceptorPdfController::class)
        ->name('seguimiento.partes-diarios.pdf');
});
