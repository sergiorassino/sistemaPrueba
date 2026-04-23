-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         5.7.28-log - MySQL Community Server (GPL)
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla ia_demo.actasreincorporaciones
CREATE TABLE IF NOT EXISTS `actasreincorporaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `nroReinco` int(1) NOT NULL DEFAULT '0',
  `fecha` date NOT NULL,
  `cantInasist` decimal(5,2) NOT NULL DEFAULT '0.00',
  `preceptor` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `padresNombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `padresDni` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.analiticodatos
CREATE TABLE IF NOT EXISTS `analiticodatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL DEFAULT '0',
  `analCohorte` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `analObservaciones` text COLLATE utf8_spanish_ci,
  `analParaCompletar` text COLLATE utf8_spanish_ci,
  `analValidez` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `serie` varchar(6) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `numero` varchar(6) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `analLibroFolio` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `analFechaEmision` date DEFAULT NULL,
  `analParaPre` varchar(200) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_variosalumnos_legajos` (`idLegajos`)
) ENGINE=InnoDB AUTO_INCREMENT=384 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.apf
CREATE TABLE IF NOT EXISTS `apf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idFamilias` int(11) DEFAULT NULL,
  `idLegajos` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1847 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='alumnos x familia';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.aprendizajes
CREATE TABLE IF NOT EXISTS `aprendizajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idContenidos` int(11) NOT NULL,
  `aprendizaje` text COLLATE utf8_spanish_ci NOT NULL,
  `etapa` int(1) NOT NULL DEFAULT '0',
  `ord` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_aprendizajes_contenidos` (`idContenidos`),
  CONSTRAINT `FK_aprendizajes_contenidos` FOREIGN KEY (`idContenidos`) REFERENCES `contenidos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.aspicursos_fps
CREATE TABLE IF NOT EXISTS `aspicursos_fps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curso` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `idnivel` int(11) DEFAULT NULL,
  `mostrar` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.aspiniveles
CREATE TABLE IF NOT EXISTS `aspiniveles` (
  `idNivel` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(50) DEFAULT NULL,
  KEY `idNivel` (`idNivel`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.aspirantes_ento
CREATE TABLE IF NOT EXISTS `aspirantes_ento` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `idNivel` int(2) DEFAULT NULL,
  `idAspiTerlec` int(2) DEFAULT NULL,
  `insti` varchar(255) DEFAULT NULL,
  `titulo3` varchar(300) DEFAULT '',
  `fechdesde` datetime DEFAULT NULL,
  `fechhasta` datetime DEFAULT NULL,
  `cue` varchar(20) DEFAULT NULL,
  `dirección` varchar(100) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `replegal` varchar(100) DEFAULT NULL,
  `tipoinscri` varchar(20) DEFAULT NULL,
  `tipoleyenda` int(1) DEFAULT NULL,
  `ano` int(4) DEFAULT NULL,
  `mailNivel` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_aspiento_niveles` (`idNivel`),
  KEY `FK_aspiento_terlec_2` (`idAspiTerlec`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.aspirantes_fps
CREATE TABLE IF NOT EXISTS `aspirantes_fps` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apellido` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `nombre` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `dni` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fechnaci` date NOT NULL,
  `lugarnac` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sexo` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `domicilio` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `barrio` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `madpad` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombremad` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `telemad` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `emailmad` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombrepad` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `telepad` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `emailpad` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombretut` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `teletut` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailtut` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `escori` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `destino` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `obs` longtext CHARACTER SET latin1,
  `fechhora` datetime DEFAULT NULL,
  `identif` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `idnivel` int(11) DEFAULT NULL,
  `hijoherm` int(1) DEFAULT NULL,
  `dathijo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datherm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `needes` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `needes_detalle` longtext COLLATE utf8_unicode_ci,
  `vivecon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parroquia` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `motivo` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `motivo_detalle` longtext COLLATE utf8_unicode_ci,
  `acopro` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acopro_detalle` longtext COLLATE utf8_unicode_ci,
  `idAspiTerlec` int(10) NOT NULL,
  `tipoinscri` int(1) NOT NULL,
  `hermanosAspirantes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=279 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ayuda
CREATE TABLE IF NOT EXISTS `ayuda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `textoAyuda` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.bancos
CREATE TABLE IF NOT EXISTS `bancos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCuentasBancos` int(11) DEFAULT NULL,
  `idTipoMovimiento` int(11) DEFAULT NULL,
  `fechaCheque` date DEFAULT NULL,
  `numCheque` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `idConcepto` int(11) DEFAULT NULL,
  `idProveedores` int(11) DEFAULT NULL,
  `fechhora` datetime DEFAULT NULL,
  `comprobante` varchar(20) COLLATE utf8_spanish_ci DEFAULT NULL,
  `monto` float(15,2) DEFAULT NULL,
  `saldo` float(15,2) DEFAULT NULL,
  `obs` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.caja
CREATE TABLE IF NOT EXISTS `caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTipoMovimiento` int(11) DEFAULT NULL,
  `idConcepto` int(11) DEFAULT NULL,
  `idProveedores` int(11) DEFAULT NULL,
  `fechhora` datetime DEFAULT NULL,
  `comprobante` varchar(20) COLLATE utf8_spanish_ci DEFAULT NULL,
  `monto` float(15,2) DEFAULT NULL,
  `saldo` float(15,2) DEFAULT NULL,
  `obs` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`),
  KEY `FK_caja_tipomovimiento` (`idTipoMovimiento`),
  KEY `FK_caja_conceptos` (`idConcepto`),
  CONSTRAINT `FK_caja_conceptos` FOREIGN KEY (`idConcepto`) REFERENCES `conceptos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_caja_tipomovimiento` FOREIGN KEY (`idTipoMovimiento`) REFERENCES `tipomovimiento` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.calificaciones
CREATE TABLE IF NOT EXISTS `calificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) DEFAULT NULL,
  `idMatricula` int(11) DEFAULT NULL,
  `ord` int(4) DEFAULT NULL,
  `idTerlec` int(4) DEFAULT NULL,
  `idCursos` int(4) DEFAULT NULL,
  `idMaterias` int(11) DEFAULT NULL,
  `idMatPlan` int(11) DEFAULT NULL,
  `ic01` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic02` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic03` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic04` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic05` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic06` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic07` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic08` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic09` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic10` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic11` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic12` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic13` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic14` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic15` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic16` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic17` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic18` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic19` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic20` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic21` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic22` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic23` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic24` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic25` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic26` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic27` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic28` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic29` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic30` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic31` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic32` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic33` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic34` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic35` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic36` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic37` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic38` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic39` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `ic40` varchar(15) CHARACTER SET latin1 DEFAULT '',
  `obs01` text CHARACTER SET latin1,
  `obs02` text,
  `tm1` varchar(15) DEFAULT '',
  `tm2` varchar(15) DEFAULT '',
  `tm3` varchar(15) DEFAULT '',
  `tm4` varchar(15) DEFAULT '',
  `tm5` varchar(15) DEFAULT '',
  `tm6` varchar(15) DEFAULT '',
  `tmNota` varchar(15) DEFAULT '',
  `dic` varchar(10) DEFAULT '',
  `feb` varchar(10) DEFAULT '',
  `inscri` int(1) NOT NULL DEFAULT '0',
  `condAdeuda` varchar(2) CHARACTER SET latin1 DEFAULT NULL,
  `apro` int(1) DEFAULT NULL,
  `calif` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
  `mes` int(2) DEFAULT NULL,
  `ano` int(4) DEFAULT NULL,
  `cond` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `escuapro` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `libro` varchar(10) DEFAULT NULL,
  `folio` varchar(10) DEFAULT NULL,
  `fechApro` date DEFAULT NULL,
  `libroDic` varchar(10) DEFAULT NULL,
  `folioDic` varchar(10) DEFAULT NULL,
  `fechAproDic` date DEFAULT NULL,
  `libroFeb` varchar(10) DEFAULT NULL,
  `folioFeb` varchar(10) DEFAULT NULL,
  `fechAproFeb` date DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_calificaciones_materias` (`idMaterias`),
  KEY `FK_calificaciones_terlec` (`idTerlec`),
  KEY `FK_calificaciones_cursos` (`idCursos`),
  KEY `FK_calificaciones_matplan` (`idMatPlan`),
  KEY `FK_calificaciones_legajos` (`idLegajos`),
  KEY `FK_calificaciones_matricula` (`idMatricula`),
  KEY `condAdeuda` (`condAdeuda`),
  CONSTRAINT `FK_calificaciones_cursos` FOREIGN KEY (`idCursos`) REFERENCES `cursos` (`Id`),
  CONSTRAINT `FK_calificaciones_legajos` FOREIGN KEY (`idLegajos`) REFERENCES `legajos` (`id`),
  CONSTRAINT `FK_calificaciones_materias` FOREIGN KEY (`idMaterias`) REFERENCES `materias` (`id`),
  CONSTRAINT `FK_calificaciones_matplan` FOREIGN KEY (`idMatPlan`) REFERENCES `matplan` (`id`),
  CONSTRAINT `FK_calificaciones_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_calificaciones_terlec` FOREIGN KEY (`idTerlec`) REFERENCES `terlec` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=118297 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certalureg
CREATE TABLE IF NOT EXISTS `certalureg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL DEFAULT '0',
  `iniFin` int(1) DEFAULT '0',
  `fechIniFin` date DEFAULT NULL,
  `prePor` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `prePorDni` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `preAnte` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fechaEmision` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=621 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certasistprof
CREATE TABLE IF NOT EXISTS `certasistprof` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idProfesores` int(11) NOT NULL DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `texto` varchar(200) COLLATE utf8_spanish_ci DEFAULT '',
  `parapre` varchar(300) COLLATE utf8_spanish_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certestutram
CREATE TABLE IF NOT EXISTS `certestutram` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL DEFAULT '0',
  `mateAdeud` text COLLATE utf8_spanish_ci,
  `idiomaCursado` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `preAnte` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `fechaEmision` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certificacion
CREATE TABLE IF NOT EXISTS `certificacion` (
  `idcertificacion` int(11) NOT NULL AUTO_INCREMENT,
  `idpersonal` int(11) NOT NULL,
  `cargo` varchar(200) DEFAULT NULL,
  `titularSuplente` varchar(15) DEFAULT NULL,
  `nroResolucion` varchar(25) DEFAULT NULL,
  `fechaAlta` date DEFAULT NULL,
  `FechaBaja` date DEFAULT '0000-00-00',
  `hsCatedra` int(11) DEFAULT NULL,
  PRIMARY KEY (`idcertificacion`),
  KEY `idpersonal_idx` (`idpersonal`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certificadojardin
CREATE TABLE IF NOT EXISTS `certificadojardin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serie` int(4) NOT NULL DEFAULT '0',
  `mesApro` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `anoApro` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `diaEmision` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `mesEmision` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `anoEmision` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.certificadosextogrado
CREATE TABLE IF NOT EXISTS `certificadosextogrado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serie` int(4) NOT NULL DEFAULT '0',
  `mesApro` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `anoApro` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `diaEmision` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `mesEmision` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `anoEmision` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `ppi` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.comprobanteafip
CREATE TABLE IF NOT EXISTS `comprobanteafip` (
  `idComprobanteAfip` int(11) NOT NULL AUTO_INCREMENT,
  `nombreInstitucion` varchar(100) DEFAULT NULL,
  `razonSocial` varchar(100) DEFAULT NULL,
  `cuitInstitucion` varchar(50) DEFAULT NULL,
  `domicilioComercial` varchar(100) DEFAULT NULL,
  `condicionIvaInstitucion` varchar(50) DEFAULT NULL,
  `puntoVenta` varchar(10) DEFAULT NULL,
  `ingresosBrutos` varchar(10) DEFAULT NULL,
  `fechaInicioActividades` varchar(15) DEFAULT NULL,
  `nombreAlumno` varchar(200) DEFAULT NULL,
  `dni` varchar(10) DEFAULT NULL,
  `nombreResp` varchar(200) DEFAULT NULL,
  `dniResp` varchar(10) DEFAULT NULL,
  `domicilioAlumno` varchar(100) DEFAULT NULL,
  `condicionIvaAlumno` varchar(50) DEFAULT NULL,
  `condicionVenta` varchar(50) DEFAULT NULL,
  `fechaDesde` varchar(15) DEFAULT NULL,
  `fechaHasta` varchar(15) DEFAULT NULL,
  `fechaEmision` varchar(25) DEFAULT NULL,
  `fechaVencimiento` varchar(15) DEFAULT NULL,
  `tipoComprobante` varchar(4) DEFAULT NULL,
  `codigoBarras` varchar(100) DEFAULT NULL,
  `nroRecibo` varchar(50) DEFAULT NULL,
  `cae` varchar(100) DEFAULT NULL,
  `vtoCae` varchar(15) DEFAULT NULL,
  `importePagado` varchar(15) DEFAULT NULL,
  `interesPagado` varchar(15) DEFAULT '0',
  `idCbteAsoc` int(11) DEFAULT NULL,
  `concepto` text,
  `subConceptos` text,
  `importeSubConceptos` text,
  `saldoRestante` text,
  `idCuotasPagos` int(11) DEFAULT NULL,
  PRIMARY KEY (`idComprobanteAfip`),
  KEY `cuotasPagos` (`idCuotasPagos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.comunicdocentes
CREATE TABLE IF NOT EXISTS `comunicdocentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idNivel` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `descripcion` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `texto` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.conceptos
CREATE TABLE IF NOT EXISTS `conceptos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipoConcepto` int(1) NOT NULL,
  `n1` int(3) NOT NULL,
  `n2` int(3) NOT NULL,
  `n3` int(3) NOT NULL,
  `n4` int(3) NOT NULL,
  `concepto` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.condadeudada
CREATE TABLE IF NOT EXISTS `condadeudada` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `condAdeuda` varchar(30) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `abrev` varchar(2) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.condaprobacion
CREATE TABLE IF NOT EXISTS `condaprobacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCondApro` int(11) NOT NULL DEFAULT '0',
  `condApro` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.condiciones
CREATE TABLE IF NOT EXISTS `condiciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(2) DEFAULT NULL,
  `condicion` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `proteg` int(2) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.constdocu
CREATE TABLE IF NOT EXISTS `constdocu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL,
  `certifde` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `otorpor` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `fechotor` date DEFAULT NULL,
  `parnacop` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `parapre` varchar(300) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `fechemis` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.contenidos
CREATE TABLE IF NOT EXISTS `contenidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMateria` int(11) NOT NULL,
  `contenido` text COLLATE utf8_spanish_ci NOT NULL,
  `ord` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cubririnasdocentes
CREATE TABLE IF NOT EXISTS `cubririnasdocentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idInasdocentes` int(11) NOT NULL DEFAULT '0',
  `idProfesores` int(11) NOT NULL DEFAULT '0',
  `cantidad` int(2) NOT NULL DEFAULT '0',
  `pagacon` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotas
CREATE TABLE IF NOT EXISTS `cuotas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCuotasmeses` int(2) NOT NULL,
  `idCuotastipo` int(2) NOT NULL,
  `idTerlec` int(11) NOT NULL,
  `orden` int(5) NOT NULL DEFAULT '0',
  `nombre` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `venc1` date DEFAULT NULL,
  `venc2` date DEFAULT NULL,
  `venc3` date DEFAULT NULL,
  `sinConBeca` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_cuotas_cuotasmeses` (`idCuotasmeses`),
  KEY `FK_cuotas_cuotastipo` (`idCuotastipo`),
  CONSTRAINT `FK_cuotas_cuotasmeses` FOREIGN KEY (`idCuotasmeses`) REFERENCES `cuotasmeses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotas_cuotastipo` FOREIGN KEY (`idCuotastipo`) REFERENCES `cuotastipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotasbecas
CREATE TABLE IF NOT EXISTS `cuotasbecas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombreBeca` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `porcentaje` int(3) NOT NULL DEFAULT '0',
  `obsBeca` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotasgeneradas
CREATE TABLE IF NOT EXISTS `cuotasgeneradas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTerlec` int(11) NOT NULL,
  `idLegajos` int(11) NOT NULL,
  `idCursos` int(11) NOT NULL,
  `idMatricula` int(11) NOT NULL,
  `idCuotas` int(11) NOT NULL,
  `idCuotastipo` int(11) NOT NULL,
  `idCuotasmeses` int(11) NOT NULL,
  `idCuotasbecas` int(11) NOT NULL,
  `venc1` date DEFAULT NULL,
  `venc2` date DEFAULT NULL,
  `venc3` date DEFAULT NULL,
  `importe` decimal(20,2) DEFAULT '0.00',
  `bonificacion` decimal(20,2) DEFAULT '0.00',
  `interes` decimal(20,2) DEFAULT '0.00',
  `pagado` decimal(20,2) DEFAULT '0.00',
  `faltapa` decimal(20,2) DEFAULT '0.00',
  `fechaPago` datetime DEFAULT NULL,
  `obs` text COLLATE utf8_spanish_ci,
  `ultUpload` int(4) DEFAULT '0',
  `nueVenc` date DEFAULT NULL,
  `nroComp` int(10) DEFAULT '0',
  `difePlan` int(1) DEFAULT '0',
  `fechaDifePlan` date DEFAULT '0000-00-00',
  `avisoPago` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idTerlec` (`idTerlec`),
  KEY `idLegajos` (`idLegajos`),
  KEY `idCursos` (`idCursos`),
  KEY `idMatricula` (`idMatricula`),
  KEY `idCuotas` (`idCuotas`),
  KEY `FK_cuotasgeneradas_cuotastipo` (`idCuotastipo`),
  KEY `FK_cuotasgeneradas_cuotasmeses` (`idCuotasmeses`),
  KEY `FK_cuotasgeneradas_cuotasbecas` (`idCuotasbecas`),
  CONSTRAINT `FK_cuotasgeneradas_cuotas` FOREIGN KEY (`idCuotas`) REFERENCES `cuotas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotasgeneradas_cuotasbecas` FOREIGN KEY (`idCuotasbecas`) REFERENCES `cuotasbecas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotasgeneradas_cuotasmeses` FOREIGN KEY (`idCuotasmeses`) REFERENCES `cuotasmeses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotasgeneradas_cuotastipo` FOREIGN KEY (`idCuotastipo`) REFERENCES `cuotastipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotasgeneradas_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=45695 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotasimportes
CREATE TABLE IF NOT EXISTS `cuotasimportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCuotas` int(11) NOT NULL,
  `idCursos` int(11) NOT NULL,
  `importe` decimal(20,2) DEFAULT '0.00',
  `signo1v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `valor1v` decimal(20,2) DEFAULT '0.00',
  `porcan1v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `signo2v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `valor2v` decimal(20,2) DEFAULT '0.00',
  `porcan2v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `signo3v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `valor3v` decimal(20,2) DEFAULT '0.00',
  `porcan3v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `signo4v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  `valor4v` decimal(20,2) DEFAULT '0.00',
  `porcan4v` varchar(1) COLLATE utf8_spanish_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idCuotas` (`idCuotas`),
  KEY `idCursos` (`idCursos`),
  CONSTRAINT `FK_cuotasimportes_cuotas` FOREIGN KEY (`idCuotas`) REFERENCES `cuotas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotasimportes_cursos` FOREIGN KEY (`idCursos`) REFERENCES `cursos` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4089 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotasmeses
CREATE TABLE IF NOT EXISTS `cuotasmeses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotaspagos
CREATE TABLE IF NOT EXISTS `cuotaspagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCuotasGeneradas` int(11) NOT NULL,
  `idCuotastipopago` int(11) NOT NULL,
  `fechhora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `importe` decimal(20,2) DEFAULT NULL,
  `bonificacion` decimal(20,2) DEFAULT NULL,
  `interes` decimal(20,2) DEFAULT NULL,
  `nombreArchivo` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `cadenaPago` varchar(480) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCuotasGeneradas` (`idCuotasGeneradas`),
  KEY `FK_cuotaspagos_cuotastipopago` (`idCuotastipopago`),
  CONSTRAINT `FK_cuotaspagos_cuotasgeneradas` FOREIGN KEY (`idCuotasGeneradas`) REFERENCES `cuotasgeneradas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cuotaspagos_cuotastipopago` FOREIGN KEY (`idCuotastipopago`) REFERENCES `cuotastipopago` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=36167 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotastipo
CREATE TABLE IF NOT EXISTS `cuotastipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(10) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cuotastipopago
CREATE TABLE IF NOT EXISTS `cuotastipopago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipoPago` varchar(10) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `abrev` varchar(3) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.curplan
CREATE TABLE IF NOT EXISTS `curplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlan` int(11) NOT NULL,
  `curPlanCurso` varchar(30) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_curplan_planes` (`idPlan`),
  CONSTRAINT `FK_curplan_planes` FOREIGN KEY (`idPlan`) REFERENCES `planes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.cursos
CREATE TABLE IF NOT EXISTS `cursos` (
  `Id` int(10) NOT NULL AUTO_INCREMENT,
  `orden` int(3) DEFAULT NULL,
  `idCurPlan` int(11) DEFAULT NULL,
  `idTerlec` int(11) DEFAULT NULL,
  `idNivel` int(11) DEFAULT NULL,
  `cursec` varchar(30) DEFAULT NULL,
  `c` varchar(1) DEFAULT NULL,
  `s` varchar(1) DEFAULT NULL,
  `turno` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_cursos_curplan` (`idCurPlan`),
  KEY `FK_cursos_terlec` (`idTerlec`),
  KEY `FK_cursos_niveles` (`idNivel`),
  CONSTRAINT `FK_cursos_curplan` FOREIGN KEY (`idCurPlan`) REFERENCES `curplan` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cursos_niveles` FOREIGN KEY (`idNivel`) REFERENCES `niveles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_cursos_terlec` FOREIGN KEY (`idTerlec`) REFERENCES `terlec` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=426 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.datosvarios
CREATE TABLE IF NOT EXISTS `datosvarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ultimoComprobante` int(11) NOT NULL DEFAULT '0',
  `textoInicNotDeuda` text COLLATE utf8_spanish_ci NOT NULL,
  `textoFinalNotDeuda` text COLLATE utf8_spanish_ci NOT NULL,
  `textoFinalNotDeudaBec` text COLLATE utf8_spanish_ci NOT NULL,
  `ultimaSoliBeca` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.emails
CREATE TABLE IF NOT EXISTS `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proAlu` int(2) DEFAULT NULL,
  `idNiveles` int(3) DEFAULT NULL,
  `destinatarios` text COLLATE utf8_spanish_ci,
  `fecha` date DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `text` text COLLATE utf8_spanish_ci,
  `adjuntos` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `obs` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.emails_enviados_morosos
CREATE TABLE IF NOT EXISTS `emails_enviados_morosos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `idFamilia` int(11) NOT NULL,
  `emailFamilia` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `texto` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2697 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.enletras
CREATE TABLE IF NOT EXISTS `enletras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `enLetras` varchar(60) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ento
CREATE TABLE IF NOT EXISTS `ento` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `idNivel` int(2) DEFAULT NULL,
  `idTerlecVerNotas` int(2) DEFAULT NULL,
  `idTerlecVerNotas2` int(2) DEFAULT NULL,
  `insti` varchar(255) DEFAULT NULL,
  `cue` varchar(20) DEFAULT NULL,
  `ee` varchar(20) DEFAULT NULL,
  `cuit` varchar(11) DEFAULT NULL,
  `categoria` varchar(20) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `replegal` varchar(100) DEFAULT NULL,
  `idAspiTerlec` int(2) DEFAULT NULL,
  `platOff` int(1) DEFAULT NULL,
  `offMensaje` varchar(300) DEFAULT NULL,
  `cargaNotasOff` int(1) DEFAULT NULL,
  `notasOffMensaje` varchar(300) DEFAULT NULL,
  `verNotasOff` int(1) DEFAULT NULL,
  `verOffMensaje` varchar(300) DEFAULT NULL,
  `actDatDocOff` int(1) DEFAULT '0',
  `environment` varchar(50) DEFAULT '',
  `matriculaWebOff` int(1) DEFAULT '0',
  `mensajeBloqPeda` varchar(200) DEFAULT NULL,
  `mensajeBloqAdmi` varchar(200) DEFAULT NULL,
  `FHinicioMatrWeb` datetime DEFAULT NULL,
  `verLibreDeuda` int(1) DEFAULT NULL,
  `apiDrive` varchar(15) DEFAULT '',
  `siroIniPrim` varchar(10) DEFAULT '',
  `siroSecu` varchar(10) DEFAULT '',
  `siroMje` varchar(15) DEFAULT '',
  `examTodosInscri` varchar(1) DEFAULT NULL,
  `arancelesOff` int(1) DEFAULT '0',
  `documAcept1` varchar(150) DEFAULT NULL,
  `documAcept2` varchar(150) DEFAULT NULL,
  `documAcept3` varchar(150) DEFAULT NULL,
  `documAcept4` varchar(150) DEFAULT NULL,
  `claveCole` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_ento_niveles` (`idNivel`),
  KEY `FK_ento_terlec` (`idTerlecVerNotas`),
  KEY `FK_ento_terlec_2` (`idAspiTerlec`),
  CONSTRAINT `FK_ento_niveles` FOREIGN KEY (`idNivel`) REFERENCES `niveles` (`id`),
  CONSTRAINT `FK_ento_terlec` FOREIGN KEY (`idTerlecVerNotas`) REFERENCES `terlec` (`id`),
  CONSTRAINT `FK_ento_terlec_2` FOREIGN KEY (`idAspiTerlec`) REFERENCES `terlec` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.estadocivil
CREATE TABLE IF NOT EXISTS `estadocivil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.evaluac
CREATE TABLE IF NOT EXISTS `evaluac` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `idMateria` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `fecheval` date DEFAULT NULL,
  `temas` varchar(200) DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `fechregi` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_evaluac_materias` (`idMateria`),
  KEY `FK_evaluac_cursos` (`idCurso`),
  CONSTRAINT `FK_evaluac_cursos` FOREIGN KEY (`idCurso`) REFERENCES `cursos` (`Id`),
  CONSTRAINT `FK_evaluac_materias` FOREIGN KEY (`idMateria`) REFERENCES `materias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.familias
CREATE TABLE IF NOT EXISTS `familias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apellido` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `responsable` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `email` varchar(150) COLLATE utf8_spanish_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1616 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.fechascalendario
CREATE TABLE IF NOT EXISTS `fechascalendario` (
  `idFechaCalendario` int(5) NOT NULL AUTO_INCREMENT,
  `idMateria` int(50) NOT NULL,
  `fecha` date NOT NULL,
  `observaciones` varchar(100) NOT NULL,
  `horario` varchar(50) NOT NULL,
  `nombremateria` varchar(60) NOT NULL,
  PRIMARY KEY (`idFechaCalendario`),
  KEY `FK_fechascalendario_materias` (`idMateria`),
  CONSTRAINT `FK_fechascalendario_materias` FOREIGN KEY (`idMateria`) REFERENCES `materias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.horarios
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMaterias` int(11) DEFAULT NULL,
  `idDia` varchar(3) DEFAULT NULL,
  `idHora` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_horarios_materias` (`idMaterias`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10692 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.horarios26
CREATE TABLE IF NOT EXISTS `horarios26` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idProfesores` int(11) DEFAULT '0',
  `idMaterias` int(11) DEFAULT '0',
  `idDia` varchar(3) COLLATE utf8_spanish_ci DEFAULT '0',
  `idHora` int(11) DEFAULT '0',
  `idCursos` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1122 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.horariosespeciales
CREATE TABLE IF NOT EXISTS `horariosespeciales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idProfesores` int(11) NOT NULL,
  `idDia` varchar(3) COLLATE utf8_spanish_ci DEFAULT NULL,
  `idHora` int(11) DEFAULT NULL,
  `texto` varchar(250) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ief
CREATE TABLE IF NOT EXISTS `ief` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) DEFAULT NULL,
  `idMaterias` int(11) DEFAULT NULL,
  `idAprendizajes` int(11) DEFAULT NULL,
  `idNotasIef` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ief_legajos` (`idLegajos`),
  KEY `FK_ief_materias` (`idMaterias`),
  KEY `FK_ief_aprendizajes` (`idAprendizajes`),
  KEY `FK_ief_notasief` (`idNotasIef`),
  CONSTRAINT `FK_ief_aprendizajes` FOREIGN KEY (`idAprendizajes`) REFERENCES `aprendizajes` (`id`),
  CONSTRAINT `FK_ief_legajos` FOREIGN KEY (`idLegajos`) REFERENCES `legajos` (`id`),
  CONSTRAINT `FK_ief_materias` FOREIGN KEY (`idMaterias`) REFERENCES `materias` (`id`),
  CONSTRAINT `FK_ief_notasief` FOREIGN KEY (`idNotasIef`) REFERENCES `notasief` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.inasdocentes
CREATE TABLE IF NOT EXISTS `inasdocentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idProfesores` int(11) NOT NULL,
  `inaLic` int(1) NOT NULL DEFAULT '0',
  `idTipoInaDoc` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `hasta` date DEFAULT NULL,
  `cantOblig` int(2) NOT NULL DEFAULT '0',
  `cantObligIna` int(2) NOT NULL DEFAULT '0',
  `justif` int(1) NOT NULL DEFAULT '0',
  `obs` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inasdocentes_tipoinadoc` (`idTipoInaDoc`),
  CONSTRAINT `FK_inasdocentes_tipoinadoc` FOREIGN KEY (`idTipoInaDoc`) REFERENCES `tipoinadoc` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1997 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.inasistencias
CREATE TABLE IF NOT EXISTS `inasistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `cantidad` decimal(4,2) DEFAULT NULL,
  `tipo` varchar(2) CHARACTER SET latin1 DEFAULT NULL,
  `just` varchar(1) CHARACTER SET latin1 DEFAULT NULL,
  `obs` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  KEY `id` (`id`),
  KEY `FK_inasistencias_matricula` (`idMatricula`),
  CONSTRAINT `FK_inasistencias_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=29000 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.inasistencias_valores
CREATE TABLE IF NOT EXISTS `inasistencias_valores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `concepto` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `cantidad` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.indicadores
CREATE TABLE IF NOT EXISTS `indicadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMaterias` int(11) NOT NULL DEFAULT '0',
  `indicador1` text COLLATE utf8_spanish_ci,
  `indicador2` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.infoxobse
CREATE TABLE IF NOT EXISTS `infoxobse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `idMaterias` int(11) NOT NULL DEFAULT '0',
  `idIndicador` int(11) NOT NULL DEFAULT '0',
  `etapa1` text COLLATE utf8_spanish_ci,
  `etapa2` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='tabla para el jardin u otro uso donde haya indicadores fijos y texto de obs';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.legajos
CREATE TABLE IF NOT EXISTS `legajos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `idFamilias` int(10) NOT NULL DEFAULT '1',
  `codigo` int(10) DEFAULT NULL,
  `apellido` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT '',
  `nombre` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish2_ci DEFAULT '',
  `dni` int(10) DEFAULT NULL,
  `cuil` varchar(13) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipoalumno` int(1) NOT NULL DEFAULT '0',
  `fechnaci` date DEFAULT '0000-00-00',
  `ln_ciudad` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `ln_depto` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `ln_provincia` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `ln_pais` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `sexo` varchar(1) CHARACTER SET latin1 DEFAULT '0',
  `nacion` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `callenum` varchar(50) CHARACTER SET latin1 DEFAULT '',
  `barrio` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `localidad` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `codpos` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `telefono` varchar(60) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nombremad` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `dnimad` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `vivemad` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fechnacmad` date DEFAULT NULL,
  `nacionmad` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `estacivimad` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `domimad` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cpmad` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ocupacmad` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sitlabmad` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `lugtramad` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telemad` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `telecelmad` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telltm` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emailmad` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nombrepad` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `dnipad` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `vivepad` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fechnacpad` date DEFAULT NULL,
  `nacionpad` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `estacivipad` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `domipad` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
  `cppad` varchar(4) COLLATE utf8_unicode_ci DEFAULT '',
  `ocupacpad` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `sitlabpad` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `lugtrapad` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `telepad` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `telecelpad` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telltp` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emailpad` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nombretut` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dnitut` int(10) DEFAULT NULL,
  `teletut` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emailtut` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ocupactut` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `lugtratut` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telltt` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `respAdmiNom` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `respAdmiDni` int(10) NOT NULL DEFAULT '0',
  `escori` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `destino` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `emeravis` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `retira` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `retira1` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `retira2` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `obs` text CHARACTER SET latin1,
  `fechhora` datetime DEFAULT NULL,
  `identif` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `idnivel` int(11) NOT NULL DEFAULT '0',
  `needes` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `needes_detalle` text COLLATE utf8_unicode_ci,
  `certDisc` tinytext COLLATE utf8_unicode_ci,
  `vivecon` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hermanos` text COLLATE utf8_unicode_ci,
  `ec_padres` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contacto1` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contacto2` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contacto3` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parroquia` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `motivo_detalle` text COLLATE utf8_unicode_ci,
  `acopro` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `acopro_detalle` text COLLATE utf8_unicode_ci,
  `bloqmatr` tinyint(1) NOT NULL DEFAULT '0',
  `bloqadmi` tinyint(1) NOT NULL DEFAULT '0',
  `fechActDatos` datetime DEFAULT NULL,
  `libro` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `folio` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `legajo` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `obs_web` text COLLATE utf8_unicode_ci,
  `pwrd` varchar(10) CHARACTER SET utf8 DEFAULT '',
  `reglamApenom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reglamDni` int(8) DEFAULT NULL,
  `reglamEmail` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=3415 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.librodetemas
CREATE TABLE IF NOT EXISTS `librodetemas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMateria` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `claseNro` int(5) NOT NULL,
  `unidad` int(5) NOT NULL,
  `caracter` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `temas` text COLLATE utf8_spanish_ci NOT NULL,
  `actividades` text COLLATE utf8_spanish_ci NOT NULL,
  `observaciones` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42052 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.licencias
CREATE TABLE IF NOT EXISTS `licencias` (
  `idlicencias` int(11) NOT NULL AUTO_INCREMENT,
  `idPersonal` int(11) NOT NULL,
  `fechaInicio` date DEFAULT NULL,
  `fechaFin` date DEFAULT NULL,
  `parcial` int(11) DEFAULT NULL,
  PRIMARY KEY (`idlicencias`),
  KEY `personal_idx` (`idPersonal`),
  CONSTRAINT `FK_licencias_profesores` FOREIGN KEY (`idPersonal`) REFERENCES `profesores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.log
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fechhora` datetime DEFAULT NULL,
  `usuario` varchar(255) DEFAULT NULL,
  `nombre_profesor` varchar(100) DEFAULT NULL,
  `nombre_materia` varchar(50) DEFAULT NULL,
  `nombre_alumno` varchar(100) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `cambio` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.materias
CREATE TABLE IF NOT EXISTS `materias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ord` int(11) NOT NULL DEFAULT '0',
  `idCurPlan` int(11) NOT NULL DEFAULT '0',
  `idMatPlan` int(11) NOT NULL DEFAULT '0',
  `idNivel` int(11) NOT NULL DEFAULT '0',
  `idCursos` int(11) NOT NULL DEFAULT '0',
  `idTerlec` int(11) NOT NULL DEFAULT '0',
  `materia` varchar(70) DEFAULT NULL,
  `abrev` varchar(5) DEFAULT NULL,
  `cierre1e` int(1) DEFAULT '0',
  `cierre2e` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_materias_curplan` (`idCurPlan`),
  KEY `FK_materias_matplan` (`idMatPlan`),
  KEY `FK_materias_niveles` (`idNivel`),
  KEY `FK_materias_cursos` (`idCursos`),
  KEY `FK_materias_terlec` (`idTerlec`),
  CONSTRAINT `FK_materias_curplan` FOREIGN KEY (`idCurPlan`) REFERENCES `curplan` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_materias_cursos` FOREIGN KEY (`idCursos`) REFERENCES `cursos` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_materias_matplan` FOREIGN KEY (`idMatPlan`) REFERENCES `matplan` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_materias_niveles` FOREIGN KEY (`idNivel`) REFERENCES `niveles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_materias_terlec` FOREIGN KEY (`idTerlec`) REFERENCES `terlec` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5074 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.matplan
CREATE TABLE IF NOT EXISTS `matplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCurPlan` int(11) NOT NULL,
  `matPlanMateria` varchar(70) CHARACTER SET latin1 NOT NULL,
  `ord` int(2) NOT NULL DEFAULT '0',
  `abrev` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
  `codGE` varchar(15) DEFAULT NULL,
  `codGE2` varchar(15) DEFAULT NULL,
  `codGE3` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_matplan_curplan` (`idCurPlan`),
  CONSTRAINT `FK_matplan_curplan` FOREIGN KEY (`idCurPlan`) REFERENCES `curplan` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.matricula
CREATE TABLE IF NOT EXISTS `matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTerlec` int(11) DEFAULT NULL,
  `idNivel` int(11) DEFAULT NULL,
  `idCursos` int(11) DEFAULT NULL,
  `idLegajos` int(11) DEFAULT NULL,
  `idCondiciones` int(11) DEFAULT NULL,
  `obsMatr` varchar(25) DEFAULT NULL,
  `idCuotasbecas` int(11) DEFAULT NULL,
  `nroMatricula` varchar(10) DEFAULT NULL,
  `fechaMatricula` date DEFAULT NULL,
  `obsAnual` text,
  `conducta1` varchar(20) DEFAULT NULL,
  `conducta2` varchar(20) DEFAULT NULL,
  `acept1` int(1) DEFAULT '0',
  `acept2` int(1) DEFAULT '0',
  `acept3` int(1) DEFAULT '0',
  `acept4` int(1) DEFAULT '0',
  `inscripto` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_matricula_terlec` (`idTerlec`),
  KEY `FK_matricula_niveles` (`idNivel`),
  KEY `FK_matricula_cursos` (`idCursos`),
  KEY `FK_matricula_condiciones` (`idCondiciones`),
  KEY `FK_matricula_legajos` (`idLegajos`),
  CONSTRAINT `FK_matricula_condiciones` FOREIGN KEY (`idCondiciones`) REFERENCES `condiciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_matricula_cursos` FOREIGN KEY (`idCursos`) REFERENCES `cursos` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_matricula_legajos` FOREIGN KEY (`idLegajos`) REFERENCES `legajos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_matricula_niveles` FOREIGN KEY (`idNivel`) REFERENCES `niveles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_matricula_terlec` FOREIGN KEY (`idTerlec`) REFERENCES `terlec` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9730 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.mesasexamen
CREATE TABLE IF NOT EXISTS `mesasexamen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMaterias` int(11) NOT NULL DEFAULT '0',
  `idTurnosExamen` int(11) NOT NULL DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_mesasexamen_materias` (`idMaterias`) USING BTREE,
  KEY `FK_mesasexamen_turnos` (`idTurnosExamen`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.miembrosmesaexamen
CREATE TABLE IF NOT EXISTS `miembrosmesaexamen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTurnoExamen` int(11) NOT NULL DEFAULT '0',
  `idMesasExamen` int(11) NOT NULL DEFAULT '0',
  `idProfesores` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.niveles
CREATE TABLE IF NOT EXISTS `niveles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(50) CHARACTER SET latin1 NOT NULL,
  `abrev` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.nombresmaterias
CREATE TABLE IF NOT EXISTS `nombresmaterias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) DEFAULT NULL,
  `idMaterias` int(11) DEFAULT NULL,
  `nombreMateria` varchar(300) CHARACTER SET utf8 DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.notasexamen
CREATE TABLE IF NOT EXISTS `notasexamen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCalificaciones` int(11) DEFAULT NULL,
  `idLegajos` int(11) DEFAULT NULL,
  `nota` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `condExamen` varchar(2) COLLATE utf8_spanish_ci DEFAULT NULL,
  `libro` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `folio` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_notasexamen_calificaciones` (`idCalificaciones`),
  CONSTRAINT `FK_notasexamen_calificaciones` FOREIGN KEY (`idCalificaciones`) REFERENCES `calificaciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4281 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.notasief
CREATE TABLE IF NOT EXISTS `notasief` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota` varchar(10) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.notaspermitidas
CREATE TABLE IF NOT EXISTS `notaspermitidas` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `nota` varchar(20) NOT NULL,
  `idNivel` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.permisosusuarios
CREATE TABLE IF NOT EXISTS `permisosusuarios` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `orden` int(4) NOT NULL,
  `tema` varchar(50) NOT NULL DEFAULT '',
  `descripcion` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.planes
CREATE TABLE IF NOT EXISTS `planes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idNivel` int(11) NOT NULL,
  `plan` varchar(70) CHARACTER SET latin1 NOT NULL,
  `abrev` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_planes_niveles` (`idNivel`),
  CONSTRAINT `FK_planes_niveles` FOREIGN KEY (`idNivel`) REFERENCES `niveles` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.planillasdescargacuotas
CREATE TABLE IF NOT EXISTS `planillasdescargacuotas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nroPlanilla` int(11) NOT NULL DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `desde` date DEFAULT NULL,
  `hasta` date DEFAULT NULL,
  `canalPago` int(11) DEFAULT NULL,
  `nombreArchivo` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `impactado` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nroPlanilla` (`nroPlanilla`)
) ENGINE=InnoDB AUTO_INCREMENT=1010 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.plapro
CREATE TABLE IF NOT EXISTS `plapro` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `idMateria` int(10) NOT NULL DEFAULT '0',
  `idUpload_pp` int(10) NOT NULL DEFAULT '0',
  `planificacion` int(1) NOT NULL DEFAULT '0',
  `idRepoPPplan` int(10) DEFAULT '0',
  `programa` int(1) DEFAULT '0',
  `idRepoPPprog` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_plapro_materias` (`idMateria`),
  CONSTRAINT `FK_plapro_materias` FOREIGN KEY (`idMateria`) REFERENCES `materias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='planificaciones y programas';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ppc
CREATE TABLE IF NOT EXISTS `ppc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMateria` int(11) NOT NULL DEFAULT '0',
  `idProfesor` int(11) NOT NULL DEFAULT '0',
  `idSituRevis` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ppc_materias` (`idMateria`),
  KEY `FK_ppc_profesores` (`idProfesor`),
  KEY `FK_ppc_situacionrevista` (`idSituRevis`),
  CONSTRAINT `FK_ppc_materias` FOREIGN KEY (`idMateria`) REFERENCES `materias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ppc_profesores` FOREIGN KEY (`idProfesor`) REFERENCES `profesores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ppc_situacionrevista` FOREIGN KEY (`idSituRevis`) REFERENCES `situacionrevista` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2214 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ppi
CREATE TABLE IF NOT EXISTS `ppi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `ppi1` text COLLATE utf8_spanish_ci,
  `ppi2` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.profesores
CREATE TABLE IF NOT EXISTS `profesores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoProf` int(2) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `dni` int(10) NOT NULL,
  `cuil` varchar(13) NOT NULL DEFAULT '',
  `sexo` int(1) NOT NULL DEFAULT '0',
  `nivel` int(1) NOT NULL DEFAULT '0',
  `email` varchar(100) DEFAULT NULL,
  `emailInsti` varchar(100) DEFAULT NULL,
  `callenum` varchar(200) DEFAULT NULL,
  `barrio` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `nacion` varchar(50) DEFAULT NULL,
  `estacivi` int(2) DEFAULT NULL,
  `legJunta` varchar(10) DEFAULT NULL,
  `legEscuela` varchar(10) DEFAULT NULL,
  `celular` varchar(50) DEFAULT NULL,
  `fechnaci` date DEFAULT NULL,
  `titulo` varchar(250) DEFAULT NULL,
  `numreg` varchar(30) DEFAULT NULL,
  `apto` date DEFAULT NULL,
  `incapac` varchar(50) DEFAULT NULL,
  `escalafonD` date DEFAULT NULL,
  `escalafonE` date DEFAULT NULL,
  `cargo` varchar(30) DEFAULT NULL,
  `obs` text,
  `ult_idTerlec` int(2) DEFAULT NULL,
  `ult_idNivel` int(2) DEFAULT NULL,
  `pwrd` varchar(10) DEFAULT NULL,
  `permisos` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_profesores_profesortipo` (`IdTipoProf`),
  CONSTRAINT `FK_profesores_profesortipo` FOREIGN KEY (`IdTipoProf`) REFERENCES `profesortipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=570 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.profesortipo
CREATE TABLE IF NOT EXISTS `profesortipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `accesoMenu` int(1) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cuit` varchar(13) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `proveedor` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.reinco2025
CREATE TABLE IF NOT EXISTS `reinco2025` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL,
  `idReinco_tipo` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `obs` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.reinco2025_tipo
CREATE TABLE IF NOT EXISTS `reinco2025_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(3) NOT NULL DEFAULT '0',
  `tipo` varchar(80) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.reincorporaciones
CREATE TABLE IF NOT EXISTS `reincorporaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `fechCarga` date DEFAULT NULL,
  `fechLibre` date DEFAULT NULL,
  `fechReinc` date DEFAULT NULL,
  `nroReinc` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_reincorporaciones_matricula` (`idMatricula`)
) ENGINE=InnoDB AUTO_INCREMENT=768 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.reloj
CREATE TABLE IF NOT EXISTS `reloj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) DEFAULT NULL,
  `idNivel` int(11) DEFAULT NULL,
  `horario` varchar(13) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.rendicionesroela
CREATE TABLE IF NOT EXISTS `rendicionesroela` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fechaPago` date DEFAULT NULL,
  `fechaAcreditacion` date DEFAULT NULL,
  `idCuotastipopago` int(11) DEFAULT NULL,
  `idLegajos` int(11) DEFAULT NULL,
  `nroPlanilla` int(11) DEFAULT NULL,
  `idCuotas` int(11) DEFAULT NULL,
  `fechVenc1` date DEFAULT NULL,
  `importe` float(10,2) DEFAULT NULL,
  `pagado` float(10,2) DEFAULT NULL,
  `interes` float(10,2) DEFAULT NULL,
  `bonificacion` float(10,2) DEFAULT NULL,
  `nombreArchivo` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `cadenaPago` varchar(480) COLLATE utf8_spanish_ci DEFAULT NULL,
  `idCuotasbecas` int(11) DEFAULT NULL,
  `idCuotasgeneradas` int(11) DEFAULT NULL,
  `impactado` int(1) DEFAULT '0',
  `idCursos` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `nroPlanilla` (`nroPlanilla`),
  CONSTRAINT `FK_rendicionesroela_planillasdescargacuotas` FOREIGN KEY (`nroPlanilla`) REFERENCES `planillasdescargacuotas` (`nroPlanilla`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=37172 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.repopp
CREATE TABLE IF NOT EXISTS `repopp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlaPro` int(11) NOT NULL DEFAULT '0',
  `nombre` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `plan_ok` int(1) DEFAULT '0',
  `prog_ok` int(1) DEFAULT '0',
  `plan_obs` text COLLATE utf8_spanish_ci,
  `prog_obs` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`id`),
  KEY `FK_repopp_plapro` (`idPlaPro`),
  CONSTRAINT `FK_repopp_plapro` FOREIGN KEY (`idPlaPro`) REFERENCES `plapro` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.sanciones
CREATE TABLE IF NOT EXISTS `sanciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `idTipoSancion` int(2) DEFAULT NULL,
  `idProfesores` int(11) DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `cantidad` int(2) DEFAULT NULL,
  `motivo` text CHARACTER SET latin1,
  `solipor` varchar(150) CHARACTER SET latin1 DEFAULT NULL,
  `publicada` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_sanciones_sancionTipo` (`idTipoSancion`),
  KEY `FK_sanciones_matricula` (`idMatricula`),
  CONSTRAINT `FK_sanciones_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_sanciones_sancionTipo` FOREIGN KEY (`idTipoSancion`) REFERENCES `sanciontipo` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1351 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.sanciontipo
CREATE TABLE IF NOT EXISTS `sanciontipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.sexos
CREATE TABLE IF NOT EXISTS `sexos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sexo` varchar(20) CHARACTER SET latin1 NOT NULL,
  KEY `Columna 1` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.situacionrevista
CREATE TABLE IF NOT EXISTS `situacionrevista` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sitRev` varchar(20) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.solibecahist
CREATE TABLE IF NOT EXISTS `solibecahist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `nro` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=661 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.temasrecup
CREATE TABLE IF NOT EXISTS `temasrecup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL DEFAULT '0',
  `idMaterias` int(11) NOT NULL DEFAULT '0',
  `modulo` int(2) NOT NULL DEFAULT '0',
  `temas` varchar(250) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.terlec
CREATE TABLE IF NOT EXISTS `terlec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ano` int(4) DEFAULT NULL,
  `orden` int(2) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.tipoinadoc
CREATE TABLE IF NOT EXISTS `tipoinadoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `motivo` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `ord` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.tipomovimiento
CREATE TABLE IF NOT EXISTS `tipomovimiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipoMovimiento` varchar(20) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.turnos
CREATE TABLE IF NOT EXISTS `turnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `turno` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `nturno` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.turnosexamen
CREATE TABLE IF NOT EXISTS `turnosexamen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTurno` int(11) NOT NULL DEFAULT '0',
  `idTerlec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.ud_tipodoc
CREATE TABLE IF NOT EXISTS `ud_tipodoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipodoc` varchar(300) DEFAULT NULL,
  `oblig` int(1) DEFAULT NULL,
  `nombreCorto` varchar(15) DEFAULT NULL,
  `obs` varchar(200) DEFAULT NULL,
  `formato` varchar(5) DEFAULT NULL,
  `formato2` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.upload_doc
CREATE TABLE IF NOT EXISTS `upload_doc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) DEFAULT NULL,
  `id_tipodoc` int(11) DEFAULT NULL,
  `tipodoc` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `codigo` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `descripcion` varchar(30) CHARACTER SET latin1 DEFAULT '',
  `nombre` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6351 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.usermenu
CREATE TABLE IF NOT EXISTS `usermenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item` varchar(60) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.vacia
CREATE TABLE IF NOT EXISTS `vacia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla ia_demo.variosalumnos
CREATE TABLE IF NOT EXISTS `variosalumnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idLegajos` int(11) NOT NULL DEFAULT '0',
  `analCohorte` varchar(30) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `analObservaciones` text COLLATE utf8_spanish_ci,
  `analParaCompletar` text COLLATE utf8_spanish_ci,
  `analValidez` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `analLibroFolio` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `analFechaEmision` date DEFAULT NULL,
  `analParaPre` varchar(200) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_variosalumnos_legajos` (`idLegajos`),
  CONSTRAINT `FK_variosalumnos_legajos` FOREIGN KEY (`idLegajos`) REFERENCES `legajos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
