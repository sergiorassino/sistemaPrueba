# Template: Paquete Módulo

> Referencia para crear cualquier nuevo paquete `se/modulo-XXXX`.
> Usar como checklist al extraer un módulo del repo actual o al crear uno nuevo.
>
> Ver también: `07-arquitectura-modular.md` para el contexto general.

---

## 1. Estructura de directorios

```
modulo-listados/                    ← nombre del repo en GitHub
├── composer.json
├── CHANGELOG.md
├── README.md
├── src/
│   ├── ListadosServiceProvider.php
│   ├── Livewire/
│   │   └── ListadoPorCurso.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── ListadoCursoPdfController.php
│   ├── Models/                     ← SOLO si el módulo tiene tablas propias
│   │   └── CampoListadoAlumno.php
│   ├── Services/                   ← lógica de negocio no acoplada a HTTP
│   ├── Support/                    ← clases de soporte (catálogos, filtros, etc.)
│   │   ├── ListadoCursoPdfFieldCatalog.php
│   │   └── ListadoCursoCondicionFiltro.php
│   └── Contracts/                  ← interfaces públicas del módulo
│       └── ListadoQueryInterface.php
├── resources/
│   └── views/                      ← vistas con namespace 'listados::'
│       └── livewire/
│           └── listados/
│               └── por-curso.blade.php
├── routes/
│   └── web.php
├── database/
│   └── migrations/
│       └── 2026_04_25_120000_create_campos_listado_alumnos_table.php
└── config/
    └── listados.php
```

### Reglas de la estructura

- Todo el código PHP vive en `src/`. El namespace raíz del paquete es `Se\ModuloListados\`.
- Las vistas van en `resources/views/`. Se registran con el namespace `listados`
  (o el nombre del módulo). Desde Blade se referencian como `view('listados::livewire.listados.por-curso')`.
- Las migraciones en `database/migrations/` se publican con `loadMigrationsFrom`.
  Son **aditivas e idempotentes** (ver sección 5).
- `config/listados.php` contiene los flags configurables del módulo. Se publica
  al colegio con `php artisan vendor:publish --tag=listados-config`.

---

## 2. `composer.json` del paquete

```json
{
  "name": "se/modulo-listados",
  "description": "Módulo de listados de alumnos por curso y por materia.",
  "type": "library",
  "license": "proprietary",
  "version": "0.1.0",
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "livewire/livewire": "^4.2",
    "barryvdh/laravel-dompdf": "^3.1"
  },
  "autoload": {
    "psr-4": {
      "Se\\ModuloListados\\": "src/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Se\\ModuloListados\\ListadosServiceProvider"
      ]
    }
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
```

**Puntos importantes:**

- `"type": "library"` (no `"project"`).
- `"license": "proprietary"` porque es software privado.
- `extra.laravel.providers` habilita el **auto-discovery**: el colegio no necesita
  registrar el provider manualmente; Laravel lo detecta al correr `composer install`.
- Declarar en `require` solo las dependencias reales del módulo.
- No declarar dependencias de desarrollo (`require-dev`) salvo que el módulo tenga
  sus propios tests. PHPUnit/Pint ya viven en el repo del colegio.

---

## 3. `ListadosServiceProvider.php`

```php
<?php

namespace Se\ModuloListados;

use Illuminate\Support\ServiceProvider;

class ListadosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/listados.php',
            'listados'
        );
    }

    public function boot(): void
    {
        // Rutas del módulo (con middleware aplicado dentro del archivo de rutas)
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Vistas con namespace 'listados::*'
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'listados');

        // Migraciones (el colegio elige cuándo ejecutar php artisan migrate)
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publicar config para que el colegio pueda sobreescribirla
        $this->publishes([
            __DIR__.'/../config/listados.php' => config_path('listados.php'),
        ], 'listados-config');

        // Publicar vistas para que el colegio pueda sobreescribirlas
        // (alternativa al namespace prepend; ver 09-template-colegio.md)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/listados'),
        ], 'listados-views');
    }
}
```

**Sobre el registro de componentes Livewire:**

Livewire 4 hace auto-discovery de componentes por namespace. Para que los
componentes del paquete sean descubiertos, agregar en el `boot()`:

```php
use Livewire\Livewire;

// Registrar explícitamente si auto-discovery no los encuentra
Livewire::component('listados.por-curso', \Se\ModuloListados\Livewire\ListadoPorCurso::class);
```

---

## 4. `routes/web.php` del paquete

El archivo de rutas del módulo usa las mismas convenciones del sistema:

```php
<?php

use Illuminate\Support\Facades\Route;
use Se\ModuloListados\Http\Controllers\ListadoCursoPdfController;
use Se\ModuloListados\Livewire\ListadoPorCurso;

Route::middleware(['auth', 'school.context'])->group(function () {

    Route::get('/listados/por-curso', ListadoPorCurso::class)
        ->middleware('permiso:2')
        ->name('listados.por-curso');

    Route::get('/listados/por-curso/listado', ListadoCursoPdfController::class)
        ->middleware('permiso:2')
        ->name('listados.por-curso.pdf');
});
```

**Reglas:**

- Siempre dentro del grupo `['auth', 'school.context']`.
- Usar `middleware('permiso:N')` igual que en el sistema actual.
- Los nombres de rutas (`name()`) deben ser idénticos a los que usan los
  componentes y vistas del módulo (no cambiarlos al migrar).

---

## 5. Migraciones aditivas e idempotentes

**Regla fundamental:** las migraciones de los paquetes nunca deben romper una
instalación donde la tabla o columna ya exista. Siempre usar `hasTable` /
`hasColumn` antes de actuar.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campos_listado_alumnos')) {
            Schema::create('campos_listado_alumnos', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('idNivel');
                $table->string('columna', 80);
                $table->tinyInteger('visible')->default(1);
                $table->unsignedSmallInteger('orden')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Las migraciones down son solo informativas; nunca se ejecutan
        // automáticamente en producción.
        Schema::dropIfExists('campos_listado_alumnos');
    }
};
```

**Para agregar una columna a una tabla existente:**

```php
public function up(): void
{
    if (! Schema::hasColumn('campos_listado_alumnos', 'descripcion')) {
        Schema::table('campos_listado_alumnos', function (Blueprint $table) {
            $table->string('descripcion', 200)->nullable()->after('columna');
        });
    }
}
```

**Nunca usar `migrate:fresh`, `migrate:refresh`, `dropIfExists` en tablas legacy
ni `TRUNCATE`. Toda migración es aditiva.**

---

## 6. `config/listados.php`

```php
<?php

return [

    /*
     | Flags de features del módulo.
     | El colegio puede publicar esta config y cambiar valores sin tocar código.
     */
    'features' => [
        'por_curso'   => true,   // listado por curso (siempre activo)
        'por_materia' => true,   // listado por materia (algunos colegios no lo usan)
        'exportar_pdf' => true,
    ],

    /*
     | Número máximo de cursos seleccionables en el panel de "Listado por curso".
     */
    'max_cursos_seleccionados' => 30,

];
```

En Blade y PHP del paquete, leer siempre con `config('listados.features.por_materia', true)`.
El segundo argumento es el default para instalaciones que no publicaron la config.

---

## 7. Contratos públicos del módulo (`Contracts/`)

Si otro módulo o el colegio necesita interactuar con la lógica de este módulo,
exponer una interfaz en `Contracts/`. Nunca exponer la clase concreta directamente.

```php
<?php

namespace Se\ModuloListados\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ListadoQueryInterface
{
    /**
     * Devuelve los cursos disponibles para el contexto activo.
     *
     * @return Collection<int, \App\Models\Curso>
     */
    public function cursosDisponibles(): Collection;
}
```

El ServiceProvider enlaza la interfaz con la implementación concreta:

```php
// En ListadosServiceProvider::register()
$this->app->bind(
    \Se\ModuloListados\Contracts\ListadoQueryInterface::class,
    \Se\ModuloListados\Services\ListadoQueryService::class
);
```

Un colegio puede reemplazar la implementación sin tocar el paquete:

```php
// En TenantOverridesServiceProvider del colegio
$this->app->bind(
    \Se\ModuloListados\Contracts\ListadoQueryInterface::class,
    \App\Custom\Listados\Services\MiListadoQueryService::class
);
```

---

## 8. Versionado y CHANGELOG

### Reglas SemVer

| Tipo de cambio | Versión |
|---|---|
| Bugfix sin cambios de interfaz | PATCH: `0.1.1` → `0.1.2` |
| Feature nueva compatible hacia atrás | MINOR: `0.1.x` → `0.2.0` |
| Cambio de API, migración obligatoria, requiere update en el colegio | MAJOR: `0.x.y` → `1.0.0` |

Los `composer.json` de los colegios usan `^0.1` durante la fase pre-estable
(acepta minors). Cuando el paquete llega a `1.0.0`, los colegios pasan a `^1.0`.

### Formato de `CHANGELOG.md`

```markdown
# Changelog — se/modulo-listados

## [Unreleased]

## [0.2.0] — 2026-06-15

### Agregado
- Listado por materia (`ListadoPorMateria`).
- Ruta `/listados/por-materia`.

### Notas de deploy
- Esta versión no trae migraciones nuevas.
- Ejecutar `php artisan view:clear` y `php artisan config:cache`.

## [0.1.0] — 2026-05-06

### Inicial
- Listado por curso con selección de campos y exportación PDF.
- Tabla `campos_listado_alumnos` para parametrizar campos visibles.
```

La sección **"Notas de deploy"** es obligatoria en cada versión MINOR o MAJOR.
Detalla exactamente qué comandos correr en el VPS y si hay migraciones que
un humano debe ejecutar manualmente.

---

## 9. Workflow de publicación de una nueva versión

```bash
# En el repo del paquete

# 1. Hacer todos los cambios, commitear.
git add .
git commit -m "feat: agregar ListadoPorMateria"

# 2. Actualizar CHANGELOG.md con la nueva versión.
# 3. Crear el tag.
git tag v0.2.0

# 4. Push con tags.
git push origin main --tags
```

En el VPS del colegio que quiere la mejora:

```bash
# Actualizar solo el paquete modificado.
composer update se/modulo-listados

# Ver qué versión quedó instalada.
composer show se/modulo-listados

# Limpiar caches.
php artisan view:clear
php artisan config:cache

# Si hay migraciones nuevas (revisar CHANGELOG):
# php artisan migrate
```

---

## 10. Checklist antes de publicar una versión

- [ ] El ServiceProvider registra rutas, vistas, migraciones y config.
- [ ] Las migraciones son aditivas e idempotentes (`hasTable` / `hasColumn`).
- [ ] Las vistas usan el namespace del módulo (`listados::*`), no paths absolutos.
- [ ] Las rutas están dentro del grupo `['auth', 'school.context']`.
- [ ] Los componentes Livewire filtran por `schoolCtx()->idNivel` / `idTerlec`.
- [ ] No hay `DB::raw()` con input de usuario.
- [ ] Los modelos tienen `$table`, `$timestamps = false` y `$fillable` explícitos.
- [ ] `CHANGELOG.md` tiene la sección de la nueva versión con "Notas de deploy".
- [ ] El tag de Git coincide exactamente con la versión en `composer.json`.
- [ ] Probado en el repo de colegio piloto antes de anunciar la versión.
