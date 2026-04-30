<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$id = $_GET['id'] ?? '';
?>
<section class="page-header">
    <h2>Detalle de ticket</h2>
    <p>Consulta interna de incidencias.</p>
</section>
<div class="card">
    <?php if ($id === ''): ?>
        <div class="notice">Seleccione un ticket desde el listado interno.</div>
    <?php else: ?>
        <?php
        $query = "SELECT * FROM tickets WHERE id = '$id'";
        $result = $conn->query($query);
        $ticket = $result->fetch_assoc();
        ?>
        <?php if ($ticket): ?>
            <h3><?php echo htmlspecialchars($ticket['codigo']); ?> · <?php echo htmlspecialchars($ticket['asunto']); ?></h3>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($ticket['estado']); ?></p>
            <p><strong>Prioridad:</strong> <?php echo htmlspecialchars($ticket['prioridad']); ?></p>
            <p><strong>Asignado:</strong> <?php echo htmlspecialchars($ticket['empleado_asignado']); ?></p>
            <p><strong>Descripción:</strong></p>
            <div class="notice"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></div>
        <?php else: ?>
            <div class="notice">Ticket no encontrado</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
