# Preferencias y Convenciones de Desarrollo

> Este archivo concentra las preferencias del usuario/proyecto y las convenciones
> de código que deben respetarse en todos los módulos, presentes y futuros.

---

## 1. Seguridad (obligatorio)

Aplicar **medidas de seguridad de un sistema profesional** (PHP + MySQL + Laravel)
a todos los módulos. Ver [06-reglas-de-seguridad.md](06-reglas-de-seguridad.md) para el detalle completo.

### Resumen de medidas mínimas por módulo

- ✅ Autenticación para todo lo interno (`auth` middleware)
- ✅ Autorización / control de alcance por contexto (`schoolCtx()`)
- ✅ Validación server-side y normalización (`trim`, formatos)
- ✅ Protección XSS (escape en Blade, evitar `{!! !!}`)
- ✅ Evitar SQL injection (sin `raw` con input de usuario)
- ✅ Rate limit en operaciones ABM sensibles

---

## 2. Base de datos

- **NO modificar** tablas existentes de la base legacy.
- Crear migraciones **aditivas** (agregar columnas, tablas nuevas).
- Crear migraciones para **instalación limpia** del sistema nuevo.
- Modelos Eloquent con `$table` explícito, sin timestamps automáticos.
- `$fillable` explícito en todos los modelos — nunca `$guarded = []`.

---

## 3. Estilo de implementación

- Preferir cambios **seguros y conservadores** (hardening) sin romper compatibilidad.
- Donde falten roles/permisos, aplicar al menos **control de alcance por contexto** 
  (ej. `schoolCtx()`).
- Toda acción ABM (crear/editar/eliminar) debe revalidar el alcance del registro 
  consultando con el filtro de contexto.

---

## 4. Convenciones de código

### PHP / Laravel

- Nombres de clases en PascalCase.
- Componentes Livewire organizados por dominio: `Livewire/Auth/`, `Livewire/Abm/`.
- Vistas Blade en mirror: `livewire/auth/`, `livewire/abm/`.
- Helper global `schoolCtx()` para acceder al contexto de sesión.
- Mensajes de validación en español.
- Comentarios en español cuando aclaren lógica de negocio.

### Frontend / Blade

- Usar `{{ }}` siempre (escape XSS).
- Tailwind CSS 4 para estilos.
- Colores del design system (ver [04-identidad-visual.md](04-identidad-visual.md)).
- Layout responsivo, mobile-first para autogestión.

### Grillas / listados anchos (convención)

- Para listados tipo planilla con muchas columnas (patrón `.gf-*`), **no centrar** el contenedor con `.gf-wrap` si puede haber overflow horizontal: al cambiar el ancho disponible (ej. sidebar), se pueden ocultar columnas.
- Usar siempre un wrapper con scroll horizontal y alineación a la izquierda:

```blade
<div class="w-full overflow-x-auto">
    <div class="flex justify-start">
        <div class="gf min-w-[1180px]">
            <!-- gf-head / gf-row -->
        </div>
    </div>
</div>
```

---

## 5. Varios colegios (tenants)

- Un despliegue (o entorno local) por colegio: `TENANT_SLUG` + BD propia. Ver [07-versionado-de-modulos-por-tenant.md](07-versionado-de-modulos-por-tenant.md) (personalización real: config + BD + permisos; **sin** paquetes Composer por módulo).
- Preferir parametrización en tablas (`solapas_legajo`, `campos_legajo`, permisos, `ento`) antes de ramas de código por colegio.
- Overrides en `config/tenants/{slug}.php` solo para lo que no corresponda en BD.

---

## 6. Menú lateral, dashboard y módulos por nivel educativo

Cuando un módulo aplica solo a **secundario**, **primario** o **inicial** (o existirán variantes por nivel, como boletines o calificaciones), debe quedar explícito en código, menú y documentación. No usar nombres genéricos ambiguos (`Boletines` a secas) si el alcance es de un solo nivel.

### Sidebar (`resources/views/layouts/app.blade.php`)

- Cada enlace del menú lleva atributo **`title`** (tooltip al pasar el mouse), con el mismo criterio que el resto del sistema:
  - Nombre del módulo con **nivel entre paréntesis** cuando corresponda: `(secundario)`, `(primario)`, `(inicial)`.
  - Descripción breve opcional separada por ` · `.
  - **Versión del módulo al final:** `v1.0` (referencia visual; no implica conmutación por config).
- Ejemplo actual: `title="Boletines (secundario) · Informe de progreso escolar v1.0"`.
- El texto visible del ítem (`<span class="truncate">`) debe incluir el nivel si pronto coexistirán ítems homónimos (p. ej. `Boletines (secundario)` y, más adelante, `Boletines (primario)`).

### Rutas, PHP y nombres

- Namespaces, carpetas Livewire, prefijos de ruta y nombres de ruta (`boletinesSecundario.*`, `BoletinesSecundario\`, etc.) deben incluir el nivel.
- Al agregar el mismo tipo de módulo para otro nivel: **ítem de menú y ruta propios**; no reutilizar un único enlace genérico.

### Dashboard

- `title` y `hint` en `dashboard.blade.php` deben alinear con el sidebar (nivel en el título; versión o alcance en el `hint` cuando aplique).

### Referencia

- Calificaciones secundario: tooltips `… (secundario) v1.0` en el grupo CALIFICACIONES.
- Boletines secundario: `boletinesSecundario.index`, tooltip y etiqueta `Boletines (secundario)`.

---

## 7. Calificaciones — promedio anual (secundario)

**Regla obligatoria:** no calcular promedios de calificaciones en ninguna parte del sistema salvo que se pida explícitamente en una tarea o decisión de producto documentada.

### Único lugar autorizado (por ahora)

- **Carga manual de calificaciones (secundario):** `Livewire/CalificacionesSecundario/CargaCalificacionesSecundario.php`
- Al salir de un campo de módulo (`ic01`…`ic28`, blur/change), tras persistir la nota, se ejecuta `syncPromedioAnual()` y se guarda el resultado en `calificaciones.calif` (columna Pr. Final, solo lectura en la UI).
- La lógica numérica vive en `App\Support\PromedioAnualCalificacionesSecundario::calcular()` y **solo** debe llamarse desde ese `syncPromedioAnual()`.

### Qué no debe calcular promedios

- Planilla PDF de calificaciones: mostrar `calif` de BD (vacío si no hay valor).
- Boletines, consulta de calificaciones (alumno o personal), exportaciones e impresiones: leer `calif` persistido.
- Sincronización GE/CIDI: importar `calif` del archivo; no recalcular desde `ic**`.
- Cualquier vista, job o script nuevo: no inferir Pr. Final desde Eval/JIS salvo nueva autorización explícita.

### Presentación sin promedio

- En la planilla PDF se puede usar `PromedioAnualCalificacionesSecundario::bloqueDesaprobado()` solo para **resaltar** bloques desaprobados; no sustituye ni recalcula `calif`.

### Extender el cálculo en el futuro

Si se agrega otro flujo que deba calcular (p. ej. batch nocturno o otro nivel), documentarlo aquí y centralizar la llamada a `calcular()` — no duplicar fórmulas en Blade ni en importadores.

---

## 8. Convenciones de documentación

- Mantener la carpeta `docs/` actualizada con cada cambio significativo.
- Cuando aparezcan nuevas preferencias/restricciones, agregarlas en este archivo.
- Los archivos de documentación se numeran secuencialmente para facilitar la lectura.
- Personalización multi-colegio: [07-versionado-de-modulos-por-tenant.md](07-versionado-de-modulos-por-tenant.md).
