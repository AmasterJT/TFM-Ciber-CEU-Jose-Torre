# 💉 1. Añadir tu propia vulnerabilidad (SQLi custom)
Crear nueva página vulnerable

```bash
cd /var/www/html/DVWA/vulnerabilities
sudo mkdir sqli_custom
cd sqli_custom
sudo nano index.php
``` 

Ejemplo vulnerable:

```php 
    <?php
    $conn = new mysqli("localhost", "dvwa", "password", "dvwa");

    $id = $_GET['id'];

    $query = "SELECT * FROM users WHERE id = '$id'";
    $result = $conn->query($query);

    while($row = $result->fetch_assoc()) {
        echo "User: " . $row['user'];
    }
?>
```

👉 Esto es SQL Injection directo.

# 🔓 2. Broken Access Control (MUY IMPORTANTE)
Crear panel admin sin control
```Bbash
sudo nano /var/www/html/admin_panel.php
```
```php
<?php
echo "Bienvenido admin";
?>
```

👉 Sin login
👉 Acceso directo por URL

✔ Esto simula:

- A01 Broken Access Control

# ⚙️ 3. Security Misconfiguration (fácil y realista)

Haz esto en Apache:
```bash 
sudo nano /etc/php/8.1/apache2/php.ini
```

Activa:
``` bash
display_errors = On
```

👉 Resultado:
- errores visibles
- fuga de información

# 💣 4. Exponer archivos sensibles

```bash
cd /var/www/html
sudo mkdir backup
sudo nano backup/db.sql
```

👉 Mete datos fake:
```bash
user: admin
password: admin123
```

👉 Accesible vía:

```http
http://IP/backup/db.sql
```


🧠 PARTE 3 — documentación (IMPORTANTE TFM)

Cada vulnerabilidad debe tener:

📌 Estructura:
- Descripción técnica
- Código vulnerable
- Escenario
- Explotación paso a paso
- Evidencias (capturas)
- Impacto
- Mitigación

👉 Esto va directo a:

- Desarrollo
- Resultados



---
---

💡 Mejora (clave 🔑)

Diseña una cadena así:

1. Recon (dirsearch / gobuster)
2. Encuentra endpoint raro
3. SQLi → credenciales
4. Login
5. IDOR → ver otros usuarios
6. Subida archivo → RCE
7. Reverse shell
8. Privilege escalation (sudo mal configurado)
🧨 3. Idea de evolución brutal (te la dejo hecha)

Si quieres subirlo a nivel top:

🧩 FASE 1 (tu actual, pero refinada)
SQLi (pero con filtro básico)
Login bypass
IDOR en clientes
🔐 FASE 2
Hashes débiles (MD5)
Session fixation
Cookie manipulable
💀 FASE 3
Upload bypass → webshell
Cron vulnerable
SUID binary