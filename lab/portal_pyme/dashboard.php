<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['empleado_id'])) {
    header("Location: /portal_pyme/login.php");
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<section class="page-header">
    <h2>Dashboard</h2>
    <p>Resumen de actividad del entorno corporativo.</p>
</section>

<div class="grid">
    <div class="stat">
        <div class="label">Incidencias críticas</div>
        <div class="value">3</div>
    </div>
    <div class="stat">
        <div class="label">Pendientes de revisión</div>
        <div class="value">8</div>
    </div>
    <div class="stat">
        <div class="label">Clientes prioritarios</div>
        <div class="value">5</div>
    </div>
</div>

<div class="card">
    <h3>Actividad reciente</h3>
    <p>Se han registrado nuevas solicitudes de soporte y actualizaciones de pedidos en las últimas 24 horas.</p>
</div>

<?php include 'includes/footer.php'; ?>
