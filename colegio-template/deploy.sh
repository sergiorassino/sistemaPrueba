#!/bin/bash
# deploy.sh — Actualización del sistema en el VPS de este colegio.
# Uso: ./deploy.sh
#
# Precondiciones:
#   - Ejecutar desde la raíz del repo del colegio en el VPS.
#   - PHP, Composer y Node.js en el PATH.
#   - auth.json presente (o SSH deploy key configurada) para acceder a repos privados.
#
# IMPORTANTE: Las migraciones NO se ejecutan automáticamente.
# Ver instrucciones al final del script.
set -e

FECHA=$(date '+%d/%m/%Y %H:%M')
echo ""
echo "======================================================"
echo " Deploy — ${APP_NAME:-este colegio}"
echo " ${FECHA}"
echo "======================================================"

echo ""
echo "--- [1/5] Actualizando código ---"
git pull origin main

echo ""
echo "--- [2/5] Actualizando dependencias Composer ---"
composer install --no-dev --optimize-autoloader

echo ""
echo "--- [3/5] Limpiando caches ---"
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo ""
echo "--- [4/5] Compilando assets ---"
npm ci --omit=dev
npm run build

echo ""
echo "--- [5/5] Reiniciando colas (si aplica) ---"
# Descomentar si se usa php-fpm o supervisor para colas:
# php artisan queue:restart

echo ""
echo "======================================================"
echo " Deploy completado: ${FECHA}"
echo "======================================================"
echo ""
echo "  ATENCION: Si esta version incluye migraciones nuevas,"
echo "  revisar el CHANGELOG de los paquetes actualizados y"
echo "  ejecutar manualmente:"
echo ""
echo "    php artisan migrate"
echo ""
echo "  Para migraciones solo del colegio (tenant):"
echo "    php artisan migrate --path=database/migrations/tenant"
echo ""
