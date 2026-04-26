<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$result = $conn->query("SELECT id, codigo, asunto, estado, prioridad, empleado_asignado FROM tickets ORDER BY id DESC");
?>
<section class="page-header">
    <h2>Tickets internos</h2>
    <p>Gestión de incidencias y solicitudes de soporte.</p>
</section>
<div class="card">
    <div class="table-wrapper">
        <table>
            <tr><th>ID</th><th>Código</th><th>Asunto</th><th>Estado</th><th>Prioridad</th><th>Asignado</th><th>Acción</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($row['asunto']); ?></td>
                    <td><?php echo htmlspecialchars($row['estado']); ?></td>
                    <td><?php echo htmlspecialchars($row['prioridad']); ?></td>
                    <td><?php echo htmlspecialchars($row['empleado_asignado']); ?></td>
                    <td><a href="/portal_pyme/ticket.php?id=<?php echo $row['id']; ?>">Ver</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
