# Versionado de Módulos por Tenant

> Estrategia para personalizar funcionalidades por colegio sin afectar a los demás.
> Todo módulo nuevo que requiera diferenciación entre colegios debe seguir este patrón.

---

## 1. El problema que resuelve

El sistema atiende múltiples colegios (tenants) que parten de un código base común pero necesitan ir diferenciándose con el tiempo. El riesgo central es: **modificar algo para el colegio A sin tener certeza de que no se afectan los colegios B y C**.

La solución es encapsular cada variante funcional en un **paquete Laravel independiente** (path package en monorepo), instalar ese paquete solo en el repo del colegio que lo necesita, y declarar en la configuración del tenant qué versión debe usar.

---

## 2. Principio de diseño

- Todo módulo nace en `v1.0` dentro de `app/` (código compartido del sistema base).
- Cuando un colegio necesita una variante, se crea un paquete nuevo (`v2.0`, `v3.0`, etc.) en `packages/`.
- El colegio A requiere el paquete v2 en su `composer.json`. Los colegios B y C no lo incluyen: **el código v2 no existe en sus repos**.
- El sidebar y el dashboard leen la configuración del tenant (`tenantConfig()`) y apuntan a la ruta correcta según la versión activa.

**Nunca se modifica el módulo v1 para hacer cambios de v2.** Cada versión es un paquete separado, con sus propias rutas, componentes y vistas.

---

## 3. Estructura de un paquete versionado

```
sistema/packages/modulo-{nombre}-v2/
├── composer.json
├── routes/
│   └── web.php
├── src/
│   ├── {Nombre}V2ServiceProvider.php
│   └── Livewire/
│       └── {Componente}V2Index.php
│       └── (otros componentes...)
└── resources/
    └── views/
        └── livewire/
            └── {nombre}-v2/
                └── index.blade.php
                └── (otras vistas...)
```

### `composer.json` del paquete

```json
{
    "name": "se/modulo-{nombre}-v2",
    "type": "library",
    "version": "2.0.0",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "livewire/livewire": "^4.2"
    },
    "autoload": {
        "psr-4": {
            "Se\\Modulo{Nombre}V2\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Se\\Modulo{Nombre}V2\\{Nombre}V2ServiceProvider"
            ]
        }
    }
}
```

### `ServiceProvider`

```php
class {Nombre}V2ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', '{nombreV2}');

        Livewire::component('{nombreV2}.index', Livewire\{Componente}V2Index::class);
    }
}
```

### Rutas

Las rutas v2 tienen nombres únicos y URLs únicas, sin colisión con v1:

```php
// v1 (en app/): route('seguimiento.disciplinario')  → /seguimiento/disciplinario
// v2 (paquete): route('disciplinarioV2.index')       → /seguimiento/disciplinario-v2
```

---

## 4. Configuración por tenant

### 4.1 Clave en `config/tenant.php` (default global)

Cada módulo versionable declara su clave en el archivo base:

```php
'disciplinario' => [
    // 'v1.0' usa el módulo en app/  → route('seguimiento.disciplinario')
    // 'v2.0' usa el paquete         → route('disciplinarioV2.index')
    'version' => 'v1.0',
],
```

El default es siempre `v1.0`. Todos los colegios que no declaren otra cosa usan el módulo base.

### 4.2 Override en `config/tenants/{slug}.php` (solo lo que difiere)

Solo el colegio que necesita la versión nueva declara el override:

```php
// config/tenants/montecristo.php
return [
    'nombre' => 'Colegio Montecristo',

    'disciplinario' => [
        'version' => 'v2.0',   // usa el paquete se/modulo-disciplinario-v2
    ],

    'sidebar' => [
        'modulos' => [
            // ... otros módulos en v1.0 ...
            'disciplinario' => 'v2.0',  // etiqueta de versión en tooltip del sidebar
        ],
    ],
];
```

Los colegios B y C no tienen esta clave → heredan `v1.0` del base → no usan el paquete v2.

---

## 5. Registrar el paquete en `composer.json`

En el `composer.json` del colegio A (el que usa v2):

```json
{
    "require": {
        "se/modulo-{nombre}-v2": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "packages/modulo-{nombre}-v2",
            "options": { "symlink": true }
        }
    ]
}
```

Los repos de B y C no incluyen esta dependencia. El paquete directamente no existe en su instalación.

Instalar el paquete:

```bash
composer require "se/modulo-{nombre}-v2:@dev" --no-interaction
```

---

## 6. Enganche en sidebar y dashboard

### Sidebar (`resources/views/layouts/app.blade.php`)

Para cada módulo versionable, el sidebar:
1. Lee la versión activa con `tenantConfig('{modulo}.version', 'v1.0')`
2. Lanza `RuntimeException` si la versión pedida no tiene su ruta registrada (falla explícita y rápida)
3. Muestra el link a la ruta correspondiente

```php
@php
    $version = tenantConfig('disciplinario.version', 'v1.0');
    if ($version === 'v2.0' && ! Route::has('disciplinarioV2.index')) {
        throw new \RuntimeException("Sidebar: tenant solicita Disciplinario v2.0 pero la ruta no existe.");
    }
@endphp

@if ($version === 'v2.0')
    <a href="{{ route('disciplinarioV2.index') }}" ...>Seguimiento Disciplinario</a>
@else
    <a href="{{ route('seguimiento.disciplinario') }}" ...>Seguimiento Disciplinario</a>
@endif
```

El cálculo del grupo activo de Alpine también contempla ambas familias de rutas:

```php
disciplinario: {{ (str_starts_with($route ?? '', 'seguimiento.disciplinario') || str_starts_with($route ?? '', 'disciplinarioV2.')) ? 'true' : 'false' }},
```

### Dashboard (`resources/views/dashboard.blade.php`)

Mismo patrón:

```php
$version = tenantConfig('disciplinario.version', 'v1.0');
$dashboardLinks[] = [
    'title' => 'Seguimiento disciplinario',
    'href'  => $version === 'v2.0'
        ? route('disciplinarioV2.index')
        : route('seguimiento.disciplinario'),
    'icon'  => 'shield',
];
```

---

## 7. Módulos versionados existentes

| Módulo | v1.0 (base, en `app/`) | v2.0 (paquete) | Clave de config |
|---|---|---|---|
| Listados por curso | `route('listados.por-curso')` | `route('listadosV2.por-curso')` | `listados.por_curso_version` |
| Listados por curso | `route('listadoPorCurso.v1_2')` | — | `listados.por_curso_version = v1.2` |
| Seguimiento disciplinario | `route('seguimiento.disciplinario')` | `route('disciplinarioV2.index')` | `disciplinario.version` |

**Paquetes en `packages/`:**

| Paquete | Versión | Descripción |
|---|---|---|
| `se/modulo-listado-por-curso-v12` | v1.2 | Listado simple (apellido, nombre, DNI) sin PDF |
| `se/modulo-listados-v2` | v2.0 | Listado con búsqueda interactiva |
| `se/modulo-disciplinario-v2` | v2.0 | Seguimiento disciplinario — variante personalizable |

---

## 8. Cuándo crear un paquete v2 (criterio)

Crear un paquete nuevo **solo cuando**:

- Un colegio pide una funcionalidad que **modifica la lógica o la UI** de un módulo existente.
- El cambio no puede resolverse con configuración simple en `config/tenants/{slug}.php`.
- Se quiere **garantía absoluta** de que los demás colegios no se ven afectados.

No crear paquetes por anticipación. Si el módulo aún no tiene solicitudes de variantes, queda en `app/` hasta que surja la necesidad real.

---

## 9. Flujo de trabajo para un cambio en colegio A

1. **Crear** el paquete `packages/modulo-{nombre}-v2/` con la nueva funcionalidad.
2. **Registrar** el path repository y el `require` en el `composer.json` del repo de A.
3. **Ejecutar** `composer require "se/modulo-{nombre}-v2:@dev"`.
4. **Declarar** la versión en `config/tenants/{slug-de-A}.php`.
5. **Enganchar** en sidebar y dashboard (agregar el `@if` de versión).
6. **Verificar** que B y C no tienen la dependencia ni la clave en su config → usan v1 sin cambios.

---

## 10. Limpieza de archivos de tenant

Los archivos `config/tenants/{slug}.php` deben declarar **solo lo que difiere del default**:

- Si una clave tiene el mismo valor que en `config/tenant.php`, **no declararla** en el tenant.
- Si un bloque entero coincide con los defaults, **no incluirlo**.
- El principio: leer el archivo de tenant debe responder a la pregunta "¿en qué es diferente este colegio?", no repetir lo que ya está en el base.
