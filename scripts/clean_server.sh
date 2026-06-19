#!/usr/bin/env bash
set -euo pipefail

LAB_DIR="/opt/tfm-lab"
SCRIPT_DIR="/opt/scripts"
SUDOERS_FILE="/etc/sudoers.d/tfm-lab-dev"

echo "[*] Limpiando despliegue del laboratorio TFM..."

if [[ "$EUID" -ne 0 ]]; then
    echo "[!] Ejecuta este script como root:"
    echo "    sudo ./reset_tfm_lab.sh"
    exit 1
fi

echo "[*] Deteniendo contenedores del laboratorio..."

if [[ -d "$LAB_DIR" && -f "$LAB_DIR/docker-compose.yml" ]]; then
    cd "$LAB_DIR"
    docker compose down -v --remove-orphans || true
else
    echo "[!] No existe $LAB_DIR/docker-compose.yml. Saltando docker compose down."
fi

echo "[*] Eliminando contenedores huérfanos del laboratorio..."

docker rm -f tfm_web tfm_db 2>/dev/null || true

echo "[*] Eliminando imágenes del laboratorio si existen..."

docker image rm tfm-lab-web 2>/dev/null || true
docker image rm tfm_lab-web 2>/dev/null || true

echo "[*] Eliminando redes Docker del laboratorio..."

docker network rm tfm-lab_tfm_lab 2>/dev/null || true
docker network rm tfm_lab_tfm_lab 2>/dev/null || true

echo "[*] Eliminando volúmenes Docker asociados al laboratorio..."

docker volume ls -q | grep -E 'tfm|portal|mysql' | xargs -r docker volume rm 2>/dev/null || true

echo "[*] Limpiando recursos Docker no usados..."

docker system prune -f >/dev/null || true

echo "[*] Eliminando directorios del laboratorio..."

rm -rf "$LAB_DIR"
rm -rf "$SCRIPT_DIR"

echo "[*] Eliminando sudoers vulnerable del laboratorio..."

rm -f "$SUDOERS_FILE"

echo "[*] Reiniciando SSH..."

systemctl restart ssh 2>/dev/null || systemctl restart sshd 2>/dev/null || true

echo
echo "[+] Limpieza completada."
echo
echo "No se han eliminado usuarios ni grupos."
echo "Se conservan:"
echo "  - usuario victima"
echo "  - usuario dev"
echo "  - grupo developers"
echo "  - /home/victima"
echo "  - /home/dev"
echo
echo "Ahora puedes volver a desplegar con:"