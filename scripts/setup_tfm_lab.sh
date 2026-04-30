#!/usr/bin/env bash
set -euo pipefail


# ============================================================
# TFM Red Team Lab - Instalador automático de la máquina víctima
# Autor: Jose Torre
#
# Uso:
#   chmod +x setup_tfm_lab.sh
#   sudo ./setup_tfm_lab.sh
#   sudo ./setup_tfm_lab.sh --skip-install
#
# Este script prepara la VM víctima Ubuntu:
#   - valida el sistema (Ubuntu)
#   - instala Docker y Docker Compose Plugin (salvo con --skip-install)
#   - copia el laboratorio a /opt/tfm-lab
#   - configura permisos del portal
#   - crea usuario dev y grupo developers
#   - habilita SSH
#   - crea credenciales simuladas en shared/
#   - configura sudo vulnerable para PATH hijacking
#   - levanta la web vulnerable en Docker
#
# IMPORTANTE:
#   Ejecutar solo en la máquina víctima del laboratorio.
# ============================================================


LAB_DIR="/opt/tfm-lab"
REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEV_USER="dev"
DEV_PASS="dev123"
DEV_GROUP="developers"
VICTIM_USER="victima"
SCRIPT_DIR="/opt/scripts"
BACKUP_SCRIPT="${SCRIPT_DIR}/backup.sh"
SUDOERS_FILE="/etc/sudoers.d/tfm-lab-dev"
SSH_CONFIG="/etc/ssh/sshd_config"


print_info() {
    echo -e "\033[1;34m[*] $1\033[0m"
}

print_ok() {
    echo -e "\033[1;32m[+] $1\033[0m"
}

print_warn() {
    echo -e "\033[1;33m[!] $1\033[0m"
}

print_error() {
    echo -e "\033[1;31m[!] $1\033[0m"
}


require_root() {
    if [[ "${EUID}" -ne 0 ]]; then
        print_error "Este script debe ejecutarse como root: sudo ./setup_tfm_lab.sh"
        exit 1
    fi
}


require_ubuntu() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        if [[ "${ID}" != "ubuntu" ]]; then
            print_error "Este script está pensado solo para Ubuntu (${ID})."
            exit 1
        fi
        print_ok "Sistema validado: Ubuntu ${VERSION_ID}"
    else
        print_error "/etc/os-release no encontrado; este script está pensado para Ubuntu."
        exit 1
    fi
}


detect_repo_layout() {
    print_info "Detectando estructura del repositorio..."

    if [[ -d "${REPO_DIR}/lab" ]]; then
        SOURCE_LAB="${REPO_DIR}/lab"
    elif [[ -f "${REPO_DIR}/docker-compose.yml" ]]; then
        SOURCE_LAB="${REPO_DIR}"
    else
        print_error "No se ha encontrado la carpeta lab/ ni docker-compose.yml."
        print_error "Ejecuta el script desde el repositorio del TFM."
        exit 1
    fi

    if [[ ! -f "${SOURCE_LAB}/docker-compose.yml" ]]; then
        print_error "No existe docker-compose.yml en ${SOURCE_LAB}"
        exit 1
    fi

    if [[ ! -d "${SOURCE_LAB}/app/portal_pyme" ]]; then
        print_error "No existe app/portal_pyme en ${SOURCE_LAB}"
        exit 1
    fi

    print_ok "Laboratorio detectado en: ${SOURCE_LAB}"
}


install_dependencies() {
    print_info "Instalando dependencias base..."

    apt-get update
    apt-get install -y ca-certificates curl gnupg lsb-release openssh-server sudo

    if ! command -v docker >/dev/null 2>&1; then
        print_info "Instalando Docker desde el repositorio oficial..."

        install -m 0755 -d /etc/apt/keyrings

        if [[ ! -f /etc/apt/keyrings/docker.gpg ]]; then
            curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
                | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
            chmod a+r /etc/apt/keyrings/docker.gpg
        fi

        UBUNTU_CODENAME="$(. /etc/os-release && echo "${VERSION_CODENAME}")"

        echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu ${UBUNTU_CODENAME} stable" \
            > /etc/apt/sources.list.d/docker.list

        apt-get update
        apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    else
        print_ok "Docker ya está instalado."
    fi

    systemctl enable docker
    systemctl start docker

    print_ok "Dependencias instaladas."
}


prepare_lab_directory() {
    print_info "Preparando ${LAB_DIR}..."

    mkdir -p "${LAB_DIR}"

    # Copia limpia del laboratorio, preservando la estructura app/, docker/, db/, shared/, docker-compose.yml.
    rsync -a --delete \
        --exclude ".git" \
        --exclude "*.log" \
        "${SOURCE_LAB}/" "${LAB_DIR}/"

    mkdir -p "${LAB_DIR}/shared"
    mkdir -p "${LAB_DIR}/app/portal_pyme/uploads"

    print_ok "Laboratorio copiado a ${LAB_DIR}"
}


configure_users_and_groups() {
    print_info "Configurando usuarios y grupos..."

    if ! getent group "${DEV_GROUP}" >/dev/null; then
        groupadd "${DEV_GROUP}"
        print_ok "Grupo creado: ${DEV_GROUP}"
    else
        print_ok "Grupo ${DEV_GROUP} ya existe."
    fi

    if ! id "${VICTIM_USER}" >/dev/null 2>&1; then
        useradd -m -s /bin/bash -g "${DEV_GROUP}" "${VICTIM_USER}"
        echo "${VICTIM_USER}:victima123" | chpasswd
        print_ok "Usuario creado: ${VICTIM_USER}"
    else
        usermod -g "${DEV_GROUP}" "${VICTIM_USER}" || true
        print_ok "Usuario ${VICTIM_USER} ya existe."
    fi

    if ! id "${DEV_USER}" >/dev/null 2>&1; then
        useradd -m -s /bin/bash -g "${DEV_GROUP}" "${DEV_USER}"
        echo "${DEV_USER}:${DEV_PASS}" | chpasswd
        print_ok "Usuario creado: ${DEV_USER}"
    else
        usermod -g "${DEV_GROUP}" "${DEV_USER}"
        echo "${DEV_USER}:${DEV_PASS}" | chpasswd
        print_ok "Usuario ${DEV_USER} actualizado."
    fi

    # Permitir a victima gestionar Docker si existe como usuario de administración del laboratorio.
    usermod -aG docker "${VICTIM_USER}" || true

    print_ok "Usuarios y grupos configurados."
}


configure_permissions() {
    print_info "Configurando permisos del portal..."

    chown -R "${VICTIM_USER}:${DEV_GROUP}" "${LAB_DIR}"
    chmod -R 775 "${LAB_DIR}/app/portal_pyme"

    # El directorio shared simula un recurso mal gestionado con credenciales expuestas.
    chmod -R 775 "${LAB_DIR}/shared"

    cat > "${LAB_DIR}/shared/dev_credentials.txt" <<EOF
Usuario: ${DEV_USER}
Password: ${DEV_PASS}
Servicio: SSH
Host: 192.168.66.10
Nota: credenciales internas de desarrollo almacenadas incorrectamente en texto plano.
EOF

    chown -R "${VICTIM_USER}:${DEV_GROUP}" "${LAB_DIR}/shared"
    chmod 664 "${LAB_DIR}/shared/dev_credentials.txt"

    print_ok "Permisos configurados."
}


configure_ssh() {
    print_info "Configurando SSH..."

    systemctl enable ssh
    systemctl start ssh

    # Asegura autenticación por contraseña para el laboratorio.
    if grep -qE "^#?PasswordAuthentication" "${SSH_CONFIG}"; then
        sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication yes/' "${SSH_CONFIG}"
    else
        echo "PasswordAuthentication yes" >> "${SSH_CONFIG}"
    fi

    if grep -qE "^#?PermitRootLogin" "${SSH_CONFIG}"; then
        sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' "${SSH_CONFIG}"
    else
        echo "PermitRootLogin no" >> "${SSH_CONFIG}"
    fi

    systemctl restart ssh

    print_ok "SSH habilitado para el usuario ${DEV_USER}."
}


configure_vulnerable_sudo() {
    print_info "Configurando script vulnerable y sudoers..."

    mkdir -p "${SCRIPT_DIR}"

    cat > "${BACKUP_SCRIPT}" <<'EOF'
#!/bin/bash
# Script de mantenimiento vulnerable para el laboratorio TFM.
# Vulnerabilidad: ejecuta "backup" sin ruta absoluta.
# Si sudo conserva PATH, un usuario puede controlar qué binario se ejecuta.
backup
EOF

    chown root:root "${BACKUP_SCRIPT}"
    chmod 755 "${BACKUP_SCRIPT}"

    cat > "${SUDOERS_FILE}" <<EOF
# Configuración vulnerable intencionada para el laboratorio TFM.
# Permite demostrar PATH hijacking como técnica de escalada de privilegios.
Defaults env_keep += "PATH"
Defaults !secure_path

${DEV_USER} ALL=(ALL) NOPASSWD: ${BACKUP_SCRIPT}
EOF

    chmod 440 "${SUDOERS_FILE}"

    if ! visudo -cf "${SUDOERS_FILE}" >/dev/null; then
        print_error "La configuración sudoers generada no es válida."
        rm -f "${SUDOERS_FILE}"
        exit 1
    fi

    print_ok "sudoers vulnerable configurado en ${SUDOERS_FILE}"
}


ensure_docker_compose_uses_lab_paths() {
    print_info "Verificando docker-compose..."

    if ! grep -q "8080:80" "${LAB_DIR}/docker-compose.yml"; then
        print_warn "No se detecta el mapeo 8080:80 en docker-compose.yml. Revisa el puerto de la web."
    fi

    if ! grep -q "./shared:/shared" "${LAB_DIR}/docker-compose.yml"; then
        print_warn "No se detecta el volumen ./shared:/shared. El movimiento lateral podría no funcionar."
    fi

    print_ok "docker-compose verificado."
}


start_docker_lab() {
    print_info "Levantando laboratorio Docker..."

    cd "${LAB_DIR}"

    docker compose down -v || true
    docker compose up -d --build

    print_ok "Contenedores levantados."
}


show_summary() {
    IP_ADDR="$(hostname -I | awk '{print $1}')"

    echo
    echo "============================================================"
    echo " Laboratorio TFM desplegado correctamente"
    echo "============================================================"
    echo
    echo "Ruta del laboratorio:"
    echo "  ${LAB_DIR}"
    echo
    echo "Acceso web desde Kali:"
    echo "  http://${IP_ADDR}:8080/portal_pyme"
    echo "  o, si usas IP fija del README:"
    echo "  http://192.168.66.10:8080/portal_pyme"
    echo
    echo "Usuario para movimiento lateral:"
    echo "  Usuario: ${DEV_USER}"
    echo "  Password: ${DEV_PASS}"
    echo
    echo "Credenciales expuestas dentro del contenedor:"
    echo "  /shared/dev_credentials.txt"
    echo
    echo "Escalada de privilegios configurada:"
    echo "  sudo -l"
    echo "  sudo -u root ${BACKUP_SCRIPT}"
    echo
    echo "Comprobación de contenedores:"
    echo "  docker ps"
    echo
    echo "Aviso:"
    echo "  Este laboratorio almacena credenciales en texto plano por diseño."
    echo "  Úsalo únicamente en la red host-only del laboratorio."
    echo "============================================================"
}


main() {
    SKIP_INSTALL=0

    for arg in "$@"; do
        if [[ "${arg}" == "--skip-install" ]]; then
            SKIP_INSTALL=1
        fi
    done

    require_root
    require_ubuntu
    detect_repo_layout

    if [[ $SKIP_INSTALL -eq 0 ]]; then
        install_dependencies
    else
        print_info "Omitiendo instalación de dependencias (--skip-install)."
    fi

    prepare_lab_directory
    configure_users_and_groups
    configure_permissions
    configure_ssh
    configure_vulnerable_sudo
    ensure_docker_compose_uses_lab_paths
    start_docker_lab
    show_summary
}

main "$@"
