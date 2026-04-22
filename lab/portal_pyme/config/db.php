<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("127.0.0.1", "portal", "portal123", "portal_pyme");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
