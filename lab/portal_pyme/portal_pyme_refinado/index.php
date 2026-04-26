<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<section class="page-header">
    <h2>Portal interno de gestión para PYME</h2>
    <p>Acceso corporativo para empleados, soporte y seguimiento de clientes.</p>
</section>
<div class="grid">
    <div class="card">
        <h3>Acceso empleados</h3>
        <p>Entrada privada para personal de soporte, ventas, administración y dirección.</p>
        <p><a href="/portal_pyme/login.php">Ir al login</a></p>
    </div>
    <div class="card">
        <h3>Consulta de tickets</h3>
        <p>Los clientes pueden consultar el estado de una incidencia mediante su código público.</p>
        <p><a href="/portal_pyme/estado_ticket.php">Consultar estado</a></p>
    </div>
    <div class="card">
        <h3>Soporte técnico</h3>
        <p>Para incidencias urgentes contacte con el departamento de soporte.</p>
        <p><strong>Horario:</strong> 08:00 - 18:00</p>
    </div>
</div>
<div class="card">
    <h3>Aviso interno</h3>
    <p>Se está migrando el sistema antiguo de tickets. Algunos módulos pueden operar en modo compatibilidad.</p>
</div>
<?php include 'includes/footer.php'; ?>
