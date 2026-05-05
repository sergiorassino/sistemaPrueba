# Instrucciones para asistentes de código (Cursor, Copilot, etc.)

Este archivo está **en el repositorio**: aplica a **todas** las personas y herramientas que trabajen sobre este código, con o sin reglas personales en Cursor.

## Base de datos (obligatorio)

**No ejecutar** desde la herramienta de terminal del asistente nada que **escriba** en la base de datos, **aunque el usuario lo pida**. Incluye, entre otros:

- `php artisan tinker` … con `delete` / `update` / `insert`
- `php artisan migrate*`, `db:*`, `db:seed`, imports, cliente `mysql`
- Scripts PHP one-shot (`php -r`, etc.) que usen Eloquent o `DB::`

**Sí hacer:** entregar **solo SQL** (o el comando Artisan) **en el chat como texto** para que un humano lo revise y ejecute en su cliente; y guardar migraciones/código en archivos **sin** invocarlos para aplicar el cambio en la BD.

Detalle y matices: `docs/06-reglas-de-seguridad.md` sección **9**.

## Resto del baseline

Seguridad, permisos, `schoolCtx`, Blade, etc.: `docs/06-reglas-de-seguridad.md` y las reglas en `.cursor/rules/` (por ejemplo `seguridad-php-mysql-laravel.mdc`, `preferencias-del-proyecto.mdc`).
