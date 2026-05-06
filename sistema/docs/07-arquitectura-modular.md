# Arquitectura Modular con Paquetes Composer

> Este documento describe la arquitectura a la que migra el sistema para soportar
> 15 instalaciones independientes que comparten código común pero pueden mantener
> versiones distintas de cada módulo.

---

## 1. Motivación

El sistema nació como una instalación única que se fue personalizando en cada
colegio. El resultado son 15 forks de facto: mismo origen, bases de datos y código
divergentes. El objetivo de esta arquitectura es:

- Volver a tener un núcleo común mantenido en un solo lugar.
- Permitir que cada colegio elija cuándo recibir una mejora y cuáles customizar.
- Mantener los overrides chicos y localizados: si un colegio tiene algo distinto,
  queda explícito en su propio repo, no enterrado en `if ($slug === 'X')` dentro
  del código compartido.

---

## 2. Los tres tipos de repositorios

```
GitHub (privado)
│
├── sistema-base                  ← esqueleto Laravel + auth + schoolCtx + design system
│
├── modulo-listados               ← paquete Composer versionado
├── modulo-comunicaciones         ← paquete Composer versionado
├── modulo-disciplinario          ← paquete Composer versionado
├── modulo-cuotas                 ← paquete Composer versionado
├── ...
│
├── colegio-sanmartin             ← repo que corre en el VPS del colegio
├── colegio-bellavista            ← repo que corre en el VPS del colegio
└── ...
```

### `sistema-base`

Contiene todo lo que comparten los 15 colegios y que nunca se toca por colegio:

- Login (guard `profesores` + guard `alumno`)
- `SchoolContext` / `StudentContext` y sus helpers (`schoolCtx()`, `studentCtx()`, etc.)
- Middleware `EnsureSchoolContext`, `EnsureStudentContext`, `permiso`
- Layout `app.blade.php`, `alumno.blade.php`, `guest.blade.php`
- Design system Tailwind (paleta SE, componentes `se-*`)
- Modelos Eloquent de las tablas del núcleo legacy (`Legajo`, `Matricula`, `Curso`, etc.)
- Helper `schoolPdfHeaderData()`, `schoolLogoUrl()`, etc.

### `modulo-XXXX`

Cada módulo es un paquete Composer independiente con versionado SemVer.
Contiene: Livewire components, vistas, rutas, migraciones propias, servicios y
contratos públicos (interfaces). Ver sección 4 y `08-template-paquete-modulo.md`.

### `colegio-YYYY`

El repo que se clona en el VPS. Declara en `composer.json` qué versión de cada
paquete usa. Almacena en `app/Custom/` solo los overrides realmente específicos
de ese colegio. Ver sección 5 y `09-template-colegio.md`.

---

## 3. Diagrama de dependencias

```
sistema-base  ←──────────────────────────────────────┐
                                                      │
modulo-listados v1.0 ──┐                              │
modulo-listados v1.1 ──┤  el colegio elige su versión │
                        ↓                             │
              colegio-sanmartin ──────────────────────┘
              colegio-bellavista ─────────────────────┘
              colegio-... (x13) ──────────────────────┘
```

Cada `colegio-YYYY/composer.json`:

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "se/sistema-base": "^1.0",
    "se/modulo-listados": "^1.0",
    "se/modulo-comunicaciones": "^2.0",
    "se/modulo-disciplinario": "^1.0"
  },
  "repositories": [
    { "type": "vcs", "url": "git@github.com:USUARIO/sistema-base.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-listados.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-comunicaciones.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-disciplinario.git" }
  ]
}
```

El `composer.lock` fija exactamente qué commit de cada paquete está corriendo
en ese colegio. Sanmartín puede tener `modulo-listados 1.0.x` y Bellavista
`1.1.x` y ambos convivir sin interferencia.

---

## 4. Anatomía de un paquete módulo

Estructura estándar de `modulo-listados/` (ver detalle en `08-template-paquete-modulo.md`):

```
modulo-listados/
├── composer.json                   ← auto-discovery del ServiceProvider
├── CHANGELOG.md                    ← historial de versiones con notas de migración
├── src/
│   ├── ListadosServiceProvider.php ← registra rutas, vistas, migraciones, config
│   ├── Livewire/                   ← componentes Livewire del módulo
│   ├── Http/Controllers/           ← controllers (ej: PDF export)
│   ├── Models/                     ← solo si el módulo tiene tablas propias
│   ├── Services/                   ← lógica de negocio
│   ├── Support/                    ← clases de soporte (filtros, catálogos, etc.)
│   └── Contracts/                  ← interfaces públicas del módulo
├── resources/views/                ← vistas con namespace 'listados::*'
├── routes/web.php                  ← rutas del módulo
├── database/migrations/            ← migraciones aditivas e idempotentes
└── config/listados.php             ← config publicable (flags del módulo)
```

Puntos clave:

- Las vistas se referencian como `view('listados::por-curso')`. Eso permite que el
  colegio pise solo la vista que necesita sin tocar el paquete.
- Los servicios se enlazan a interfaces en `Contracts/` para que un colegio pueda
  reemplazar la implementación inyectando su clase local.
- Las migraciones son **aditivas e idempotentes**: usan `Schema::hasColumn` antes
  de agregar columnas. Nunca rompen si el colegio ya tiene ese campo.
- `composer.json` declara `extra.laravel.providers` para auto-discovery; ningún
  colegio necesita registrar el provider manualmente.

---

## 5. Anatomía de un repo de colegio

Estructura de `colegio-sanmartin/` (ver detalle en `09-template-colegio.md`):

```
colegio-sanmartin/
├── composer.json          ← paquetes y versiones
├── auth.json              ← token GitHub privado (gitignored)
├── .env                   ← TENANT_SLUG=sanmartin, DB_*, APP_KEY, ...
├── app/
│   ├── Custom/
│   │   └── Listados/
│   │       ├── Livewire/  ← overrides de componentes (extienden el del paquete)
│   │       └── views/     ← vistas que pisan al paquete por namespace
│   └── Providers/
│       └── TenantOverridesServiceProvider.php
├── config/
│   └── tenant.php         ← branding, flags de features, módulos activos
└── resources/             ← assets y vistas específicas del colegio si hay
```

---

## 6. Cómo se propaga una mejora

Escenario: agregar "Listado por materia" al módulo Listados.

**Paso 1 — En el repo `modulo-listados`:**

```bash
# Agregar el componente y la vista.
# Commitear y crear el tag de versión.
git add .
git commit -m "feat: agregar ListadoPorMateria"
git tag v1.1.0
git push origin main --tags
```

**Paso 2 — En cada VPS que quiera la mejora:**

```bash
cd /var/www/colegio-bellavista
composer update se/modulo-listados
php artisan view:clear
php artisan config:cache
# Si v1.1.0 trajo migraciones nuevas, ejecutar manualmente:
# php artisan migrate
```

**Paso 3 — El colegio que NO quiere la mejora (Sanmartín):**

No hace nada. Su `composer.lock` lo mantiene en `1.0.x`.

**Paso 4 — Override puntual de Bellavista (botón con texto distinto):**

En `colegio-bellavista/app/Custom/Listados/views/boton-exportar.blade.php`
se copia solo la vista del botón y se cambia el texto. El
`TenantOverridesServiceProvider` prepende el path local al namespace `listados::`.
Laravel lo encuentra primero. Son 5 líneas; no se toca el paquete.

> **Regla de BD:** las migraciones se *escriben* en el paquete pero las *ejecuta*
> un humano en el VPS (ver `06-reglas-de-seguridad.md` sección 9). Nunca se
> ejecutan desde el chat o terminal del asistente.

---

## 7. Mecanismo de overrides por colegio

Tres niveles, del más liviano al más pesado. Usar siempre el más liviano posible.

### Nivel 1 — Configuración (sin código)

`config/tenant.php` del colegio:

```php
return [
    'modulos' => [
        'listados' => [
            'por_materia' => false,   // este colegio no muestra ese tipo de listado
        ],
    ],
];
```

En la vista del paquete:

```blade
@if(config('tenant.modulos.listados.por_materia', true))
    {{-- botón Listado por materia --}}
@endif
```

### Nivel 2 — Vista (solo Blade, sin PHP)

El `TenantOverridesServiceProvider` del colegio prepende el path local:

```php
View::prependNamespace('listados', resource_path('custom/listados'));
```

Cualquier archivo en `resources/custom/listados/boton-exportar.blade.php` pisa
la vista `listados::boton-exportar` del paquete. Sin tocar PHP.

### Nivel 3 — Servicio o componente Livewire

Para override de lógica de negocio real. La clase del colegio extiende la del
paquete y reemplaza solo el método necesario:

```php
// app/Custom/Listados/Livewire/ListadoPorCurso.php del colegio
namespace App\Custom\Listados\Livewire;

use Se\ModuloListados\Livewire\ListadoPorCurso as Base;

class ListadoPorCurso extends Base
{
    protected function columnasVisibles(): array
    {
        return [...parent::columnasVisibles(), 'beca_municipal'];
    }
}
```

El `TenantOverridesServiceProvider` enlaza la clase del colegio en el contenedor:

```php
$this->app->bind(
    \Se\ModuloListados\Livewire\ListadoPorCurso::class,
    \App\Custom\Listados\Livewire\ListadoPorCurso::class
);
```

> **Regla:** si más de 3 colegios necesitan el mismo override, deja de ser
> override. Se promueve a feature configurable dentro del paquete (Nivel 1).

---

## 8. Versionado con SemVer

Cada paquete sigue SemVer estricto:

| Tipo | Cuándo | Ejemplo |
|---|---|---|
| PATCH `x.y.Z` | Bugfix sin cambios de API | `1.0.1` |
| MINOR `x.Y.0` | Feature nueva, compatible con versiones anteriores | `1.1.0` |
| MAJOR `X.0.0` | Cambio de API pública o migración obligatoria | `2.0.0` |

Los `composer.json` de los colegios usan `^1.0` (acepta minors automáticamente).
Un MAJOR requiere cambio manual en el `composer.json` del colegio; eso es
intencional: fuerza a revisar el CHANGELOG antes de actualizar.

Cada paquete mantiene `CHANGELOG.md` con una sección por versión. Las versiones
MAJOR o MINOR con migraciones incluyen sección **"Notas de deploy"** que detalla
exactamente qué comandos correr en el VPS.

---

## 9. Autenticación con repos privados de GitHub

**Opción A — Personal Access Token (más simple para empezar):**

`auth.json` en la raíz de cada colegio (gitignored):

```json
{
  "github-oauth": {
    "github.com": "ghp_XXXXXXXXXXXXXXXXXXXX"
  }
}
```

El token necesita scope `repo`. Un token por desarrollador; no por colegio.

**Opción B — SSH deploy key (recomendada para VPS de producción):**

Se genera un par de llaves SSH por VPS y se agrega la pública como "deploy key"
(solo lectura) en cada repo de paquete de GitHub. Sin tokens, sin expiración.

**Durante desarrollo local — path repo:**

En el `composer.json` del colegio en desarrollo, reemplazar temporalmente el VCS
por un path local para iterar sin hacer push/tag continuo:

```json
"repositories": [
  { "type": "path", "url": "../modulo-listados" }
]
```

---

## 10. Regla de diseño de módulos

Los módulos no deben conocerse entre sí directamente. Si "Comunicaciones"
necesita datos de legajos, importa el modelo `Legajo` del `sistema-base`,
no un servicio de otro módulo. Si en algún momento un módulo necesita algo
de otro, se declara la dependencia explícita en su `composer.json`.

Nunca `if (class_exists('Se\ModuloComunicaciones\...'))` dentro de código de
otro módulo. Las dependencias opcionales entre módulos se resuelven con eventos
de Laravel o con interfaces publicadas en `sistema-base`.

---

## 11. Inventario de drift (trabajo previo a la migración)

Antes de crear los repos de colegio y migrar colegios reales, hay que hacer
un inventario formal de las diferencias entre las 15 bases de datos.

Para cada par (colegio, schema canónico) clasificar cada diferencia:

| Tipo | Estrategia |
|---|---|
| Mismo dato, distinto nombre de columna | Accessor Eloquent o vista SQL; no renombrar la tabla legacy |
| Columna extra exclusiva del colegio | Migration en `database/migrations/tenant/` del repo del colegio |
| Tabla extra exclusiva | Migration tenant + módulo Custom en ese colegio |
| Columna faltante en algunos colegios | Migration core aditiva con `Schema::hasColumn` + default nullable |
| Tipo de dato distinto | Migration core normalizadora o tolerancia en validación |

---

## 12. Script de deploy

Script mínimo para el VPS. Guarda en la raíz de cada repo de colegio como
`deploy.sh`. Se llama con `./deploy.sh` (un colegio completo) o
`./deploy-all.sh` para iterar los 15.

```bash
#!/bin/bash
# deploy.sh — ejecutar en la raíz del repo de colegio en el VPS
set -e

echo "--- Pulling latest code ---"
git pull origin main

echo "--- Updating Composer dependencies ---"
composer install --no-dev --optimize-autoloader

echo "--- Clearing caches ---"
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "--- Building assets ---"
npm ci --omit=dev
npm run build

echo "--- Deploy completado: $(date) ---"
# Las migraciones NO se ejecutan automáticamente.
# Si esta versión trae migraciones, ejecutar manualmente:
#   php artisan migrate
```

---

## 13. Roadmap de fases

| Fase | Objetivo | Estado |
|---|---|---|
| F1 | Documentar la arquitectura (este archivo) | Completo |
| F2 | Templates de paquete y de colegio (`08` y `09`) | Completo |
| F3 | Extraer Listados a paquete piloto (path local) | Pendiente |
| F4 | Convertir el repo actual en colegio piloto | Pendiente |
| F5 | Probar flujo de override real en el piloto | Pendiente |
| F6 | Subir paquete a GitHub privado y configurar auth | Pendiente |
| F7 | Repetir con Comunicaciones (segundo módulo) | Pendiente |
| F8 | Inventario de drift de los 15 colegios | Pendiente |
| F9 | Template de repo de colegio + `deploy.sh` | Pendiente |
| F10 | Migrar primer colegio real (convivencia con ScriptCase) | Pendiente |
| F11 | Migrar el resto en lotes | Pendiente |

---

## 14. Decisiones tomadas

### Vendor name: `se`

El vendor name de todos los paquetes y del sistema base es **`se`**, coherente
con la identidad visual y los nombres internos del proyecto (`ui-front-se.mdc`,
clases `.se-*`, carpeta `/SE/`).

Ejemplos:
- `se/sistema-base`
- `se/modulo-listados`
- `se/modulo-comunicaciones`
- `se/modulo-disciplinario`

El namespace PHP correspondiente es `Se\NombreDelModulo\`:
- `Se\SistemaBase\`
- `Se\ModuloListados\`
- `Se\ModuloComunicaciones\`

### Convención de versionado inicial: `0.1.0`

Los paquetes arrancan en `0.1.0` (pre-1.0). Esto señala que la API pública
todavía puede cambiar mientras se trabaja en los primeros colegios piloto.
Se pasa a `1.0.0` cuando la estructura del paquete es estable y al menos
dos colegios reales lo están usando en producción.

Los `composer.json` de los colegios durante esta fase usan `^0.1`.

### Módulo piloto: Listados

El primer módulo a extraer como paquete es **Listados**:

- Código fuente: `app/Livewire/Listados/ListadoPorCurso.php` y el controlador PDF.
- Soporte: `app/Support/ListadoCursoPdfFieldCatalog.php`,
  `app/Support/ListadoCursoCondicionFiltro.php`.
- Modelo de config: `app/Models/CampoListadoAlumno.php`.
- Vista: `resources/views/livewire/listados/por-curso.blade.php`.
- Rutas: `/listados/por-curso` y `/listados/por-curso/listado` (PDF).
- Migración propia: `campos_listado_alumnos` (tabla de configuración de campos).

El módulo es autocontenido, tiene pocos acoplamientos externos (solo usa modelos
del núcleo: `Curso`, `Legajo`, `Matricula`) y tiene una funcionalidad bien
delimitada. Ideal para validar el modelo sin riesgo.
