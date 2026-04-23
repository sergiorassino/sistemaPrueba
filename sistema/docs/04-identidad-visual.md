# Identidad Visual y Design System

---

## 1. Paleta de Colores

Fuente: `/SE/PALETA COLORES LOGO.PNG`

| Color        | Hex       | Nombre     | Uso recomendado                           |
|--------------|-----------|------------|-------------------------------------------|
| Jet          | `#333333` | Oscuro     | Texto principal, sidebar fondo            |
| Dark Cyan    | `#40848D` | Primario   | Botones, links, acentos principales       |
| Moonstone    | `#739FA5` | Secundario | Hover, bordes activos, íconos             |
| White        | `#FFFFFF` | Blanco     | Fondos principales, texto sobre oscuro    |
| Light Blue   | `#C1D7DA` | Suave      | Fondos secundarios, badges, separadores   |

### Escala extendida sugerida (derivada de la paleta)

```css
/* Primario (Dark Cyan #40848D) */
--primary-50:  #edf5f6;
--primary-100: #d1e6e9;
--primary-200: #a3cdd3;
--primary-300: #739fa5;  /* Moonstone */
--primary-400: #5a9199;
--primary-500: #40848d;  /* Dark Cyan — principal */
--primary-600: #366f76;
--primary-700: #2c5a60;
--primary-800: #224549;
--primary-900: #183033;

/* Neutros (Jet #333333) */
--neutral-50:  #f5f5f5;
--neutral-100: #e5e5e5;
--neutral-200: #cccccc;
--neutral-300: #b3b3b3;
--neutral-400: #999999;
--neutral-500: #666666;
--neutral-600: #4d4d4d;
--neutral-700: #404040;
--neutral-800: #333333;  /* Jet — principal */
--neutral-900: #1a1a1a;

/* Acento claro (Light Blue #C1D7DA) */
--accent-50:  #f4f8f9;
--accent-100: #e5eff0;
--accent-200: #c1d7da;  /* Light Blue — principal */
--accent-300: #a8c8cc;
--accent-400: #8fb9be;
--accent-500: #76aab0;
```

---

## 2. Logos Disponibles

Ubicación: `/SE/` (raíz del proyecto)

| Archivo | Variante                                      | Recomendación                    |
|---------|-----------------------------------------------|----------------------------------|
| `1.png` | Ícono "SE" solo, fondo color, opaco           | Favicon, ícono compacto          |
| `2.png` | Ícono "SE" solo, fondo claro, suave           | Variante clara                   |
| `3.png` | Logo completo vertical (ícono + texto)         | Página de login, splash          |
| `4.png` | Logo completo horizontal (ícono + texto)       | **Sidebar header (recomendado)** |
| `5.png` | Logo completo horizontal, variante compacta    | Header reducido, mobile          |

### Logo elegido para el sistema

**Logo 4** (`/SE/4.png`) — formato horizontal con ícono y texto
"SISTEMAS ESCOLARES – Soluciones Informáticas para Escuelas".

**Razón:** El formato horizontal se adapta al header del sidebar sin desperdiciar
espacio vertical. Incluye el nombre completo, ideal para una interfaz de gestión
institucional.

**Usos secundarios:**
- Login / splash → Logo 3 (vertical, más prominente)
- Favicon → Logo 1 (solo ícono)
- Sidebar colapsado / mobile → Logo 1 (solo ícono)

---

## 3. Tipografía

Recomendación: **Inter** (Google Fonts) — moderna, legible, profesional.

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
```

---

## 4. Componentes del Design System

### Botones

| Variante  | Fondo        | Texto    | Hover          | Uso                    |
|-----------|--------------|----------|----------------|------------------------|
| Primary   | `#40848D`    | `#FFF`   | `#366F76`      | Acción principal       |
| Secondary | `transparent`| `#40848D`| `#EDF5F6`      | Acción secundaria      |
| Danger    | `#DC2626`    | `#FFF`   | `#B91C1C`      | Eliminar               |
| Ghost     | `transparent`| `#666`   | `#F5F5F5`      | Acciones terciarias    |

### Sidebar

| Elemento          | Color                              |
|-------------------|------------------------------------|
| Fondo             | `#333333` (Jet)                    |
| Texto             | `#FFFFFF`                          |
| Ítem activo fondo | `#40848D` (Dark Cyan)              |
| Ítem hover        | `rgba(64,132,141,0.2)`             |
| Separadores       | `rgba(255,255,255,0.1)`            |

### Estados

| Estado   | Color      | Uso                          |
|----------|------------|------------------------------|
| Success  | `#059669`  | Confirmaciones, guardado OK  |
| Warning  | `#D97706`  | Alertas, validaciones        |
| Error    | `#DC2626`  | Errores, eliminación         |
| Info     | `#40848D`  | Información (= primario)     |
