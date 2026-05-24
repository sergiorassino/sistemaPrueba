-- =============================================================================
-- Actualización de esquema — idempotente (re-ejecutable)
-- Compatible con MySQL 5.7+ / MariaDB 10.x
--
-- - Tablas: CREATE TABLE IF NOT EXISTS
-- - Columnas: procedimiento sp_add_column_if_missing
-- - Datos semilla: INSERT ... ON DUPLICATE KEY UPDATE
--
-- Ejecutar en la base del colegio (phpMyAdmin, HeidiSQL, mysql CLI).
-- Revisar backup antes de aplicar en producción.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Utilidad: agregar columna solo si no existe
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE sp_add_column_if_missing(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table
          AND COLUMN_NAME = p_column
    ) THEN
        SET @ddl = CONCAT(
            'ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_definition
        );
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- =============================================================================
-- 1. Legajo alumnos — solapas y campos
-- =============================================================================
CREATE TABLE IF NOT EXISTS `solapas_legajo` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `slug` varchar(30) NOT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solapas_legajo_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solapas_legajo` (`id`, `nombre`, `slug`, `orden`) VALUES
(1, 'Alumno',   'alumno',   1),
(2, 'Contacto', 'contacto', 2),
(3, 'Madre',    'madre',    3),
(4, 'Padre',    'padre',    4),
(5, 'Tutor',    'tutor',    5),
(6, 'Otros',    'otros',    6)
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`),
  `orden`  = VALUES(`orden`);

CREATE TABLE IF NOT EXISTS `campos_legajo` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `columna` varchar(64) NOT NULL,
  `etiqueta` varchar(150) DEFAULT NULL,
  `visible_listado` tinyint(1) NOT NULL DEFAULT 1,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `solapa_legajo_id` bigint(20) unsigned DEFAULT NULL,
  `orden_en_solapa` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campos_listado_alumnos_columna_unique` (`columna`),
  KEY `campos_legajo_solapa_legajo_id_foreign` (`solapa_legajo_id`),
  CONSTRAINT `campos_legajo_solapa_legajo_id_foreign`
    FOREIGN KEY (`solapa_legajo_id`) REFERENCES `solapas_legajo` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. Entidad (ento) y cursos
-- =============================================================================
CALL sp_add_column_if_missing('ento', 'logo_path', 'varchar(255) DEFAULT NULL AFTER `replegal`');
CALL sp_add_column_if_missing('ento', 'logo_original_name', 'varchar(255) DEFAULT NULL AFTER `logo_path`');
CALL sp_add_column_if_missing('ento', 'cuit', 'VARCHAR(13) NULL DEFAULT NULL AFTER `insti`');
CALL sp_add_column_if_missing('ento', 'ee', 'VARCHAR(20) NULL DEFAULT NULL AFTER `cue`');
CALL sp_add_column_if_missing('cursos', 'turno', 'VARCHAR(20) NULL DEFAULT NULL AFTER `s`');
CALL sp_add_column_if_missing('cursos', 'idTurnoClase', 'TINYINT UNSIGNED NULL DEFAULT NULL');

-- =============================================================================
-- 3. Módulo comunicaciones (com_*)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `com_canales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rol_emisor` enum('directivo','preceptor','profesor','familia') NOT NULL,
  `rol_receptor` enum('directivo','preceptor','profesor','familia') NOT NULL,
  `puede_iniciar` tinyint(1) NOT NULL DEFAULT 0,
  `puede_responder` tinyint(1) NOT NULL DEFAULT 0,
  `medios_permitidos` json DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_canal_par` (`rol_emisor`,`rol_receptor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_hilos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(200) NOT NULL,
  `cuerpo_inicial_id` bigint(20) unsigned DEFAULT NULL,
  `scope` enum('alumno','varios_alumnos','curso','varios_cursos','colegio','docentes') NOT NULL,
  `id_legajo` int(10) unsigned DEFAULT NULL,
  `id_curso` int(10) unsigned DEFAULT NULL,
  `cursos_envio` json DEFAULT NULL,
  `id_nivel` int(10) unsigned DEFAULT NULL,
  `id_terlec` int(10) unsigned DEFAULT NULL,
  `creado_por_tipo` enum('profesor','familia') NOT NULL,
  `creado_por_id` int(10) unsigned NOT NULL,
  `creado_por_rol` varchar(30) DEFAULT NULL,
  `estado` enum('abierto','cerrado') NOT NULL DEFAULT 'abierto',
  `familia_puede_responder` tinyint(1) NOT NULL DEFAULT 1,
  `docentes_permite_respuestas` tinyint(1) DEFAULT NULL COMMENT 'Docentes: NULL=permitir respuestas (legado); 0=solo informativo; 1=permitir',
  `ultimo_mensaje_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `com_hilos_id_nivel_id_terlec_index` (`id_nivel`,`id_terlec`),
  KEY `com_hilos_id_legajo_index` (`id_legajo`),
  KEY `com_hilos_id_curso_index` (`id_curso`),
  KEY `com_hilos_creado_por_tipo_creado_por_id_index` (`creado_por_tipo`,`creado_por_id`),
  KEY `com_hilos_ultimo_mensaje_at_index` (`ultimo_mensaje_at`),
  KEY `com_hilos_cuerpo_inicial_id_index` (`cuerpo_inicial_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_hilos_participantes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_hilo` bigint(20) unsigned NOT NULL,
  `tipo` enum('profesor','familia') NOT NULL,
  `id_profesor` int(10) unsigned DEFAULT NULL,
  `id_legajo` int(10) unsigned DEFAULT NULL,
  `rol` varchar(30) DEFAULT NULL,
  `vinculo` enum('madre','padre','tutor','resp_admin','otro') DEFAULT NULL,
  `nombre_snapshot` varchar(150) DEFAULT NULL,
  `dni_snapshot` varchar(20) DEFAULT NULL,
  `agregado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `com_hilos_participantes_id_hilo_tipo_id_profesor_index` (`id_hilo`,`tipo`,`id_profesor`),
  KEY `com_hilos_participantes_id_hilo_tipo_id_legajo_index` (`id_hilo`,`tipo`,`id_legajo`),
  CONSTRAINT `com_hilos_participantes_id_hilo_foreign` FOREIGN KEY (`id_hilo`) REFERENCES `com_hilos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_hilo` bigint(20) unsigned NOT NULL,
  `id_mensaje_padre` bigint(20) unsigned DEFAULT NULL,
  `tipo_remitente` enum('profesor','familia') NOT NULL,
  `id_profesor` int(10) unsigned DEFAULT NULL,
  `id_legajo` int(10) unsigned DEFAULT NULL,
  `rol_remitente` varchar(30) DEFAULT NULL,
  `vinculo_familiar` enum('madre','padre','tutor','resp_admin','otro') DEFAULT NULL,
  `nombre_remitente_snapshot` varchar(150) DEFAULT NULL,
  `dni_remitente_snapshot` varchar(20) DEFAULT NULL,
  `contenido` text NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `com_mensajes_id_hilo_created_at_index` (`id_hilo`,`created_at`),
  KEY `com_mensajes_tipo_remitente_id_profesor_index` (`tipo_remitente`,`id_profesor`),
  KEY `com_mensajes_tipo_remitente_id_legajo_index` (`tipo_remitente`,`id_legajo`),
  CONSTRAINT `com_mensajes_id_hilo_foreign` FOREIGN KEY (`id_hilo`) REFERENCES `com_hilos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_mensajes_destinatarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_mensaje` bigint(20) unsigned NOT NULL,
  `id_hilo` bigint(20) unsigned NOT NULL,
  `tipo_destinatario` enum('profesor','familia') NOT NULL,
  `id_profesor` int(10) unsigned DEFAULT NULL,
  `id_legajo` int(10) unsigned DEFAULT NULL,
  `rol_destinatario` varchar(30) DEFAULT NULL,
  `nombre_snapshot` varchar(150) DEFAULT NULL,
  `dni_snapshot` varchar(20) DEFAULT NULL,
  `leido_at` timestamp NULL DEFAULT NULL,
  `respondido_at` timestamp NULL DEFAULT NULL,
  `id_mensaje_respuesta` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `com_mensajes_destinatarios_id_mensaje_foreign` (`id_mensaje`),
  KEY `idx_dest_legajo_leido` (`tipo_destinatario`,`id_legajo`,`leido_at`),
  KEY `idx_dest_prof_leido` (`tipo_destinatario`,`id_profesor`,`leido_at`),
  KEY `idx_cmd_hilo_tipo_legajo` (`id_hilo`,`tipo_destinatario`,`id_legajo`),
  KEY `idx_cmd_hilo_tipo_prof` (`id_hilo`,`tipo_destinatario`,`id_profesor`),
  CONSTRAINT `com_mensajes_destinatarios_id_hilo_foreign` FOREIGN KEY (`id_hilo`) REFERENCES `com_hilos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `com_mensajes_destinatarios_id_mensaje_foreign` FOREIGN KEY (`id_mensaje`) REFERENCES `com_mensajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_mensajes_envios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_mensaje_destinatario` bigint(20) unsigned NOT NULL,
  `medio` enum('push','email','whatsapp') NOT NULL,
  `estado` enum('pendiente','enviado','fallido','no_aplicable') NOT NULL DEFAULT 'pendiente',
  `motivo` varchar(255) DEFAULT NULL,
  `proveedor_msgid` text DEFAULT NULL,
  `enviado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `com_mensajes_envios_id_mensaje_destinatario_medio_index` (`id_mensaje_destinatario`,`medio`),
  CONSTRAINT `com_mensajes_envios_id_mensaje_destinatario_foreign` FOREIGN KEY (`id_mensaje_destinatario`) REFERENCES `com_mensajes_destinatarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `com_preferencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_usuario` enum('familia','profesor') NOT NULL,
  `id_legajo` int(10) unsigned DEFAULT NULL,
  `id_profesor` int(10) unsigned DEFAULT NULL,
  `vinculo_contacto` enum('madre','padre','tutor','resp_admin','otro') DEFAULT NULL,
  `vinculos_contacto` json DEFAULT NULL,
  `push` tinyint(1) NOT NULL DEFAULT 1,
  `email` tinyint(1) NOT NULL DEFAULT 1,
  `whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pref_legajo` (`id_legajo`),
  UNIQUE KEY `uq_pref_profesor` (`id_profesor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `com_canales` (`rol_emisor`,`rol_receptor`,`puede_iniciar`,`puede_responder`,`medios_permitidos`,`activo`,`created_at`,`updated_at`) VALUES
('familia','preceptor',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('familia','profesor',0,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('familia','directivo',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('profesor','familia',1,0,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('preceptor','familia',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('directivo','familia',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('preceptor','profesor',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('profesor','profesor',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('profesor','preceptor',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('profesor','directivo',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('preceptor','preceptor',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('preceptor','directivo',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('directivo','profesor',1,1,'["push","email"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('directivo','preceptor',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP),
('directivo','directivo',1,1,'["push","email","whatsapp"]',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
  `puede_iniciar` = VALUES(`puede_iniciar`),
  `puede_responder` = VALUES(`puede_responder`),
  `medios_permitidos` = VALUES(`medios_permitidos`),
  `activo` = VALUES(`activo`),
  `updated_at` = CURRENT_TIMESTAMP;

-- =============================================================================
-- 4. Push, boletín, legajos (dnitut)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `endpoint_hash` varchar(64) NOT NULL,
  `endpoint` text NOT NULL,
  `auth_key` varchar(255) NOT NULL,
  `p256dh_key` varchar(255) NOT NULL,
  `user_key` varchar(50) NOT NULL DEFAULT '' COMMENT 'legajo id (string para compatibilidad)',
  `device_type` varchar(20) DEFAULT NULL COMMENT 'mobile|tablet|desktop',
  `user_agent` varchar(512) DEFAULT NULL,
  `device_label` varchar(100) DEFAULT NULL,
  `client_hints` varchar(512) DEFAULT NULL COMMENT 'JSON string',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`endpoint_hash`,`user_key`),
  KEY `push_subscriptions_user_key_index` (`user_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `itemsboletin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden` smallint unsigned NOT NULL DEFAULT 0,
  `etiqueta` varchar(160) NOT NULL,
  `fuente` varchar(32) NOT NULL,
  `condicion_where` varchar(500) NOT NULL,
  `idTerlec` int unsigned NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `itemsboletin_activo_orden_index` (`activo`, `orden`),
  KEY `itemsboletin_idterlec_activo_index` (`idTerlec`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `itemsboletin` (`id`, `orden`, `etiqueta`, `fuente`, `condicion_where`, `idTerlec`, `activo`) VALUES
(1, 1, 'Inasistencias Justificadas', 'inasistencias', 'tipo <> 5 and just = ''J''', NULL, 1),
(2, 2, 'Inasistencias Injustificadas', 'inasistencias', 'tipo <> 5 and just = ''I''', NULL, 1),
(3, 3, 'Total de Inasistencias', 'inasistencias', 'tipo <> 5 and (just = ''J'' or just = ''I'')', NULL, 1),
(4, 4, 'Inasistencias a Educación Física', 'inasistencias', 'tipo = 5', NULL, 1),
(5, 5, 'Apercibimientos Orales', 'sanciones', 'idTipoSancion = 2', NULL, 1),
(6, 6, 'Apercibimientos Escritos', 'sanciones', 'idTipoSancion = 3', NULL, 1),
(7, 7, 'Amonestaciones', 'sanciones', 'idTipoSancion = 1 and publicada = 1', NULL, 1),
(8, 8, 'Suspensiones', 'sanciones', 'idTipoSancion = 6', NULL, 1)
ON DUPLICATE KEY UPDATE
  `orden` = VALUES(`orden`),
  `etiqueta` = VALUES(`etiqueta`),
  `fuente` = VALUES(`fuente`),
  `condicion_where` = VALUES(`condicion_where`),
  `idTerlec` = VALUES(`idTerlec`),
  `activo` = VALUES(`activo`);

-- Solo si existe la columna dnitut (tabla legajos legacy)
SET @has_dnitut := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'legajos' AND COLUMN_NAME = 'dnitut'
);
SET @ddl_legajos := IF(@has_dnitut > 0,
  'ALTER TABLE `legajos` CHANGE COLUMN `dnitut` `dnitut` VARCHAR(10) NULL DEFAULT '''' COLLATE utf8mb3_unicode_ci AFTER `nombretut`',
  'SELECT 1'
);
PREPARE stmt_leg FROM @ddl_legajos;
EXECUTE stmt_leg;
DEALLOCATE PREPARE stmt_leg;

-- =============================================================================
-- 5. Horarios
-- =============================================================================
CREATE TABLE IF NOT EXISTS `turnos_clase` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `orden` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `turnos_clase` (`id`, `codigo`, `nombre`, `orden`) VALUES
  (1, 'manana', 'Mañana', 1),
  (2, 'tarde', 'Tarde', 2),
  (3, 'noche', 'Noche', 3)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `orden` = VALUES(`orden`);

CREATE TABLE IF NOT EXISTS `horarios_config` (
  `idNivel` smallint unsigned NOT NULL,
  `turnos_activos` varchar(20) NOT NULL DEFAULT '1',
  `dias_activos` varchar(20) NOT NULL DEFAULT '1,2,3,4,5',
  PRIMARY KEY (`idNivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CALL sp_add_column_if_missing('reloj', 'idTurnoClase', 'tinyint unsigned NULL DEFAULT 1 AFTER `idNivel`');

-- =============================================================================
-- 6. Legajo docentes
-- =============================================================================
CREATE TABLE IF NOT EXISTS `solapas_legajo_profesor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `slug` varchar(30) NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `solapas_legajo_profesor_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solapas_legajo_profesor` (`nombre`, `slug`, `orden`) VALUES ('DOCENTE', 'docente', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `orden` = VALUES(`orden`);

CREATE TABLE IF NOT EXISTS `campos_profesores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `columna` varchar(80) NOT NULL,
  `etiqueta` varchar(100) DEFAULT NULL,
  `visible_listado` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int unsigned NOT NULL DEFAULT 0,
  `solapa_legajo_profesor_id` bigint unsigned DEFAULT NULL,
  `orden_en_solapa` smallint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `campos_profesores_solapa_legajo_profesor_id_foreign` (`solapa_legajo_profesor_id`),
  CONSTRAINT `campos_profesores_solapa_legajo_profesor_id_foreign`
    FOREIGN KEY (`solapa_legajo_profesor_id`) REFERENCES `solapas_legajo_profesor` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 7. Permisos IA (catálogo alineado con migraciones Laravel)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `permisos_ia` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `orden` int(4) NOT NULL,
  `tema` varchar(50) NOT NULL DEFAULT '',
  `descripcion` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permisos_ia_orden_unique` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CALL sp_add_column_if_missing('profesores', 'permisos_ia', 'varchar(50) NULL DEFAULT NULL AFTER `permisos`');

INSERT INTO `permisos_ia` (`id`, `orden`, `tema`, `descripcion`) VALUES
(1, 0, 'ADMINISTRACIÓN', 'Administrar permisos del portal de gestión (sistema nuevo).'),
(2, 1, 'PARAMETRIZACIÓN', 'Términos lectivos, niveles, cursos, planes, materias del año, legajos de docentes y parametrización relacionada.'),
(3, 2, 'LEGAJOS ESTUDIANTES', 'Crear, editar y eliminar legajos de estudiantes; gestionar matrículas.'),
(4, 3, 'COMUNICACIONES', 'Ver la bandeja de comunicados y los hilos de conversación.'),
(5, 4, 'COMUNICACIONES', 'Iniciar nuevos comunicados hacia familias.'),
(6, 5, 'COMUNICACIONES - CONFIG', 'Administrar la configuración de canales (quién puede comunicarse con quién y por qué medios).'),
(7, 6, 'COMUNICACIONES', 'Borrar mensajes propios en un hilo.'),
(8, 7, 'COMUNICACIONES', 'Borrar mensajes de otros participantes en un hilo.'),
(9, 8, 'COMUNICACIONES', 'Acceder a la bandeja de revisión de comunicados.'),
(10, 9, 'CALIFICACIONES SECUNDARIO', 'Importar calificaciones desde CIDI/GE y carga manual de calificaciones (secundario).'),
(11, 10, 'CALIFICACIONES SECUNDARIO', 'Carga de coloquios Dic / Feb (secundario).'),
(12, 11, 'LEGAJOS DOCENTES', 'Crear, editar y eliminar legajos de docentes; asignar y quitar docentes en materias (ppc).'),
(13, 12, 'EXÁMENES', 'Módulo de exámenes: materias adeudadas, gestión, listados y borrado de inscripciones.'),
(14, 13, 'HORARIOS', 'Configuración de horarios (turnos, días, reloj) y carga de horas cátedra por docente.'),
(15, 14, 'CONFIGURACIÓN', 'Menú Configuración: términos lectivos, niveles, legajos, parámetros, planes, cursos, materias y notificaciones push.'),
(19, 17, 'CERTIFICADOS', 'Certificado escolar de alumno/a regular: listado de matriculados del año en curso y emisión de PDF.'),
(20, 18, 'CERTIFICADOS', 'Constancia de certificado de estudios en trámite: listado de matriculados y emisión de PDF.'),
(21, 19, 'CERTIFICADOS', 'Constancia de documentos: listado de matriculados y emisión de PDF.'),
(22, 20, 'CERTIFICADOS', 'Certificado de asistencia del profesor: listado de personal del legajo y emisión de PDF.'),
(23, 21, 'CERTIFICADOS', 'Pase parcial: listado de legajos de nivel medio, solicitud y emisión de PDF.'),
(24, 22, 'CERTIFICADOS', 'Solicitud de pase: listado de legajos de nivel medio, datos en paseprovisorio y emisión de PDF.'),
(25, 23, 'INASISTENCIAS DOCENTES', 'Gestión de inasistencias docentes: cargos, registros, informes por bimestre y PDF.'),
(26, 24, 'ASISTENCIA ESTUDIANTES', 'Importar inasistencias de estudiantes desde CSV CIDI/GE (InasistenciasDetalle).')
ON DUPLICATE KEY UPDATE
  `orden` = VALUES(`orden`),
  `tema` = VALUES(`tema`),
  `descripcion` = VALUES(`descripcion`);

-- =============================================================================
-- 8. Inasistencias estudiantes — texto CIDI en catálogo de tipos
-- =============================================================================
CALL sp_add_column_if_missing('inasistencias_valores', 'texto_cidi', 'varchar(120) NULL DEFAULT NULL AFTER `concepto`');

-- =============================================================================
-- 9. Inasistencias docentes
-- =============================================================================
CREATE TABLE IF NOT EXISTS `cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cargo` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cargosxprofesor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idCargos` int unsigned NOT NULL,
  `idProfesores` int unsigned NOT NULL,
  `dniProfesor` int unsigned NOT NULL DEFAULT 0,
  `idNiveles` int unsigned NOT NULL DEFAULT 0,
  `cant` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CALL sp_add_column_if_missing('cargosxprofesor', 'dniProfesor', 'INT UNSIGNED NOT NULL DEFAULT 0');
CALL sp_add_column_if_missing('cargosxprofesor', 'idNiveles', 'INT UNSIGNED NOT NULL DEFAULT 0');
CALL sp_add_column_if_missing('cargosxprofesor', 'cant', 'INT UNSIGNED NOT NULL DEFAULT 0');

-- Sin AFTER: evita error si la columna ancla no existe aún (orden físico irrelevante)
CALL sp_add_column_if_missing('inasdocentes', 'dniProfesor', 'INT UNSIGNED NOT NULL DEFAULT 0');
CALL sp_add_column_if_missing('inasdocentes', 'idNivel', 'INT UNSIGNED NOT NULL DEFAULT 0');
CALL sp_add_column_if_missing('inasdocentes', 'idCargosXProfesor', 'INT UNSIGNED NOT NULL DEFAULT 0');

CREATE TABLE IF NOT EXISTS `inasdocentes_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idInasDocentes` int unsigned NOT NULL,
  `idMaterias` int unsigned NOT NULL,
  `idCursos` int unsigned NOT NULL,
  `cantidad` decimal(5,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `inasdocentes_detalle_idinasdocentes_index` (`idInasDocentes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

DROP PROCEDURE IF EXISTS sp_add_column_if_missing;

-- =============================================================================
-- Fin. Puede ejecutarse varias veces sin error por tablas/columnas existentes.
-- =============================================================================
