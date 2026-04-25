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

## 5. Convenciones de documentación

- Mantener la carpeta `docs/` actualizada con cada cambio significativo.
- Cuando aparezcan nuevas preferencias/restricciones, agregarlas en este archivo.
- Los archivos de documentación se numeran secuencialmente para facilitar la lectura.
