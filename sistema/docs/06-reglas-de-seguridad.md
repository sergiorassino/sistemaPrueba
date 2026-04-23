# Reglas de Seguridad (Baseline Obligatorio)

> Estas reglas aplican a **todo módulo nuevo** y a cambios en módulos existentes.
> Son el estándar mínimo de seguridad del sistema.

---

## 1. Autenticación y Sesión

- Usar `auth` middleware para toda ruta interna.
- Cookies/sesión seguras (config Laravel): `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `same_site`.
- Regenerar sesión/token en login/logout (ya implementado).
- Dos guards separados: gestión (`profesores`) y autogestión (`legajos`).

---

## 2. Autorización (evitar acceso indebido)

- Todo ABM debe tener **chequeo de alcance por contexto** y/o permisos:
  - Si el módulo depende de `schoolCtx()`, **filtrar queries** por 
    `schoolCtx()->idNivel` / `idTerlec` según corresponda.
  - En operaciones por ID (editar/eliminar), **volver a consultar** el registro 
    con el mismo filtro (no confiar en IDs del cliente).
- Verificar permisos usando el modelo de cadena `0/1` de `profesores.permisos`
  contra `permisosusuarios.orden` (ver [03-autenticacion-y-permisos.md](03-autenticacion-y-permisos.md)).
- Si más adelante se implementan Policies/Gates, centralizar allí y llamar 
  desde Livewire (ej: `authorize()`).

---

## 3. Validación, Normalización y Seguridad de Datos

- Validar **siempre server-side** (`$this->validate()` o FormRequest).
- Normalizar entradas antes de guardar:
  - `trim()` en strings.
  - `strtoupper()` cuando corresponda (abreviaturas/códigos).
- Evitar mass-assignment peligroso:
  - Preferir `$fillable` explícito en modelos (no usar `$guarded = []` en modelos nuevos).
  - En updates/creates, pasar arrays con claves explícitas (no `->update($this->all())`).

---

## 4. Protección contra XSS

- En Blade, usar `{{ }}` (escape) siempre.
- Evitar `{!! !!}`; si fuese indispensable, sanitizar en backend primero.
- No interpolar HTML/JS con datos de usuario en atributos JS. Si se requiere, 
  castear/escapar.

---

## 5. SQL Injection

- Usar Eloquent/Query Builder con parámetros (bindings).
- Evitar `DB::raw()` con entrada de usuario.
- Si hay que usar `DB::raw()`, que sea **constante** y revisada.

---

## 6. Rate Limiting y Abuso

- Rate-limit en acciones sensibles (crear/editar/eliminar) en Livewire/Controllers.
- Límites por usuario con ventanas cortas:
  - Save/update: máx 30/min
  - Delete: máx 10/min

---

## 7. Errores y Logging

- No exponer trazas/SQL en producción (`APP_DEBUG=false`).
- Loggear eventos de ABM importantes (crear/editar/eliminar) sin datos sensibles.

---

## 8. Checklist por Módulo / PR

Antes de considerar completo un módulo o PR:

- [ ] ¿La consulta está filtrada por contexto (`schoolCtx`) cuando corresponde?
- [ ] ¿Acciones por ID revalidan alcance del registro?
- [ ] ¿Hay validación y normalización server-side?
- [ ] ¿No hay `DB::raw()` con input?
- [ ] ¿Blade escapa correctamente (sin `{!! !!}`)?
- [ ] ¿Rate limiting configurado en acciones sensibles?
- [ ] ¿Permisos verificados según modelo de cadena `0/1`?
