<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$id = $_GET['id'] ?? '1';
// Vulnerabilidad didáctica: IDOR. No se comprueba owner_id contra $_SESSION['empleado_id'].
$query = "SELECT * FROM clientes WHERE id = $id";
$result = $conn->query($query);
$cliente = $result->fetch_assoc();
?>
<section class="page-header">
    <h2>Ficha de cliente</h2>
    <p>Detalle operativo del cliente seleccionado.</p>
</section>
<div class="card">
    <?php if ($cliente): ?>
        <h3><?php echo htmlspecialchars($cliente['empresa']); ?></h3>
        <p><strong>ID:</strong> <?php echo $cliente['id']; ?></p>
        <p><strong>Contacto:</strong> <?php echo htmlspecialchars($cliente['nombre']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
        <p><strong>Responsable interno:</strong> empleado #<?php echo htmlspecialchars($cliente['owner_id']); ?></p>
        <p><strong>Notas internas:</strong></p>
        <div class="notice"><?php echo nl2br(htmlspecialchars($cliente['notas'])); ?></div>
        <p><a href="/portal_pyme/clientes.php">Volver al listado</a></p>
    <?php else: ?>
        <div class="notice">Cliente no encontrado</div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
