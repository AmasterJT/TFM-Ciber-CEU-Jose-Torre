<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$result = $conn->query("SELECT * FROM documentos ORDER BY id ASC");
?>
<section class="page-header">
    <h2>Documentos internos</h2>
    <p>Repositorio documental para empleados.</p>
</section>
<div class="card">
    <div class="table-wrapper">
        <table>
            <tr><th>Nombre</th><th>Tipo</th><th>Descripción</th><th>Ruta</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($row['ruta']); ?>">Abrir</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
