# Autenticación y Permisos

---

## 1. Portales y logins

Hay **tres menús de navegación** (ver [08-menus-de-navegacion.md](08-menus-de-navegacion.md)) y **dos logins** independientes hoy:

| Menú | Login |
|------|--------|
| **Menú de Secretaría** | `/loginUsuario` → `profesores` |
| **Menú de Docentes** | Mismo login que Secretaría (redirección por rol: pendiente) |
| **Menú de Alumnos** | `/loginEstudiante` → `legajos` |

---

### 1.1 Login de Secretaría (tabla `profesores`)

Aplica a: secretarios, administración, preceptores y también profesores que entren por este login.
Alcance habitual: **Menú de Secretaría** (`layouts/app.blade.php`). Los profesores con rol acotado irán al **Menú de Docentes** cuando esté definida la redirección.

| Campo          | Origen                           |
|----------------|----------------------------------|
| Usuario        | `profesores.dni`                 |
| Contraseña     | `profesores.pwrd`                |
| Nivel          | Selección en formulario de login |
| Ciclo lectivo  | Selección en formulario de login |

**Implementación actual:**
- Componente Livewire: `App\Livewire\Auth\Login`
- Auth provider custom: `App\Auth\ProfesorUserProvider`
- Al hacer login, se establece el `SchoolContext` (idProfesor, idNivel, idTerlec) en sesión.
- Middleware `EnsureSchoolContext` protege todas las rutas autenticadas.

**Menú según rol (`IdTipoProf` en `profesortipo`):**

| Rol en legajo (ejemplos) | `IdTipoProf` típico | Menú tras login |
|--------------------------|---------------------|-----------------|
| Profesor/a | **6** | **Menú de Docentes** (`/portal-docente`, `portalDocente.*`) |
| Directivo, Secretario, Preceptor, Administrador, Gabinete de orientación | distinto de 6 | **Menú de Secretaría** (`/dashboard`, rutas con `menu.portal:secretaria`) |

Lógica centralizada en `App\Support\ProfesorMenuPortal::ID_TIPO_PROFESOR_AULA` (valor **6**).
Middleware `menu.portal` impide cruzar portales (un profesor no navega ABM de secretaría por URL directa).
Permisos `profesores.permisos` siguen aplicando **dentro** del menú de Secretaría.

---

### 1.2 Login del Menú de Alumnos (tabla `legajos`)

Portal completamente separado del login de Secretaría.

| Campo          | Origen                           |
|----------------|----------------------------------|
| Usuario        | `legajos.dni`                    |
| Contraseña     | `legajos.pwrd`                   |
| Nivel          | No se selecciona                 |
| Ciclo lectivo  | Se toma de `ento.idTerlecVerNotas` |

**Estado:** Implementado (guard `alumno`, layout `alumno.blade.php`).

**Diferencias clave con el login de Secretaría:**
- Sin selección de nivel ni ciclo lectivo en el formulario.
- El ciclo lectivo se determina automáticamente desde `ento.idTerlecVerNotas`.
- Requiere su propio auth provider, guard y rutas separadas.

---

## 2. Manejo de Contraseñas — Modo Híbrido

El sistema usa un **esquema híbrido** de contraseñas por razones legacy:

```
┌─────────────────────┐     ┌──────────────────────────────┐
│ Usuarios existentes │────►│ Contraseña en texto plano    │
│ (legacy)            │     │ Comparación: hash_equals()   │
└─────────────────────┘     └──────────────────────────────┘

┌─────────────────────┐     ┌──────────────────────────────┐
│ Usuarios nuevos o   │────►│ Hash bcrypt ($2y$ / $2a$)    │
│ blanqueo de clave   │     │ Comparación: password_verify()│
└─────────────────────┘     └──────────────────────────────┘
```

**Lógica de validación** (en `ProfesorUserProvider::validateCredentials`):
1. Si `$stored` empieza con `$2y$` o `$2a$` → usar `password_verify()`.
2. Si no → comparar con `hash_equals()` (texto plano legacy).

**Regla para código nuevo:**
- Al crear usuario nuevo o blanquear contraseña → guardar con `bcrypt()`.
- Mismo criterio aplica a ambas tablas (`profesores` y `legajos`).

---

## 3. Ciclo Lectivo — Comportamiento en Sesión

```
┌──────────┐   selecciona    ┌──────────────────────┐
│  Login   │───────────────►│  Sesión: idTerlec     │
│          │  nivel+terlec   │  (persiste toda la    │
└──────────┘                 │   navegación)         │
                             └──────────────────────┘
                                       │
                                       ▼
                             ┌──────────────────────┐
                             │  Toda consulta se     │
                             │  filtra por idTerlec   │
                             │  + idNivel de sesión   │
                             └──────────────────────┘
```

- En el login, el usuario selecciona el ciclo lectivo (por defecto: el actual).
- Toda la navegación y operación queda **acotada a ese ciclo lectivo**.
- Para cambiar de ciclo lectivo: desde el login o desde un control
  visible en el dashboard/página de inicio.
- El ciclo lectivo activo debe **persistir en sesión** durante toda la navegación.

**Implementación actual:** `App\Support\SchoolContext`
- Almacena `idProfesor`, `idNivel`, `idTerlec` en la sesión.
- Helper global `schoolCtx()` retorna la instancia.

---

## 4. Modelo de Permisos

### Tablas involucradas

- `profesores.permisos` — varchar con cadena de `0`s y `1`s.
- `permisosusuarios` — catálogo donde cada registro tiene un campo `orden`.

### Mecánica

Cada posición de la cadena en `profesores.permisos` corresponde al campo `orden`
de un registro en `permisosusuarios`:

```
permisos = "111111111111111..."
            │││
            ││└─ orden=2 → tiene permiso
            │└── orden=1 → tiene permiso
            └─── orden=0 → tiene permiso

permisos = "001111111111111..."
            │││
            ││└─ orden=2 → tiene permiso
            │└── orden=1 → sin permiso
            └─── orden=0 → sin permiso
```

- `'1'` en posición N = tiene permiso del ítem con `orden = N`
- `'0'` en posición N = sin permiso

### Verificación obligatoria

El sistema debe verificar permisos en:
- Cada **ruta** (middleware o check en controlador)
- Cada **componente Livewire** (en `mount()` o con policy)
- Cada **acción** (crear, editar, eliminar)

### Ejemplo de helper sugerido

```php
function tienePermiso(int $orden): bool
{
    $permisos = schoolCtx()->profesor()?->permisos ?? '';
    return isset($permisos[$orden]) && $permisos[$orden] === '1';
}
```
