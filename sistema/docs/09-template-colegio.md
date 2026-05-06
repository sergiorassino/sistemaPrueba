# Template: Repo de Colegio

> Referencia para crear cualquier nuevo repo `colegio-YYYY` o para convertir
> el repo actual en el primer colegio piloto.
>
> Ver también: `07-arquitectura-modular.md` para el contexto general y
> `08-template-paquete-modulo.md` para los paquetes que este repo consume.

---

## 1. Estructura de directorios

```
colegio-sanmartin/
├── composer.json                      ← paquetes y versiones
├── composer.lock                      ← fija exactamente qué está instalado
├── auth.json                          ← token GitHub (gitignored)
├── .env                               ← TENANT_SLUG, DB, APP_KEY, etc.
├── .env.example                       ← plantilla sin valores reales
├── .gitignore                         ← incluye auth.json, .env, vendor/, node_modules/
├── deploy.sh                          ← script de actualización del VPS
│
├── app/
│   ├── Custom/                        ← overrides del colegio (solo lo que difiere)
│   │   └── Listados/                  ← ejemplo: override del módulo Listados
│   │       ├── Livewire/
│   │       │   └── ListadoPorCurso.php    ← extiende la clase del paquete
│   │       └── views/
│   │           └── boton-exportar.blade.php  ← pisa la vista del paquete
│   └── Providers/
│       └── TenantOverridesServiceProvider.php
│
├── config/
│   └── tenant.php                     ← branding, flags de features, módulos activos
│
├── database/
│   └── migrations/
│       └── tenant/                    ← migraciones específicas del colegio
│           └── 2026_05_06_000000_add_beca_municipal_to_legajos.php
│
└── resources/
    └── custom/                        ← views de colegio para override por namespace
        └── listados/
            └── boton-exportar.blade.php
```

---

## 2. `composer.json` del repo de colegio

```json
{
  "name": "colegio/sanmartin",
  "description": "Instalación del sistema SE para el colegio San Martín.",
  "type": "project",
  "license": "proprietary",
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "livewire/livewire": "^4.2",
    "barryvdh/laravel-dompdf": "^3.1",
    "minishlink/web-push": "^10.0",
    "se/sistema-base": "^0.1",
    "se/modulo-listados": "^0.1",
    "se/modulo-comunicaciones": "^0.1",
    "se/modulo-disciplinario": "^0.1"
  },
  "require-dev": {
    "fakerphp/faker": "^1.23",
    "laravel/pint": "^1.13",
    "phpunit/phpunit": "^10.5",
    "spatie/laravel-ignition": "^2.4"
  },
  "repositories": [
    { "type": "vcs", "url": "git@github.com:USUARIO/sistema-base.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-listados.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-comunicaciones.git" },
    { "type": "vcs", "url": "git@github.com:USUARIO/modulo-disciplinario.git" }
  ],
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  },
  "scripts": {
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi"
    ],
    "post-update-cmd": [
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
    ]
  },
  "config": {
    "optimize-autoloader": true,
    "preferred-install": "dist",
    "sort-packages": true
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
```

**Para desarrollo local con path local (sin push/tag continuo):**

Reemplazar temporalmente el repositorio VCS por un path:

```json
"repositories": [
  { "type": "path", "url": "../modulo-listados" },
  { "type": "path", "url": "../modulo-comunicaciones" }
]
```

Con `"type": "path"`, Composer crea un symlink a la carpeta local. Los cambios
en el paquete se ven inmediatamente sin necesidad de commit ni tag.

---

## 3. `.env` mínimo

```dotenv
APP_NAME="Colegio San Martín"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://sanmartin.misitio.edu.ar

# Identificador único de este colegio — usado en logs, futuras migraciones tenant
TENANT_SLUG=sanmartin

# Base de datos del colegio
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanmartin_db
DB_USERNAME=sanmartin_user
DB_PASSWORD=

# Sesión
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

# Cache y cola
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
```

El archivo `.env.example` es la plantilla versionada (sin valores reales).
El `.env` real nunca se versiona.

---

## 4. `auth.json` — autenticación con GitHub privado

Archivo en la raíz del proyecto, **gitignored**. Dos opciones:

**Opción A — Personal Access Token (para desarrollo local y VPS con un solo dev):**

```json
{
  "github-oauth": {
    "github.com": "ghp_XXXXXXXXXXXXXXXXXXXX"
  }
}
```

El token necesita scope `repo` (acceso a repos privados).

**Opción B — SSH deploy key (recomendada para VPS de producción):**

No se necesita `auth.json`. En cambio:

1. Generar par de llaves en el VPS: `ssh-keygen -t ed25519 -C "vps-sanmartin" -f ~/.ssh/id_ed25519_sanmartin`
2. Agregar la clave pública como **deploy key** (solo lectura) en cada repo de
   paquete en GitHub (Settings → Deploy keys).
3. Configurar `~/.ssh/config` en el VPS:

```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_sanmartin
```

Con esto, `composer install` en el VPS funciona sin tokens ni contraseñas.

---

## 5. `config/tenant.php`

```php
<?php

return [

    /*
     | Datos institucionales del colegio.
     | Usados en encabezados, PDFs y notificaciones.
     */
    'nombre'    => 'Colegio San Martín',
    'slug'      => 'sanmartin',
    'localidad' => 'Ciudad de ejemplo',
    'logo'      => null,   // null = usar schoolLogoUrl() de la BD (ento.logo_path)

    /*
     | Módulos activos para este colegio.
     | Un módulo en false no registra sus rutas ni providers aunque esté instalado.
     | (Requiere lógica de activación en el AppServiceProvider del colegio)
     */
    'modulos' => [
        'listados'       => true,
        'comunicaciones' => true,
        'disciplinario'  => true,
        'cuotas'         => false,   // este colegio no usa el módulo de cuotas
    ],

    /*
     | Overrides de configuración de módulos específicos.
     | Estos valores sobreescriben los defaults del paquete.
     */
    'listados' => [
        'features' => [
            'por_materia' => false,   // sanmartin no usa listado por materia
        ],
    ],

    'comunicaciones' => [
        'familia_puede_responder' => true,
    ],

];
```

**Acceder en código:** `config('tenant.modulos.listados')`, `config('tenant.nombre')`, etc.

---

## 6. `TenantOverridesServiceProvider.php`

Este provider es el mecanismo central de overrides del colegio. Se registra en
`bootstrap/providers.php` (o `config/app.php` según la versión de Laravel).

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class TenantOverridesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         | OVERRIDES DE SERVICIOS / COMPONENTES LIVEWIRE
         |
         | Enlazar la clase del colegio en reemplazo de la del paquete.
         | Usar solo cuando hay lógica de negocio diferente, no solo visual.
         |
         | Ejemplo: este colegio tiene un ListadoPorCurso extendido que agrega
         | la columna 'beca_municipal' al listado.
         */
        // $this->app->bind(
        //     \Se\ModuloListados\Livewire\ListadoPorCurso::class,
        //     \App\Custom\Listados\Livewire\ListadoPorCurso::class,
        // );
    }

    public function boot(): void
    {
        /*
         | OVERRIDES DE VISTAS POR NAMESPACE
         |
         | prependNamespace hace que Laravel busque primero en la carpeta local
         | del colegio antes de usar la vista del paquete.
         |
         | Cualquier archivo en resources/custom/listados/ que tenga el mismo
         | nombre que una vista del paquete la pisa automáticamente.
         */
        View::prependNamespace('listados', resource_path('custom/listados'));
        // View::prependNamespace('comunicaciones', resource_path('custom/comunicaciones'));

        /*
         | MERGE DE CONFIG DEL COLEGIO SOBRE LA DEL PAQUETE
         |
         | Si el colegio publicó la config del paquete, Laravel ya la usa.
         | Si en cambio se quiere mergear desde tenant.php sin publicar:
         */
        $this->mergeOverriddenModuleConfigs();
    }

    private function mergeOverriddenModuleConfigs(): void
    {
        // Mergear config de módulos desde tenant.php sobre la del paquete.
        // Ejemplo: 'tenant.listados' → merge en 'listados'
        $modulos = ['listados', 'comunicaciones', 'disciplinario'];

        foreach ($modulos as $modulo) {
            $overrides = config("tenant.$modulo");
            if (is_array($overrides)) {
                config([$modulo => array_merge(
                    config($modulo, []),
                    $overrides,
                )]);
            }
        }
    }
}
```

**Registrar en `bootstrap/providers.php`:**

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenantOverridesServiceProvider::class,  // ← agregar
];
```

---

## 7. Override de componente Livewire (Nivel 3)

Cuando se necesita cambiar lógica de negocio (no solo visual):

```php
<?php

// app/Custom/Listados/Livewire/ListadoPorCurso.php
namespace App\Custom\Listados\Livewire;

use Se\ModuloListados\Livewire\ListadoPorCurso as Base;

class ListadoPorCurso extends Base
{
    /**
     * Sanmartín agrega 'beca_municipal' a los campos por defecto del catálogo.
     * El catálogo del paquete no tiene esta columna porque es específica del colegio.
     */
    protected function columnasDefault(): array
    {
        return [...parent::columnasDefault(), 'legajos.beca_municipal'];
    }
}
```

Y en `TenantOverridesServiceProvider::register()`:

```php
$this->app->bind(
    \Se\ModuloListados\Livewire\ListadoPorCurso::class,
    \App\Custom\Listados\Livewire\ListadoPorCurso::class,
);
```

**Regla:** el override extiende la clase del paquete y sobreescribe solo el
método mínimo necesario. No copiar toda la clase; solo el método que cambia.

---

## 8. Override de vista (Nivel 2)

Para cambiar solo algo visual sin tocar PHP:

```blade
{{-- resources/custom/listados/boton-exportar.blade.php --}}
{{-- Pisa la vista 'listados::boton-exportar' del paquete. --}}
{{-- Este colegio prefiere llamarlo "Bajar Excel" en lugar de "Exportar". --}}

<a href="{{ $pdfUrl }}" class="se-btn-primary" @if(!$puedeGenerar) disabled @endif>
    Bajar Excel
</a>
```

El archivo vive en `resources/custom/listados/boton-exportar.blade.php`.
El `prependNamespace` del provider hace que Laravel lo encuentre antes de buscar
en el paquete. No hay configuración extra.

---

## 9. Migraciones específicas del colegio (drift de BD)

Si el colegio tiene tablas o columnas que no están en el esquema canónico, las
migraciones viven en `database/migrations/tenant/`.

```php
<?php

// database/migrations/tenant/2026_05_06_000000_add_beca_municipal_to_legajos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('legajos', 'beca_municipal')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->string('beca_municipal', 50)->nullable()->after('obs');
            });
        }
    }

    public function down(): void
    {
        // Informativo; no ejecutar en producción.
    }
};
```

Para ejecutar solo las migraciones tenant:

```bash
php artisan migrate --path=database/migrations/tenant
```

Para ejecutar todo (paquetes + tenant):

```bash
php artisan migrate
```

---

## 10. `deploy.sh` — script de actualización del VPS

Guardar en la raíz del repo del colegio. Hacer `chmod +x deploy.sh`.

```bash
#!/bin/bash
# deploy.sh — ejecutar en la raíz del repo en el VPS
# Uso: ./deploy.sh
set -e

echo "=== [$(date '+%d/%m/%Y %H:%M')] Iniciando deploy ==="

echo "--- Actualizando código ---"
git pull origin main

echo "--- Actualizando dependencias Composer ---"
composer install --no-dev --optimize-autoloader

echo "--- Limpiando caches ---"
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "--- Compilando assets ---"
npm ci --omit=dev
npm run build

echo "=== Deploy completado ==="
echo ""
echo "ATENCIÓN: Si esta versión incluye migraciones nuevas, ejecutar manualmente:"
echo "  php artisan migrate"
echo "Revisar el CHANGELOG del paquete actualizado para confirmarlo."
```

**Para un script que itere todos los colegios en el VPS** (se ejecuta con
permisos que tienen acceso a todas las carpetas):

```bash
#!/bin/bash
# deploy-all.sh — ejecutar como root o con sudo en el VPS
COLEGIOS=(sanmartin bellavista lapaz ...)

for c in "${COLEGIOS[@]}"; do
    echo ""
    echo "=============================="
    echo " Desplegando: $c"
    echo "=============================="
    cd /var/www/$c
    ./deploy.sh
done

echo ""
echo "=== Todos los colegios actualizados ==="
```

---

## 11. `.gitignore` del repo de colegio

```
/vendor/
/node_modules/
/public/hot
/public/storage
/storage/*.key
.env
auth.json
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
/.fleet
/.idea
/.vscode
```

Verificar que `auth.json` y `.env` estén en `.gitignore` **antes del primer commit**.

---

## 12. Reglas para `app/Custom/`

1. **Solo overrides reales.** Si el código es idéntico al del paquete, no copiar.
   El punto de extensión debe estar en el paquete (método `protected`, interfaz, config).

2. **Nombre de carpeta = nombre del módulo.** `app/Custom/Listados/` para el
   módulo `se/modulo-listados`. Fácil de encontrar.

3. **Documentar el motivo.** Cada clase o vista custom debe tener un comentario
   de cabecera que explique por qué existe y cuándo podría retirarse:

   ```php
   /**
    * Override específico de Sanmartín.
    * Agrega 'beca_municipal' a los campos del listado.
    * Retirar cuando ese campo entre al catálogo canónico del paquete (issue #42).
    */
   ```

4. **Revisar overrides en cada MAJOR del paquete.** Si el paquete llegó a v1.0,
   revisar si el override sigue siendo necesario o si el nuevo catálogo ya lo
   resuelve por configuración.

5. **Si 3 o más colegios tienen el mismo override, promover al paquete.** El
   override deja de ser excepción y pasa a ser feature configurable del core.
