<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['empleado_id'])) {
    header("Location: /portal_pyme/login.php");
    exit;
}

include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$result = $conn->query("SELECT * FROM documentos ORDER BY id ASC");
?>

<section class="page-header">
    <h2>Documentos internos</h2>
    <p>Repositorio documental para empleados del portal.</p>
</section>

<div class="card">
    <h3>Biblioteca corporativa</h3>
    <p class="muted">Consulte documentación interna, manuales y procedimientos de la empresa.</p>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>Documento</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Acción</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                        <td>
                            <a class="btn-link" href="<?php echo htmlspecialchars($row['ruta']); ?>" target="_blank">
                                Abrir documento
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No hay documentos disponibles.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>