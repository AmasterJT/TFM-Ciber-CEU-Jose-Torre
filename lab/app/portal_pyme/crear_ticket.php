<?php
session_start();
include 'config/db.php';

function volver() {
    header("Location: /portal_pyme/estado_ticket.php");
    exit;
}

function generarCodigoTicket() {
    return "TCK-" . date('Y') . "-" . random_int(10000, 99999);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    volver();
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

$_SESSION['ticket_form_old'] = $_POST;

$errores = [];

if ($nombre === '' || strlen($nombre) < 3) {
    $errores['nombre'] = "Nombre inválido";
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = "Email inválido";
}

if ($asunto === '' || strlen($asunto) < 5) {
    $errores['asunto'] = "Asunto demasiado corto";
}

if ($descripcion === '' || strlen($descripcion) < 10) {
    $errores['descripcion'] = "Descripción demasiado corta";
}

if (!empty($errores)) {
    $_SESSION['ticket_errors'] = $errores;
    $_SESSION['ticket_flash'] = ['message' => 'Corrige los errores'];
    volver();
}

/* RATE LIMIT */
if (!isset($_SESSION['ticket_times'])) $_SESSION['ticket_times'] = [];
$ahora = time();

$_SESSION['ticket_times'] = array_filter($_SESSION['ticket_times'], fn($t) => ($ahora - $t) < 60);

if (count($_SESSION['ticket_times']) >= 3) {
    $_SESSION['ticket_flash'] = ['message' => 'Demasiados tickets, espera'];
    volver();
}

$_SESSION['ticket_times'][] = $ahora;

$codigo = generarCodigoTicket();

$query = "
INSERT INTO tickets
(codigo, asunto, descripcion, estado, prioridad, empleado_asignado)
VALUES
('$codigo','$asunto','$descripcion','Pendiente de clasificación','Sin asignar','Sin asignar')
";

if ($conn->query($query)) {
    unset($_SESSION['ticket_form_old']);
    $_SESSION['ticket_flash'] = ['message' => "Ticket creado: $codigo"];
    header("Location: /portal_pyme/estado_ticket.php?codigo=" . urlencode($codigo));
    exit;
}

$_SESSION['ticket_flash'] = ['message' => 'Error al crear ticket'];
volver();