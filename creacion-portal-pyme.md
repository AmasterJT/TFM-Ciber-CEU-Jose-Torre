# 🏢 Portal PYME Vulnerable – Enfoque y Diseño

## 📌 Descripción general

Este proyecto consiste en el desarrollo de un **portal web corporativo simulado** orientado a representar el entorno tecnológico típico de una pequeña o mediana empresa (PYME).

El objetivo principal es construir una **máquina vulnerable realista** que permita practicar técnicas de pentesting y Red Team, siguiendo una estructura similar a plataformas como Hack The Box o TryHackMe.

El portal incluye funcionalidades habituales en entornos empresariales, como gestión de empleados, clientes y tickets de soporte, integrando vulnerabilidades de forma intencionada para su explotación controlada.

---

## 🎯 Objetivo del portal

El portal no está diseñado como una aplicación segura, sino como un **escenario de ataque progresivo**, donde el usuario debe:

* 🔍 Enumerar la superficie de ataque
* 🚪 Obtener acceso inicial
* 🔐 Escalar privilegios
* 🧠 Identificar fallos de diseño y configuración

Todo ello siguiendo una cadena de ataque realista.

---

## 🧩 Enfoque de diseño

El diseño del portal sigue tres principios clave:

### 1. Realismo

Se simula una intranet corporativa con:

* Panel de empleados
* Gestión de clientes
* Sistema de tickets
* Subida de archivos
* Panel administrativo

El objetivo es que el entorno sea creíble y cercano a escenarios reales.

---

### 2. Vulnerabilidades no evidentes

Las vulnerabilidades no están expuestas de forma directa, sino que requieren:

* Enumeración previa
* Análisis del comportamiento de la aplicación
* Encadenamiento de fallos

Esto evita soluciones triviales y fomenta el aprendizaje práctico.

---

### 3. Cadena de ataque (Kill Chain)

El portal está diseñado para ser explotado en fases:

```text
Reconocimiento → Enumeración → Acceso inicial → Post-explotación
```

---

## 🗂️ Estructura del portal

```text
portal_pyme/
├── index.php              # Página pública
├── login.php              # Acceso empleados (enumeración por timing)
├── estado_ticket.php      # Consulta pública (posible SQLi)
├── dashboard.php          # Panel interno
├── perfil.php             # Perfil + subida de archivos (vector RCE)
├── clientes.php           # Listado clientes
├── cliente_detalle.php    # IDOR
├── tickets.php            # Gestión interna
├── documentos.php         # Ficheros internos
├── uploads/               # Archivos subidos (riesgo crítico)
├── admin/                 # Panel administrativo
├── backup/                # Backups expuestos
```

---

## ⚠️ Vulnerabilidades incluidas (Fase 1)

El portal implementa vulnerabilidades alineadas con OWASP Top 10:

### 🔓 Broken Access Control

* IDOR en `cliente_detalle.php`
* Acceso indebido a información de otros usuarios

---

### 💉 Injection

* SQL Injection en `estado_ticket.php`
* Login vulnerable a bypass

---

### ⚙️ Security Misconfiguration

* `display_errors` activado
* Carpeta `/uploads` con permisos inseguros
* Backup accesible públicamente

---

### 🧠 Insecure Design

* Falta de validación en subida de archivos
* Flujo de autenticación débil

---

### 🔑 Authentication Issues

* Enumeración de usuarios por timing en `login.php`

---

## 🧨 Vector clave: Subida de archivos

El módulo de perfil permite subir archivos sin validación:

* ❌ No se comprueba extensión
* ❌ No se valida MIME
* ❌ Se conserva el nombre original

Esto permite:

```text
Subida de webshell → ejecución remota de código (RCE)
```

---

## 🔗 Cadena de ataque esperada

Ejemplo de explotación:

```text
1. Descubrimiento de endpoints públicos
2. SQLi en estado_ticket.php
3. Enumeración de usuarios
4. Identificación de "superadmin"
5. Login bypass
6. Acceso a zona privada
7. IDOR en clientes
8. Subida de archivo malicioso
9. Ejecución remota
```

---

## 🧠 Filosofía del laboratorio

Este portal no busca enseñar únicamente vulnerabilidades aisladas, sino:

* Cómo se **descubren**
* Cómo se **encadenan**
* Cómo se **explotan en contexto real**

Se prioriza el aprendizaje práctico frente a la teoría.

---

## 🚨 Advertencia

Este proyecto es exclusivamente educativo.

No debe desplegarse en entornos accesibles desde Internet ni utilizarse fuera de un laboratorio controlado.

---

## 📈 Evolución futura

El portal está diseñado para ampliarse en fases posteriores:

* 🔐 Fallos criptográficos
* 🧩 Persistencia y escalada de privilegios
* 📜 Falta de logging
* 🧱 Evasión de mecanismos de detección

---

## 🏁 Conclusión

El portal PYME vulnerable representa un entorno realista y progresivo para el aprendizaje de ciberseguridad ofensiva, combinando:

* Diseño creíble
* Vulnerabilidades prácticas
* Enfoque metodológico

Ideal para simular escenarios de Red Team en un entorno controlado.
