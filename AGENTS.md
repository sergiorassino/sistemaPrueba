# Agente / IA — políticas del repositorio

El código de la aplicación está en **`sistema/`**.

**Toda herramienta de asistencia (Cursor, etc.) debe seguir las políticas allí definidas**, en particular sobre **no ejecutar escrituras en la base de datos desde la terminal del agente** y sobre **incluir al final de la respuesta el SQL (o comando) listo para copiar** cuando el cambio implique migraciones o datos en BD (detalle en `sistema/AGENTS.md` y `sistema/docs/06-reglas-de-seguridad.md` §9.1).

Leer en este orden:

1. **`sistema/AGENTS.md`** — resumen operativo para IAs
2. **`sistema/docs/06-reglas-de-seguridad.md`** — sección 9 (BD y ejecución)
3. **`sistema/docs/08-menus-de-navegacion.md`** — Menú de Secretaría / Alumnos / Docentes (nombres oficiales)
4. **`sistema/.cursor/rules/`** — reglas del proyecto (`alwaysApply` donde corresponda)

Así la restricción **viaja con el clone del repo** y no depende de que cada colaborador configure reglas globales en Cursor.
