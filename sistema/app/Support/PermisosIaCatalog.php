<?php

namespace App\Support;

/**
 * Órdenes del catálogo {@see permisos_ia} (posición en profesores.permisos_ia).
 *
 * Cada módulo debe comprobar acceso con tienePermiso(self::ORDEN_*)
 * o middleware permiso:N / permiso-config:N.
 */
final class PermisosIaCatalog
{
    public const ADMIN_PERMISOS = 0;

    public const TOMA_ASISTENCIA_CLASE = 1;

    public const LEGAJOS_ESTUDIANTES = 2;

    public const COM_BANDEJA = 3;

    public const COM_NUEVO = 4;

    public const COM_CANALES = 5;

    public const COM_BORRAR_PROPIO = 6;

    public const COM_BORRAR_OTROS = 7;

    public const COM_REVISION = 8;

    public const CALIF_SINCRO_CARGA = 9;

    public const CALIF_COLOQUIOS = 10;

    public const LEGAJOS_DOCENTES = 11;

    public const EXAMENES = 12;

    public const HORARIOS = 13;

    public const PERMISOS_POR_USUARIO = 14;

    public const CALIF_CIERRE_ANUAL = 15;

    public const MATRIZ_ANALITICO = 16;

    public const CERT_ALUMNO_REGULAR = 17;

    public const CERT_ESTUDIOS_TRAMITE = 18;

    public const CERT_CONSTANCIA_DOCS = 19;

    public const CERT_ASISTENCIA_PROF = 20;

    public const CERT_PASE_PARCIAL = 21;

    public const CERT_SOLICITUD_PASE = 22;

    public const INASISTENCIAS_DOCENTES = 23;

    public const INASISTENCIAS_SINCRO_CIDI = 24;

    public const SEGUIMIENTO_DISCIPLINARIO = 37;

    public const INASISTENCIAS_ESTUDIANTES_GESTION = 38;

    public const ASPIRANTES_GESTION = 39;

    public const ASPIRANTES_CAMPOS = 40;

    public const COM_AUDITORIA = 43;

    public const MATRICULA_WEB_DOCUMENTOS = 44;

    public const SOLICITUDES_EVALUACION_GESTION = 45;

    public const LEGAJOS_FAMILIAS_GESTION = 46;

    /**
     * Nivel Administración: crear/editar/eliminar legajos y matrículas en cualquier nivel pedagógico (1–4).
     * Sin este permiso: solo consulta de legajos en Administración (sin alta/edición cross-nivel).
     */
    public const LEGAJOS_MODIFICAR_ADMIN = 47;

    /** @return list<array{id: int, orden: int, tema: string, descripcion: string}> */
    public static function definicionCatalogo(): array
    {
        return [
            ['id' => 1, 'orden' => self::ADMIN_PERMISOS, 'tema' => 'ADMINISTRACIÓN', 'descripcion' => 'Administrar permisos del portal de gestión (sistema nuevo).'],
            ['id' => 2, 'orden' => self::TOMA_ASISTENCIA_CLASE, 'tema' => 'ASISTENCIA ESTUDIANTES', 'descripcion' => 'Toma de asistencia a clase por curso, fecha y tipo (clase / educación física).'],
            ['id' => 3, 'orden' => self::LEGAJOS_ESTUDIANTES, 'tema' => 'LEGAJOS ESTUDIANTES', 'descripcion' => 'Crear, editar y eliminar legajos de estudiantes; gestionar matrículas.'],
            ['id' => 4, 'orden' => self::COM_BANDEJA, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Ver la bandeja de comunicados y los hilos de conversación.'],
            ['id' => 5, 'orden' => self::COM_NUEVO, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Iniciar nuevos comunicados hacia familias.'],
            ['id' => 6, 'orden' => self::COM_CANALES, 'tema' => 'COMUNICACIONES - CONFIG', 'descripcion' => 'Administrar la configuración de canales (quién puede comunicarse con quién y por qué medios).'],
            ['id' => 7, 'orden' => self::COM_BORRAR_PROPIO, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Borrar mensajes propios en un hilo.'],
            ['id' => 8, 'orden' => self::COM_BORRAR_OTROS, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Borrar mensajes de otros participantes en un hilo.'],
            ['id' => 9, 'orden' => self::COM_REVISION, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Acceder a la bandeja de revisión de comunicados.'],
            ['id' => 10, 'orden' => self::CALIF_SINCRO_CARGA, 'tema' => 'CALIFICACIONES SECUNDARIO', 'descripcion' => 'Importar calificaciones desde CIDI/GE y carga manual de calificaciones (secundario).'],
            ['id' => 11, 'orden' => self::CALIF_COLOQUIOS, 'tema' => 'CALIFICACIONES SECUNDARIO', 'descripcion' => 'Carga de coloquios Dic / Feb (secundario).'],
            ['id' => 12, 'orden' => self::LEGAJOS_DOCENTES, 'tema' => 'LEGAJOS DOCENTES', 'descripcion' => 'Crear, editar y eliminar legajos de docentes; asignar y quitar docentes en materias (ppc).'],
            ['id' => 13, 'orden' => self::EXAMENES, 'tema' => 'EXÁMENES', 'descripcion' => 'Módulo de exámenes: materias adeudadas, gestión, listados y borrado de inscripciones.'],
            ['id' => 14, 'orden' => self::HORARIOS, 'tema' => 'HORARIOS', 'descripcion' => 'Configuración de horarios (turnos, días, reloj) y carga de horas cátedra por docente.'],
            ['id' => 15, 'orden' => self::PERMISOS_POR_USUARIO, 'tema' => 'ADMINISTRACIÓN', 'descripcion' => 'Consultar permisos concedidos por usuario (módulo Permisos por Usuario).'],
            ['id' => 17, 'orden' => self::CALIF_CIERRE_ANUAL, 'tema' => 'CALIFICACIONES SECUNDARIO', 'descripcion' => 'Cierre anual: historial de calificaciones y pasaje al matriz (Dic / Feb).'],
            ['id' => 18, 'orden' => self::MATRIZ_ANALITICO, 'tema' => 'MATRÍZ Y ANALÍTICOS', 'descripcion' => 'Libro matriz, pase y certificado analítico: consulta y edición de calificaciones en matriz.'],
            ['id' => 19, 'orden' => self::CERT_ALUMNO_REGULAR, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Certificado escolar de alumno/a regular: listado de matriculados del año en curso y emisión de PDF.'],
            ['id' => 20, 'orden' => self::CERT_ESTUDIOS_TRAMITE, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Constancia de certificado de estudios en trámite: listado de matriculados y emisión de PDF.'],
            ['id' => 21, 'orden' => self::CERT_CONSTANCIA_DOCS, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Constancia de documentos: listado de matriculados y emisión de PDF.'],
            ['id' => 22, 'orden' => self::CERT_ASISTENCIA_PROF, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Certificado de asistencia del profesor: listado de personal del legajo y emisión de PDF.'],
            ['id' => 23, 'orden' => self::CERT_PASE_PARCIAL, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Pase parcial: listado de legajos de nivel medio, solicitud y emisión de PDF.'],
            ['id' => 24, 'orden' => self::CERT_SOLICITUD_PASE, 'tema' => 'CERTIFICADOS', 'descripcion' => 'Solicitud de pase: listado de legajos de nivel medio, datos en paseprovisorio y emisión de PDF.'],
            ['id' => 25, 'orden' => self::INASISTENCIAS_DOCENTES, 'tema' => 'INASISTENCIAS DOCENTES', 'descripcion' => 'Gestión de inasistencias docentes: cargos, registros, informes por bimestre y PDF.'],
            ['id' => 26, 'orden' => self::INASISTENCIAS_SINCRO_CIDI, 'tema' => 'ASISTENCIA ESTUDIANTES', 'descripcion' => 'Importar inasistencias de estudiantes desde CSV CIDI/GE (InasistenciasDetalle).'],
            ['id' => 27, 'orden' => PermisosConfiguracion::TERLEC, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Términos lectivos.'],
            ['id' => 28, 'orden' => PermisosConfiguracion::NIVELES, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Niveles educativos.'],
            ['id' => 29, 'orden' => PermisosConfiguracion::CAMPOS_LEGAJO_ESTUDIANTE, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Campos activos del legajo del estudiante.'],
            ['id' => 30, 'orden' => PermisosConfiguracion::SOLAPAS_LEGAJO_ESTUDIANTE, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Solapas del legajo del estudiante.'],
            ['id' => 31, 'orden' => PermisosConfiguracion::CAMPOS_LEGAJO_DOCENTE, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Campos activos del legajo del docente.'],
            ['id' => 32, 'orden' => PermisosConfiguracion::SOLAPAS_LEGAJO_DOCENTE, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Solapas del legajo del docente.'],
            ['id' => 33, 'orden' => PermisosConfiguracion::PARAMETROS_SISTEMA, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Parámetros del sistema.'],
            ['id' => 34, 'orden' => PermisosConfiguracion::NOTIFICACIONES_PUSH, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Notificaciones push (suscripción en este dispositivo).'],
            ['id' => 35, 'orden' => PermisosConfiguracion::PLANES_ESTUDIO, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Gestión de planes de estudio.'],
            ['id' => 36, 'orden' => PermisosConfiguracion::CURSOS_MATERIAS_PLAN, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Gestión de cursos y materias del plan.'],
            ['id' => 37, 'orden' => PermisosConfiguracion::CURSOS_ANIO, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Gestión de cursos / grados / salas del año.'],
            ['id' => 38, 'orden' => PermisosConfiguracion::MATERIAS_ANIO, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Gestión de asignaturas del año.'],
            ['id' => 39, 'orden' => self::SEGUIMIENTO_DISCIPLINARIO, 'tema' => 'SEGUIMIENTO DISCIPLINARIO', 'descripcion' => 'Registro de sanciones, antecedentes disciplinarios e impresión de comunicados.'],
            ['id' => 40, 'orden' => self::INASISTENCIAS_ESTUDIANTES_GESTION, 'tema' => 'ASISTENCIA ESTUDIANTES', 'descripcion' => 'Gestión de inasistencias del estudiante: alta, edición, baja e informe individual en PDF.'],
            ['id' => 41, 'orden' => self::ASPIRANTES_GESTION, 'tema' => 'ASPIRANTES', 'descripcion' => 'Gestión de aspirantes: parametrización de la instancia de registro, cursos disponibles y listado de inscriptos.'],
            ['id' => 42, 'orden' => self::ASPIRANTES_CAMPOS, 'tema' => 'CONFIGURACIÓN', 'descripcion' => 'Campos activos del formulario público de aspirantes.'],
            ['id' => 43, 'orden' => self::COM_AUDITORIA, 'tema' => 'COMUNICACIONES', 'descripcion' => 'Auditoría de comunicación institucional: consultar borrados y marcas de lectura en bandejas.'],
            ['id' => 44, 'orden' => self::MATRICULA_WEB_DOCUMENTOS, 'tema' => 'MATRÍCULA WEB', 'descripcion' => 'Documentos de aceptación (PDF por nivel): compromiso educativo, AEC, normativas y traslado para el portal de estudiantes.'],
            ['id' => 45, 'orden' => self::SOLICITUDES_EVALUACION_GESTION, 'tema' => 'CALIFICACIONES SECUNDARIO', 'descripcion' => 'Gestión de solicitudes de evaluación: listado por fecha, alta, edición y baja de evaluaciones programadas (tabla evaluac).'],
            ['id' => 46, 'orden' => self::LEGAJOS_FAMILIAS_GESTION, 'tema' => 'LEGAJOS ESTUDIANTES', 'descripcion' => 'Gestionar familias de estudiantes: crear, editar, eliminar y asignar o quitar vínculos con legajos (la consulta permanece disponible para todos).'],
            ['id' => 47, 'orden' => self::LEGAJOS_MODIFICAR_ADMIN, 'tema' => 'LEGAJOS ESTUDIANTES', 'descripcion' => 'Nivel Administración: crear, editar, eliminar legajos y matrículas en Inicial, Primario y Secundario (cualquier nivel pedagógico del ciclo activo).'],
        ];
    }

    public static function maxOrden(): int
    {
        $max = 0;
        foreach (self::definicionCatalogo() as $row) {
            $max = max($max, (int) $row['orden']);
        }

        return $max;
    }
}
