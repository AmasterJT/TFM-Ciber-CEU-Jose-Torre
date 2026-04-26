<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';
$clientes = $conn->query("SELECT COUNT(*) AS total FROM clientes")->fetch_assoc()['total'];
$tickets_abiertos = $conn->query("SELECT COUNT(*) AS total FROM tickets WHERE estado <> 'Cerrado'")->fetch_assoc()['total'];
$docs = $conn->query("SELECT COUNT(*) AS total FROM documentos")->fetch_assoc()['total'];
?>
<section class="page-header">
    <h2>Dashboard</h2>
    <p>Resumen de actividad del entorno corporativo.</p>
</section>
<div class="grid">
    <div class="stat"><div class="label">Clientes registrados</div><div class="value"><?php echo $clientes; ?></div></div>
    <div class="stat"><div class="label">Tickets abiertos</div><div class="value"><?php echo $tickets_abiertos; ?></div></div>
    <div class="stat"><div class="label">Documentos internos</div><div class="value"><?php echo $docs; ?></div></div>
</div>
<div class="card">
    <h3>Actividad reciente</h3>
    <p>Se ha habilitado el módulo de consulta pública de tickets para clientes externos.</p>
    <p>El antiguo sistema de gestión sigue disponible temporalmente por compatibilidad.</p>
</div>
<?php include 'includes/footer.php'; ?>
