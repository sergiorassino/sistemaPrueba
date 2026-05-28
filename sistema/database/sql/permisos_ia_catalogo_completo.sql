-- =============================================================================
-- Catálogo completo permisos_ia (39 filas, órdenes 0–38)
-- Equivalente a migración 2026_05_27_200000_sync_permisos_ia_catalogo_completo.php
--
-- ADVERTENCIA: DELETE borra todo el catálogo. profesores.permisos_ia NO se modifica.
-- Si antes el orden 14 era «Configuración completa», ahora es «Permisos por Usuario»;
-- revisar asignaciones y usar permisos granulares 25–36 para Configuración.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `permisos_ia`;

INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (1, 0, 'ADMINISTRACIÓN', 'Administrar permisos del portal de gestión (sistema nuevo).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (2, 1, 'ASISTENCIA ESTUDIANTES', 'Toma de asistencia a clase por curso, fecha y tipo (clase / educación física).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (3, 2, 'LEGAJOS ESTUDIANTES', 'Crear, editar y eliminar legajos de estudiantes; gestionar matrículas.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (4, 3, 'COMUNICACIONES', 'Ver la bandeja de comunicados y los hilos de conversación.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (5, 4, 'COMUNICACIONES', 'Iniciar nuevos comunicados hacia familias.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (6, 5, 'COMUNICACIONES - CONFIG', 'Administrar la configuración de canales (quién puede comunicarse con quién y por qué medios).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (7, 6, 'COMUNICACIONES', 'Borrar mensajes propios en un hilo.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (8, 7, 'COMUNICACIONES', 'Borrar mensajes de otros participantes en un hilo.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (9, 8, 'COMUNICACIONES', 'Acceder a la bandeja de revisión de comunicados.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (10, 9, 'CALIFICACIONES SECUNDARIO', 'Importar calificaciones desde CIDI/GE y carga manual de calificaciones (secundario).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (11, 10, 'CALIFICACIONES SECUNDARIO', 'Carga de coloquios Dic / Feb (secundario).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (12, 11, 'LEGAJOS DOCENTES', 'Crear, editar y eliminar legajos de docentes; asignar y quitar docentes en materias (ppc).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (13, 12, 'EXÁMENES', 'Módulo de exámenes: materias adeudadas, gestión, listados y borrado de inscripciones.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (14, 13, 'HORARIOS', 'Configuración de horarios (turnos, días, reloj) y carga de horas cátedra por docente.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (15, 14, 'ADMINISTRACIÓN', 'Consultar permisos concedidos por usuario (módulo Permisos por Usuario).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (17, 15, 'CALIFICACIONES SECUNDARIO', 'Cierre anual: historial de calificaciones y pasaje al matriz (Dic / Feb).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (18, 16, 'MATRÍZ Y ANALÍTICOS', 'Libro matriz, pase y certificado analítico: consulta y edición de calificaciones en matriz.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (19, 17, 'CERTIFICADOS', 'Certificado escolar de alumno/a regular: listado de matriculados del año en curso y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (20, 18, 'CERTIFICADOS', 'Constancia de certificado de estudios en trámite: listado de matriculados y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (21, 19, 'CERTIFICADOS', 'Constancia de documentos: listado de matriculados y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (22, 20, 'CERTIFICADOS', 'Certificado de asistencia del profesor: listado de personal del legajo y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (23, 21, 'CERTIFICADOS', 'Pase parcial: listado de legajos de nivel medio, solicitud y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (24, 22, 'CERTIFICADOS', 'Solicitud de pase: listado de legajos de nivel medio, datos en paseprovisorio y emisión de PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (25, 23, 'INASISTENCIAS DOCENTES', 'Gestión de inasistencias docentes: cargos, registros, informes por bimestre y PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (26, 24, 'ASISTENCIA ESTUDIANTES', 'Importar inasistencias de estudiantes desde CSV CIDI/GE (InasistenciasDetalle).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (27, 25, 'CONFIGURACIÓN', 'Términos lectivos.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (28, 26, 'CONFIGURACIÓN', 'Niveles educativos.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (29, 27, 'CONFIGURACIÓN', 'Campos activos del legajo del estudiante.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (30, 28, 'CONFIGURACIÓN', 'Solapas del legajo del estudiante.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (31, 29, 'CONFIGURACIÓN', 'Campos activos del legajo del docente.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (32, 30, 'CONFIGURACIÓN', 'Solapas del legajo del docente.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (33, 31, 'CONFIGURACIÓN', 'Parámetros del sistema.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (34, 32, 'CONFIGURACIÓN', 'Notificaciones push (suscripción en este dispositivo).');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (35, 33, 'CONFIGURACIÓN', 'Gestión de planes de estudio.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (36, 34, 'CONFIGURACIÓN', 'Gestión de cursos y materias del plan.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (37, 35, 'CONFIGURACIÓN', 'Gestión de cursos / grados / salas del año.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (38, 36, 'CONFIGURACIÓN', 'Gestión de asignaturas del año.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (39, 37, 'SEGUIMIENTO DISCIPLINARIO', 'Registro de sanciones, antecedentes disciplinarios e impresión de comunicados.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (40, 38, 'ASISTENCIA ESTUDIANTES', 'Gestión de inasistencias del estudiante: alta, edición, baja e informe individual en PDF.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (41, 39, 'ASPIRANTES', 'Gestión de aspirantes: parametrización de la instancia de registro, cursos disponibles y listado de inscriptos.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (42, 40, 'CONFIGURACIÓN', 'Campos activos del formulario público de aspirantes.');
INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES (43, 43, 'COMUNICACIONES', 'Auditoría de comunicación institucional: consultar borrados y marcas de lectura en bandejas.');

ALTER TABLE `permisos_ia` AUTO_INCREMENT = 44;

SET FOREIGN_KEY_CHECKS = 1;
