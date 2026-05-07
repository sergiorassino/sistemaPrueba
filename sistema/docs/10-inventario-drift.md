# Inventario de Drift de Bases de Datos

> Este documento registra las diferencias de schema encontradas entre las bases de datos
> de los distintos colegios y la base de referencia (`ia_demo`).
>
> Se genera con el comando `php artisan se:drift-report` y sirve como insumo para
> decidir qué cambios van al schema canónico y cuáles quedan como migraciones tenant.

---

## 1. Cómo ejecutar el informe

```bash
# Comparar ia_demo (referencia) contra otro colegio:
php artisan se:drift-report --reference=ia_demo --compare-with=ia_montecristo

# Salida en Markdown (para copiar a este documento):
php artisan se:drift-report --reference=ia_demo --compare-with=ia_montecristo --format=markdown

# Filtrar solo algunas tablas:
php artisan se:drift-report --reference=ia_demo --compare-with=ia_montecristo --tables=legajos,matricula
```

---

## 2. Clasificación de diferencias y estrategias

| Tipo | Descripción | Estrategia |
|---|---|---|
| ⚠️ tabla faltante | La tabla existe en `ia_demo` pero no en el colegio comparado | Migration core aditiva con `Schema::hasTable` |
| ➕ tabla extra | El colegio tiene una tabla que no está en `ia_demo` | Migration en `database/migrations/tenant/` del colegio |
| ⚠️ columna faltante | Columna en `ia_demo` que falta en el colegio | Migration core aditiva con `Schema::hasColumn` + default nullable |
| ➕ columna extra | Columna solo en ese colegio | Migration tenant o Accessor Eloquent |
| 🔀 tipo distinto | Misma columna, tipo de dato diferente | Accessor Eloquent o migration normalizadora; evaluar caso a caso |

> **Regla de oro:** si la diferencia aparece en 3 o más colegios, el cambio va al schema
> canónico. Si es exclusiva de 1 o 2 colegios, va como migración tenant.

---

## 3. Colegios relevados

| Colegio | BD | Estado | Diferencias |
|---|---|---|---|
| Demo (referencia) | `ia_demo` | ✅ Referencia | — |
| Montecristo | `ia_montecristo` | ✅ Relevado (07-may-2026) | 138 diferencias |
| NSSC | `ia_nssc` | ✅ Relevado (07-may-2026) | BD vacía — setup completo vía `migrate` |
| Colegio 4 | `ia_...` | ⏳ Pendiente | — |
| Colegio 4 | `ia_...` | ⏳ Pendiente | — |
| Colegio 5 | `ia_...` | ⏳ Pendiente | — |
| Colegio 6 | `ia_...` | ⏳ Pendiente | — |
| Colegio 7 | `ia_...` | ⏳ Pendiente | — |
| Colegio 8 | `ia_...` | ⏳ Pendiente | — |
| Colegio 9 | `ia_...` | ⏳ Pendiente | — |
| Colegio 10 | `ia_...` | ⏳ Pendiente | — |
| Colegio 11 | `ia_...` | ⏳ Pendiente | — |
| Colegio 12 | `ia_...` | ⏳ Pendiente | — |
| Colegio 13 | `ia_...` | ⏳ Pendiente | — |
| Colegio 14 | `ia_...` | ⏳ Pendiente | — |
| Colegio 15 | `ia_...` | ⏳ Pendiente | — |

Para agregar un colegio: actualizar DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD si el servidor es distinto
(configurar una entrada en `database.connections` en `config/database.php`) y luego correr el comando.

---

## 4. Resumen ejecutivo: `ia_demo` → `ia_montecristo`

Relevado: **07 de mayo de 2026**
Comando: `php artisan se:drift-report --reference=ia_demo --compare-with=ia_montecristo`

| Categoría | Cantidad |
|---|---|
| Tablas sin diferencias | 80 |
| Tablas faltantes en montecristo | 12 |
| Tablas extra en montecristo | 15 |
| Columnas faltantes en montecristo | 36 |
| Columnas extra en montecristo | 72 |
| Tipos de dato distintos | 13 |
| **Total diferencias** | **138** |

### 4.1 Tablas faltantes en `ia_montecristo` (presentes en `ia_demo`)

Estas tablas existen en la referencia pero no en montecristo. Evaluar si son del schema
canónico o si `ia_demo` tiene tablas que son en realidad específicas de ese colegio.

| Tabla | Observación | Decisión |
|---|---|---|
| `actasreincorporaciones` | Relacionada con proceso de reincorporación | ⏳ Evaluar |
| `aspicursos_fps` | Módulo de aspirantes FPS | ⏳ Evaluar |
| `aspiniveles` | Módulo de aspirantes — niveles | ⏳ Evaluar |
| `aspirantes_ento` | Módulo de aspirantes — datos del establecimiento | ⏳ Evaluar |
| `aspirantes_fps` | Módulo de aspirantes — datos FPS | ⏳ Evaluar |
| `certificacion` | Certificaciones de alumnos | ⏳ Evaluar |
| `cubririnasdocentes` | Docentes para cubrir inasistencias | ⏳ Evaluar |
| `emails` | Cola de emails internos | ⏳ Evaluar |
| `emails_enviados_morosos` | Historial de emails a morosos | ⏳ Evaluar |
| `licencias` | Licencias de personal | ⏳ Evaluar |
| `solibecahist` | Historial de solicitudes de beca | ⏳ Evaluar |
| `variosalumnos` | Datos varios de alumnos | ⏳ Evaluar |

### 4.2 Tablas extra en `ia_montecristo` (no existen en `ia_demo`)

Estas tablas son específicas del colegio Montecristo. Cuando se cree el repo
`colegio-montecristo`, estas tablas irán en `database/migrations/tenant/`.

| Tabla | Observación | Decisión |
|---|---|---|
| `aec` | — | Migration tenant |
| `aspiento` | Aspirantes — datos del establecimiento (variante) | Migration tenant |
| `aspirantes` | Módulo de aspirantes propio de montecristo | Migration tenant |
| `certiftrayectoria` | Certificados de trayectoria escolar | Migration tenant |
| `cuentasbancos` | Cuentas bancarias del colegio | Migration tenant |
| `debitosautomaticosborrar` | Tabla de trabajo — débitos automáticos | Migration tenant |
| `doe` | — | Migration tenant |
| `feriados` | Calendario de feriados propio | Migration tenant |
| `intencionpago` | Intención de pago de cuotas | Migration tenant |
| `paseprovisorio` | Pases provisorios de alumnos | Migration tenant |
| `siro_rendiciones` | Rendiciones SIRO (sistema de pagos) | Migration tenant |
| `sirobasedeuda` | Base de deuda para SIRO | Migration tenant |
| `sirodeudasubida` | Control de subida de deuda a SIRO | Migration tenant |
| `tarjetasoperadores` | Operadores de tarjetas de crédito | Migration tenant |
| `tarjetaspendientes` | Tarjetas con operaciones pendientes | Migration tenant |

### 4.3 Columnas con diferencias en tablas comunes

#### `calificaciones` — 13 columnas faltantes en montecristo

Columnas de libro/folio de actas. Posiblemente `ia_demo` es el colegio que usa el sistema de actas;
montecristo no lo implementa.

| Columna | Tipo | Decisión |
|---|---|---|
| `tm1` … `tm6`, `tmNota` | varchar(15) | Evaluar si son canónicas o de ia_demo |
| `libro`, `folio` | varchar(10) | Idem |
| `fechApro`, `libroDic`, `folioDic`, `fechAproDic`, `libroFeb`, `folioFeb`, `fechAproFeb` | date/varchar | Idem |

#### `cuotasgeneradas` — 3 columnas faltantes en montecristo

| Columna | Tipo | Decisión |
|---|---|---|
| `difePlan` | int(1) | ⏳ Evaluar |
| `fechaDifePlan` | date | ⏳ Evaluar |
| `avisoPago` | int(1) | ⏳ Evaluar |

#### `legajos` — columnas con diferencias (resumen)

**Faltantes en montecristo** (presentes en ia_demo):

| Columna | Tipo | Decisión |
|---|---|---|
| `telecelmad`, `telecelpad` | varchar(50) | ⏳ Evaluar — teléfonos celular |
| `lugtratut`, `telltt` | varchar(50/30) | ⏳ Evaluar |
| `retira1`, `retira2` | varchar(250) | ⏳ Evaluar — personas autorizadas a retirar |
| `certDisc` | tinytext | ⏳ Evaluar — certificado de discapacidad |
| `ec_padres` | varchar(30) | ⏳ Evaluar — estado civil de padres |
| `contacto1`, `contacto2` | varchar(200) | ⏳ Evaluar |

**Extra en montecristo** (no en ia_demo):

| Columna | Tipo | Decisión |
|---|---|---|
| `locamad`, `locapad` | varchar(50) | Migration tenant — localidad de padres |
| `domitut` | varchar(70) | Migration tenant |
| `religion`, `sacramentos` | varchar(50/100) | Migration tenant — colegio religioso |
| `obso_sn`, `obso_nombre`, `obso_nro` | varchar | Migration tenant |
| `telealte1_nom/tel`, `telealte2_nom/tel` | varchar | Migration tenant — teléfonos alternativos |
| `autori1`, `autori2` | varchar(300) | Migration tenant — autorizados a retirar |
| `bloqAbogado` | tinyint(1) | Migration tenant |

#### `matricula` — columnas con diferencias

**Faltantes en montecristo:**
`conducta1`, `conducta2`, `acept1`…`acept4`, `inscripto`

**Extra en montecristo:**
`fechaMatriculacion`, `matricCondic`, `tmp_inas`, `tmp_edfi`, `tmp_sanc`, `tmp_prom`

#### `ento` — columnas extra en montecristo

`verBimesOff`, `bimesOffMensaje`, `actDatosDocOff`, `imprBoleOff`, `mensajeAlumno`,
`carpeta`, `codCol`, `mjeAbogado`, `siroIdentCuenta`, `siroMensajeTicket`

> Muchas de estas columnas son configuración del módulo SIRO (pagos online de montecristo).
> Candidatas a quedar en una migración tenant que incluya el módulo SIRO.

#### Tipos distintos — tabla de referencias cruzadas

| Tabla | Columna | `ia_demo` | `ia_montecristo` | Impacto |
|---|---|---|---|---|
| `bancos` | `monto`, `saldo` | float(15,2) | decimal(20,2) | Bajo — Accessor o cast |
| `caja` | `monto`, `saldo` | float(15,2) | decimal(20,2) | Bajo — Accessor o cast |
| `cursos` | `turno` | varchar(20) | varchar(10) | Bajo — posible truncado |
| `ento` | `actDatDocOff`, `arancelesOff` | int(1) | int(11) | Sin impacto funcional |
| `inasistencias` | `tipo` | varchar(2) | varchar(1) | **Medio** — verificar valores |
| `legajos` | `telefono`, `email`, `telemad`, etc. | varchar más largo | varchar más corto | **Medio** — posible truncado de datos |
| `legajos` | `dnitut` | int(10) | varchar(10) | **Medio** — DNI como texto vs entero |
| `legajos` | `vivecon` | varchar(200) | varchar(50) | **Medio** — posible truncado |
| `niveles` | `abrev` | varchar(5) | varchar(4) | Bajo |
| `profesores` | `email` | varchar(100) | varchar(70) | Bajo |

---

## 5. Decisiones pendientes

Antes de crear el repo `colegio-montecristo` y aplicar migraciones, revisar:

1. **Tablas del módulo de aspirantes** (`aspirantes`, `aspiento`, `aspicursos_fps`, `aspiniveles`,
   `aspirantes_ento`, `aspirantes_fps`): determinar si son canónicas o exclusivas de algunos colegios.
   Si ≥ 3 colegios las tienen → agregar al schema canónico como migración core con `hasTable`.

2. **Columnas de `legajos` con VARCHAR más corto** en montecristo: antes de migrar, verificar
   que los datos existentes no exceden el límite. Si hay datos más largos en montecristo, el
   schema canónico debe tomar el máximo.

3. **`legajos.dnitut` int vs varchar**: decidir si la migración normalizadora amplía a varchar o
   si montecristo necesita migrar sus datos. El DNI como varchar es más correcto (acepta DNI
   extranjeros con caracteres).

4. **Columnas de libro/folio en `calificaciones`**: verificar si son del módulo de actas que
   solo algunos colegios usan. Si es así, candidatas a quedar en un futuro
   `modulo-actas` con su propia migración.

---

## 6. Próximos pasos sugeridos

1. Ejecutar `se:drift-report` contra cada una de las 13 BDs restantes.
2. Para cada diferencia que aparezca en ≥ 3 colegios → crear migration core.
3. Para diferencias exclusivas de 1 colegio → crear migration en `database/migrations/tenant/`.
4. Documentar la decisión en la columna "Decisión" de este documento.
5. Una vez cerrado el inventario, proceder con F10 (migrar primer colegio real).
