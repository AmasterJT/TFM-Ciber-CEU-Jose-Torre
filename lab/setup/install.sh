#!/usr/bin/env bash

set -euo pipefail

APP_NAME="portal_pyme"

BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_SRC="${BASE_DIR}/portal_pyme"
DB_FILE="${BASE_DIR}/database/portal_pyme.sql"

WEB_ROOT="/var/www/html"
APP_DST="${WEB_ROOT}/${APP_NAME}"

DB_NAME="portal_pyme"
DB_USER="portal"
DB_PASS="portal123"
DB_HOST="127.0.0.1"

echo "[+] Instalando ${APP_NAME}"
echo "[+] Base del proyecto: ${BASE_DIR}"

# -------- Comprobaciones --------

if [[ ! -d "${APP_SRC}" ]]; then
    echo "[-] No existe el directorio de la aplicación: ${APP_SRC}"
    exit 1
fi

if [[ ! -f "${DB_FILE}" ]]; then
    echo "[-] No existe el fichero SQL: ${DB_FILE}"
    exit 1
fi

command -v mysql >/dev/null 2>&1 || { echo "[-] mysql no está instalado"; exit 1; }
command -v rsync >/dev/null 2>&1 || { echo "[-] rsync no está instalado"; exit 1; }

echo "[+] Comprobaciones OK"

# -------- Crear destino web --------

echo "[+] Creando directorio destino..."
sudo mkdir -p "${APP_DST}"

# -------- Copiar aplicación --------
# Excluye uploads para no sobreescribir contenido subido en laboratorio

echo "[+] Copiando aplicación a ${APP_DST} ..."
sudo rsync -av --delete \
    --exclude 'uploads/*' \
    --exclude '.gitkeep' \
    "${APP_SRC}/" "${APP_DST}/"

# -------- Crear uploads --------

echo "[+] Asegurando directorio uploads..."
sudo mkdir -p "${APP_DST}/uploads"
sudo touch "${APP_DST}/uploads/.gitkeep"

# -------- Permisos --------

echo "[+] Ajustando permisos..."
sudo chown -R www-data:www-data "${APP_DST}"
sudo chmod -R 755 "${APP_DST}"
sudo chmod 775 "${APP_DST}/uploads"

# -------- Base de datos --------

echo "[+] Creando base de datos y usuario MySQL..."

sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME};

CREATE USER IF NOT EXISTS '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'${DB_HOST}';

FLUSH PRIVILEGES;
SQL

# -------- Importación SQL --------

echo "[+] Importando base de datos desde ${DB_FILE} ..."
mysql -u "${DB_USER}" -p"${DB_PASS}" -h "${DB_HOST}" "${DB_NAME}" < "${DB_FILE}"

# -------- Comprobación final --------

echo "[+] Verificando despliegue..."
if [[ -f "${APP_DST}/config/db.php" ]]; then
    echo "[+] Aplicación copiada correctamente"
else
    echo "[-] No se encontró ${APP_DST}/config/db.php"
    exit 1
fi

echo
echo "[+] Instalación completada correctamente"
echo "[+] URL local esperada:"
echo "    http://127.0.0.1/${APP_NAME}/"
echo
echo "[+] URL en tu laboratorio:"
echo "    http://<IP-VM>/${APP_NAME}/"
echo
echo "[+] Credenciales MySQL configuradas:"
echo "    Host: ${DB_HOST}"
echo "    DB:   ${DB_NAME}"
echo "    User: ${DB_USER}"
echo "    Pass: ${DB_PASS}"
