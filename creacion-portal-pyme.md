# 🧪 Ruta de despliegue del laboratorio (TFM)

Te organizo el trabajo en una **ruta progresiva (de menos a más complejidad)** para que avances sin bloquearte y sin rehacer todo desde cero.

## 🎯 Enfoque general

- **Nivel 1:** Mejorar DVWA con módulos más realistas  
- **Nivel 2:** Crear una miniaplicación propia  
- **Nivel 3:** Mantener DVWA + portal vulnerable propio (**recomendado para TFM**)  

👉 Para un TFM sólido, el objetivo final es el **Nivel 3**, pero conviene pasar por los anteriores.

---

# 🔹 Nivel 1 — Mejora rápida sobre DVWA

## 🎯 Objetivo
Aprovechar DVWA y añadir páginas con apariencia de intranet/empresa.

## ✅ Resultado esperado
Laboratorio más creíble sin desarrollar todo desde cero.

---

## 📁 Estructura

```
/var/www/html/DVWA/fase1-tfm/
    admin_custom/
        index.php
    clientes_custom/
        index.php
    login_custom/
        index.php
    pedidos_custom/
        index.php
```

---
---

## ⚙️ Paso 1 — Comprobar servicios

```bash
sudo systemctl status apache2
sudo systemctl status mysql
```

Abrir en navegador:

```
http://192.168.66.10/DVWA/
```

---

## 📂 Paso 2 — Crear directorios

```bash
cd /var/www/html/DVWA/fase1-tfm/
sudo mkdir login_custom clientes_custom pedidos_custom admin_custom
```

---

## 🧱 Paso 3 — Crear páginas base

```bash
sudo nano login_custom/index.php
sudo nano clientes_custom/index.php
sudo nano pedidos_custom/index.php
sudo nano admin_custom/index.php
```

---

## 🗄️ Paso 4 — Base de datos

```sql
USE dvwa;

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    empresa VARCHAR(100),
    propietario INT
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    descripcion VARCHAR(255),
    estado VARCHAR(50),
    importe DECIMAL(10,2),
    owner_id INT
);

CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(100),
    rol VARCHAR(20),
    email VARCHAR(100)
);
```

---

## 🧨 Paso 5 — Vulnerabilidades

### Login
- SQLi
- Sin rate limit
- Errores detallados

### Clientes
- SQLi en búsqueda
- Fuga de datos
- Sin control de acceso

### Pedidos
- IDOR
- Manipulación de parámetros

### Admin
- Acceso directo sin autenticación

---

## 🔗 Paso 6 — Navegación

```html
<a href="../login_custom/index.php">Login</a> |
<a href="../clientes_custom/index.php">Clientes</a> |
<a href="../pedidos_custom/index.php">Pedidos</a> |
<a href="../admin_custom/index.php">Admin</a>
```

---

## 🧪 Paso 7 — Pruebas

- Navegador
- SQLi manual
- Burp Suite
- sqlmap

---

## 📝 Paso 8 — Documentación

Para cada módulo:

- URL
- Vulnerabilidad
- Parámetro vulnerable
- Impacto
- Explotación
- Mitigación

---

# 🔹 Nivel 2 — Miniportal propio

## 📁 Ruta

```
/var/www/html/portal_pyme/
```

---

## ⚙️ Estructura

```
portal_pyme/
    index.php
    login.php
    dashboard.php
    clientes.php
    ticket.php
    perfil.php
    admin.php
    config/db.php
    includes/header.php
    includes/footer.php
```

---

## 🗄️ Base de datos

```sql
CREATE DATABASE portal_pyme;
USE portal_pyme;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(100),
    nombre VARCHAR(100),
    email VARCHAR(100),
    rol VARCHAR(20)
);
```

---

## 🎨 UI

Añadir Bootstrap:

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
```

---

## 🧨 Vulnerabilidades clave

- SQLi (login, búsqueda)
- IDOR
- Broken Access Control
- Security Misconfiguration

---

# 🔹 Nivel 3 — Arquitectura híbrida (RECOMENDADO)

## 🏗️ Estructura final

```
/var/www/html/DVWA/
/var/www/html/portal_pyme/
```

---

## 🎯 Objetivo

- DVWA → referencia académica  
- portal_pyme → caso realista (TU TFM)

---

## 🎭 Narrativa

- DVWA = entorno de entrenamiento  
- portal_pyme = empresa ficticia  

---

## 🧪 Escenarios de ataque

1. SQLi en login  
2. IDOR en clientes  
3. Acceso a pedidos ajenos  
4. Acceso directo a admin  
5. Enumeración de usuarios  

---

## 📊 OWASP cubierto

- A01 Broken Access Control  
- A02 Security Misconfiguration  
- A05 Injection  

---

# 🚀 Recomendación final

### Plan realista

**Semana 1**
- Nivel 1

**Semana 2**
- Nivel 2

**Semana 3**
- Nivel 3 + documentación

---

## 🧠 Estrategia

- Mantener DVWA funcionando  
- Crear portal propio  
- Introducir vulnerabilidades reales  
- Atacar desde Kali  
- Documentar explotación y mitigación  

---

💡 Resultado: laboratorio sólido, profesional y defendible.
