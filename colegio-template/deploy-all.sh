#!/bin/bash
# deploy-all.sh — Actualiza todos los colegios en el VPS.
# Uso: sudo ./deploy-all.sh
#      sudo ./deploy-all.sh sanmartin bellavista   (solo esos colegios)
#
# Precondición: ejecutar como usuario con acceso a /var/www/*/
# Cada directorio debe tener su propio deploy.sh.
set -e

# Lista de colegios (editar según la instalación real)
# Pueden pasarse como argumentos: ./deploy-all.sh sanmartin bellavista
if [ $# -gt 0 ]; then
    COLEGIOS=("$@")
else
    COLEGIOS=(
        # Completar con los slugs reales de cada colegio:
        # "sanmartin"
        # "bellavista"
        # "lapaz"
        # "montecristo"
    )
fi

BASE_DIR="/var/www"
ERRORES=()
FECHA=$(date '+%d/%m/%Y %H:%M')

echo ""
echo "======================================================"
echo " deploy-all.sh — ${FECHA}"
echo " Colegios: ${#COLEGIOS[@]}"
echo "======================================================"

for COLEGIO in "${COLEGIOS[@]}"; do
    RUTA="${BASE_DIR}/${COLEGIO}"
    echo ""
    echo "------------------------------------------------------"
    echo " Desplegando: ${COLEGIO}"
    echo "------------------------------------------------------"

    if [ ! -d "${RUTA}" ]; then
        echo "  ERROR: directorio ${RUTA} no encontrado. Saltando."
        ERRORES+=("${COLEGIO}: directorio no encontrado")
        continue
    fi

    if [ ! -f "${RUTA}/deploy.sh" ]; then
        echo "  ERROR: ${RUTA}/deploy.sh no encontrado. Saltando."
        ERRORES+=("${COLEGIO}: deploy.sh no encontrado")
        continue
    fi

    cd "${RUTA}"
    if bash deploy.sh; then
        echo "  OK: ${COLEGIO} actualizado."
    else
        echo "  ERROR: fallo en deploy de ${COLEGIO}."
        ERRORES+=("${COLEGIO}: deploy.sh retornó error")
    fi
done

echo ""
echo "======================================================"
echo " Resumen — ${FECHA}"
echo "======================================================"
echo " Colegios procesados: ${#COLEGIOS[@]}"
echo " Errores: ${#ERRORES[@]}"

if [ ${#ERRORES[@]} -gt 0 ]; then
    echo ""
    echo " Colegios con error:"
    for ERR in "${ERRORES[@]}"; do
        echo "   - ${ERR}"
    done
    exit 1
fi

echo ""
echo " Todos los colegios actualizados correctamente."
echo ""
