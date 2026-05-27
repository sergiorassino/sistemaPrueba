# Menús de navegación (terminología oficial)

Este documento fija los **nombres** que usamos en el equipo para los tres portales con sidebar.
Evita confusiones entre “docentes” como usuarios de `profesores`, el grupo **DOCENTES** del menú de secretaría
y el futuro **menú de Docentes** para profesores en el aula.

---

## Resumen

| Nombre oficial | Qué es | Layout Blade | Login / guard |
|----------------|--------|--------------|---------------|
| **Menú de Secretaría** | Sistema grande de gestión (legajos, calificaciones, exámenes, configuración, etc.) | `resources/views/layouts/app.blade.php` | `/loginUsuario` · guard `web` · tabla `profesores` |
| **Menú de Alumnos** | Autogestión familia / estudiante | `resources/views/layouts/alumno.blade.php` | `/loginEstudiante` · guard `alumno` · tabla `legajos` |
| **Menú de Docentes** | Portal reducido: pocas tareas para el profesor en el aula | `resources/views/layouts/docente.blade.php` | Mismo login que secretaría (`profesores`); rutas bajo prefijo `/portal-docente` (ver abajo) |

**Cantidad de sidebars implementados:** 3 layouts. Solo el de Secretaría usa grupos acordeón extensos; Alumnos y Docentes son listas cortas (Docentes aún en armado).

---

## 1. Menú de Secretaría

- **Audiencia:** secretaría, preceptores, administración, personal con permisos amplios en `profesores.permisos`.
- **Antes se decía:** “gestión”, “staff”, “layout app”, “sistema grande”.
- **Rutas:** prefijo raíz (`/dashboard`, `/abm/…`, `/calificacionesSecundario/…`, etc.) con middleware `auth` + `school.context`.
- **Contexto de sesión:** `schoolCtx()` (nivel + ciclo lectivo elegidos en el login o en el context-switcher del sidebar).
- **Sidebar:** ~13 grupos desplegables + enlace “Manual del sistema”. Detalle de grupos: ver historial de `layouts/app.blade.php` o el manual PDF.

**Importante:** el grupo del sidebar llamado **“DOCENTES”** (legajos del docente, asignación por materia, inasistencias docentes desde secretaría) **pertenece al menú de Secretaría**, no al menú de Docentes.

---

## 2. Menú de Alumnos

- **Audiencia:** familia / estudiante (`legajos`).
- **Antes se decía:** “autogestión”, “portal alumno”, “familia”, `layouts/alumno`.
- **Rutas:** prefijo `/alumnos/…` · middleware `auth:alumno` + `student.context`.
- **Contexto:** `studentCtx()`; ciclo desde `ento.idTerlecVerNotas`.
- **Enlaces típicos:** consulta de calificaciones, informe de inasistencias, cuaderno de comunicados, push, preferencias; aranceles externos si el tenant lo configura.

Orientación UI: **mobile-first** (ver [01-descripcion-general.md](01-descripcion-general.md)).

---

## 3. Menú de Docentes

- **Audiencia:** profesores que solo necesitan **pocas acciones** (carga/consulta acotada, comunicados propios, etc.) sin el menú completo de secretaría.
- **Estado:** layout operativo; ítems según permisos (calificaciones/cuaderno en secundario, comunicación institucional, etc.).
- **Layout:** `resources/views/layouts/docente.blade.php`.
- **Rutas (convención):** prefijo URL `/portal-docente` · nombres de ruta `portalDocente.*`  
  (no usar solo `/docentes` porque ya existe el módulo de secretaría `docentes.inasistencias.*`).

**Login:** mismo que Secretaría (`/loginUsuario`, tabla `profesores`). Tras autenticarse:

| `profesores.IdTipoProf` | Destino |
|-------------------------|---------|
| **6** (rol «Profesor/a» en `profesortipo`) | `portalDocente.home` — Menú de Docentes |
| Cualquier otro (Directivo, Secretario, Preceptor, Administrador, Gabinete de orientación, etc.) | `dashboard` — Menú de Secretaría |

Implementación: `App\Support\ProfesorMenuPortal` y middleware `menu.portal:secretaria` / `menu.portal:docente`.
Un profesor (`IdTipoProf = 6`) no puede abrir rutas de secretaría (redirección al portal); el resto no puede abrir `/portal-docente`.

**Pantalla inicial placeholder:** `portalDocente.home` → vista `resources/views/portal-docente/home.blade.php`.

### Autogestión Docente (rol mixto: Preceptor + Profesor)

Algunos usuarios tienen **dos roles** en el sistema (p. ej. Preceptor y Profesor/a). Como `profesores.IdTipoProf` es único, el login carga el rol "mayor" (Preceptor) y lleva al Menú de Secretaría; quedan sin acceso a sus materias del Menú de Docentes.

Para cubrir ese caso, el Menú de Secretaría incluye un ítem **"Autogestión Docente"** al final del sidebar, **antes** del "Manual del sistema". Se muestra **solo cuando** el usuario actual tiene `IdTipoProf ≠ 6` **y** existe al menos un registro en `ppc` para algún legajo con el **mismo DNI** en el **nivel y ciclo lectivo activos** (`schoolCtx()->idNivel`, `schoolCtx()->idTerlec`).

Al activarse (POST `autogestion.docente.activar` → `AutogestionDocenteController`):

1. Se busca el legajo en `profesores` con el mismo DNI y nivel del contexto que tenga PPC para el ciclo (prioridad: `IdTipoProf = 6`).
2. Si ese legajo es **distinto** al usuario autenticado, se cambia la identidad de Auth (`Auth::loginUsingId`) y se reescribe `schoolCtx()->idProfesor` para que el Menú de Docentes encuentre sus materias y permisos.
3. Se setea el override de sesión `school.menu_portal_override = 'docente'` (constantes en `ProfesorMenuPortal::SESSION_OVERRIDE_KEY` / `OVERRIDE_DOCENTE`).
4. Se redirige a `portalDocente.home`.

Con el override activo, `ProfesorMenuPortal::usaMenuDocentes()` devuelve `true` aunque `IdTipoProf ≠ 6`; el middleware `menu.portal:secretaria` redirige a `portalDocente.home` y el de `menu.portal:docente` deja pasar. **Para volver al Menú de Secretaría hay que cerrar sesión y reingresar** (el logout invalida la sesión y limpia el override).

---

## Glosario — qué decir y qué evitar

| Decir | Evitar (ambiguo) |
|-------|------------------|
| Menú de Secretaría | “menú app”, “sidebar staff”, “gestión” a secas |
| Menú de Alumnos | “menú alumno/familia”, “autogestión” sin aclarar portal |
| Menú de Docentes | “menú profesor” mezclado con grupo DOCENTES de secretaría |
| Grupo **DOCENTES** (secretaría) | “menú docentes” — es solo una sección del menú de Secretaría |

En código y PRs, preferir comentarios del tipo:

```blade
{{-- Menú de Secretaría: grupo Calificaciones --}}
```

```blade
{{-- Menú de Docentes: ítem pendiente de definir --}}
```

---

## Referencias en el repo

| Tema | Archivo |
|------|---------|
| Logins y permisos | [03-autenticacion-y-permisos.md](03-autenticacion-y-permisos.md) |
| Tooltips y grupos del sidebar de Secretaría | [05-preferencias-y-convenciones.md](05-preferencias-y-convenciones.md) §6 |
| Identidad visual (sidebar) | [04-identidad-visual.md](04-identidad-visual.md) · regla `.cursor/rules/ui-front-se.mdc` |
| Rutas portal docente | `routes/web.php` (bloque `portal-docente`) |

---

## Historial

- **2026-05-22:** Definición de los tres nombres oficiales; scaffold del menú de Docentes (`layouts/docente.blade.php`, `portalDocente.home`).
- **2026-05-22:** Redirección post-login y separación de rutas por `IdTipoProf` (6 → Docentes; demás → Secretaría).
- **2026-05-23:** Menú de Docentes: sección Comunicación institucional (`portalDocente.comunicaciones.*`, mismos componentes que secretaría).
- **2026-05-26:** "Autogestión Docente" en el Menú de Secretaría (antes del Manual) para usuarios con rol mixto (Preceptor + Profesor): override de sesión `school.menu_portal_override` y cambio de identidad si hay legajo paralelo con PPC. Logout para volver a Secretaría.
