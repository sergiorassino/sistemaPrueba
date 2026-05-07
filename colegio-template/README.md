# colegio-template

Template para crear el repo de un nuevo colegio en el sistema SE.

Ver documentación completa: `sistema/docs/09-template-colegio.md`

---

## Cómo usar este template

### 1. Crear el repo del nuevo colegio

```bash
# Clonar o copiar este template:
cp -r colegio-template colegio-NOMBRE
cd colegio-NOMBRE
git init
git add .
git commit -m "feat: scaffold inicial colegio NOMBRE"
```

O directamente crear un nuevo repo en GitHub usando este directorio como base.

### 2. Instalar Laravel en el directorio

Este template **no incluye** el scaffold completo de Laravel (vendor/, bootstrap/, etc.)
porque se obtiene vía Composer desde `sistema-base` (aún pendiente de extraer) o
copiando desde el repo piloto `sistema/`.

Opción más directa: clonar el repo `sistemaprueba` y reemplazar los archivos de
configuración del colegio con los de este template.

```bash
# Alternativa mientras sistema-base no está publicado:
git clone https://github.com/sergiorassino/sistemaprueba colegio-NOMBRE
cd colegio-NOMBRE
# Reemplazar composer.json, config/tenant.php, .env y providers
```

### 3. Configurar el colegio

1. Copiar `.env.example` → `.env` y completar todos los valores:
   - `APP_NAME`, `TENANT_SLUG`, `DB_DATABASE`, etc.

2. Actualizar `composer.json`:
   - Cambiar `"name": "colegio/nombre-colegio"` por el slug real.
   - Agregar o quitar módulos en `require` según los que use este colegio.

3. Actualizar `config/tenant.php`:
   - `nombre`, `slug`, `localidad`
   - Activar/desactivar módulos en `modulos`
   - Agregar overrides de config si el colegio difiere del default del paquete

4. Registrar `TenantOverridesServiceProvider` en `bootstrap/providers.php`:
   ```php
   return [
       App\Providers\AppServiceProvider::class,
       App\Providers\TenantOverridesServiceProvider::class,
   ];
   ```

5. Crear `auth.json` (gitignored) con el token de GitHub:
   ```bash
   cp auth.json.example auth.json
   # Editar auth.json con el token real (scope: repo)
   ```

6. Instalar dependencias:
   ```bash
   composer install
   npm install
   npm run build
   php artisan key:generate
   ```

### 4. Ejecutar migraciones

```bash
# Migraciones del core + paquetes:
php artisan migrate

# Migraciones exclusivas del colegio (si hay):
php artisan migrate --path=database/migrations/tenant
```

> **Regla:** las migraciones nunca se ejecutan automáticamente desde el asistente.
> Las ejecuta un humano en el VPS revisando el CHANGELOG primero.

---

## Estructura de overrides

### Nivel 1 — Configuración (sin código)

Editar `config/tenant.php`. No se toca PHP del paquete.

### Nivel 2 — Vista (solo Blade)

Agregar archivos en `resources/custom/{modulo}/`.
El nombre de archivo debe coincidir con el de la vista del paquete.
`TenantOverridesServiceProvider` ya tiene el `prependNamespace` configurado.

### Nivel 3 — Lógica de negocio

Crear la clase en `app/Custom/{Modulo}/Livewire/MiComponente.php` extendiendo la del paquete.
Descomentar el `$this->app->bind(...)` correspondiente en `TenantOverridesServiceProvider::register()`.

---

## Deploy en el VPS

```bash
# Dar permisos de ejecución la primera vez:
chmod +x deploy.sh deploy-all.sh

# Actualizar este colegio:
./deploy.sh

# Actualizar todos los colegios (desde el servidor):
sudo ./deploy-all.sh
```

---

## Comando de inventario de drift

Para comparar la BD de este colegio contra la referencia:

```bash
php artisan se:drift-report --reference=ia_demo --compare-with=NOMBRE_BD
php artisan se:drift-report --reference=ia_demo --compare-with=NOMBRE_BD --format=markdown
```

Ver resultados completos en `sistema/docs/10-inventario-drift.md`.
