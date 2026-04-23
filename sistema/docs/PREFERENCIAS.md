## Preferencias (usuario / proyecto)

### Seguridad (obligatorio)

- Aplicar **medidas de seguridad de un sistema profesional** (PHP + MySQL) a los módulos desarrollados y a todos los futuros.
- Medidas mínimas esperadas por módulo:
  - Autenticación para todo lo interno.
  - Autorización (o al menos **control de alcance por contexto** cuando aplique, p.ej. `schoolCtx()`).
  - Validación server-side y normalización (`trim`, formatos).
  - Protección XSS (escape en Blade, evitar `{!! !!}`).
  - Evitar SQL injection (sin `raw` con input).
  - Rate limit en operaciones ABM sensibles.

### Preferencias de trabajo (para el asistente)

- Documentar y mantener estas preferencias en este archivo.
- Cuando aparezcan nuevas preferencias/restricciones, agregarlas aquí en el momento.
