# Instrucciones para asistentes de código (Cursor, Copilot, etc.)

Este archivo está **en el repositorio**: aplica a **todas** las personas y herramientas que trabajen sobre este código, con o sin reglas personales en Cursor.

## Base de datos (obligatorio)

**No ejecutar** desde la herramienta de terminal del asistente nada que **escriba** en la base de datos, **aunque el usuario lo pida**. Incluye, entre otros:

- `php artisan tinker` … con `delete` / `update` / `insert`
- `php artisan migrate*`, `db:*`, `db:seed`, imports, cliente `mysql`
- Scripts PHP one-shot (`php -r`, etc.) que usen Eloquent o `DB::`

**Sí hacer:** entregar **solo SQL** (o el comando Artisan) **en el chat como texto** para que un humano lo revise y ejecute en su cliente; y guardar migraciones/código en archivos **sin** invocarlos para aplicar el cambio en la BD.

**Cierre de tareas (IAs y colaboradores):** si el cambio implica **esquema o datos** (nueva migración, seeder de datos de negocio, script de alineación, `UPDATE`/`DELETE` documentados, etc.), al **final** de la respuesta o del PR debe figurar un bloque **listo para copiar** con:

1. Las sentencias **SQL** equivalentes al `up()` de la migración (u operación de datos), en el orden correcto respecto de FKs si aplica; y  
2. Una **advertencia breve** de alcance (tablas afectadas, irreversibilidad).

Si lo habitual en el entorno es aplicar migraciones con Artisan en lugar de SQL manual, puede indicarse además `php artisan migrate` como alternativa, **sin** ejecutarlo desde la herramienta del asistente.

Detalle y matices: `docs/06-reglas-de-seguridad.md` sección **9**.

## Promedio de calificaciones (secundario)

No calcular promedios salvo en **carga manual** (`CargaCalificacionesSecundario`, al guardar `ic01..ic28` → `calif`). El resto del sistema solo **lee** `calificaciones.calif`. Detalle: `docs/05-preferencias-y-convenciones.md` §7.

## PDFs

- **Nuevos:** **TCPDF** (`tecnickcom/tcpdf`), clase en `app/Support/`, controlador `*PdfController`. No usar DomPDF ni vistas Blade de layout. Regla: `.cursor/rules/pdf-tcpdf-nuevos.mdc`. Referencia: `ActaVolantePreviosTcpdf`.
- **Legacy (DomPDF):** tablas con columnas de distinto ancho: **porcentaje inline en cada `th` y `td`** (`min-width:0; overflow:hidden`), tabla al **100%**, `table-layout: fixed`. No confiar solo en `colgroup`. Regla: `.cursor/rules/pdf-dompdf-columnas.mdc`; detalle en `docs/05-preferencias-y-convenciones.md` §8.

## Selects de año lectivo (`terlec`)

Todo desplegable de ciclo lectivo: **orden decreciente** (año más reciente primero). Usar `Terlec::paraSelector()` o `Terlec::ordenado()`; en Livewire con re-render frecuente, `livewire:components.terlec-selector`. Regla Cursor: `.cursor/rules/terlec-selector-orden.mdc`.

## Resto del baseline

Seguridad, permisos, `schoolCtx`, Blade, etc.: `docs/06-reglas-de-seguridad.md` y las reglas en `.cursor/rules/` (por ejemplo `seguridad-php-mysql-laravel.mdc`, `preferencias-del-proyecto.mdc`).
