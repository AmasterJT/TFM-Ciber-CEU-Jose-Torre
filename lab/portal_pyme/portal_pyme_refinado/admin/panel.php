<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Control de acceso incompleto a propósito: acepta rol de sesión o parámetro legacy.
$legacy_role = $_GET['role'] ?? '';
if (($_SESSION['rol'] ?? '') !== 'admin' && $legacy_role !== 'admin') {
    echo '<section class="page-header"><h2>Panel de administración</h2></section>';
    echo '<div class="card"><div class="notice">Acceso restringido al equipo de administración.</div></div>';
    include '../includes/footer.php';
    exit;
}
$empleados = $conn->query("SELECT id, username, email, rol, departamento FROM empleados ORDER BY id ASC");
?>
<section class="page-header">
    <h2>Panel de administración</h2>
    <p>Gestión interna de empleados y configuración del portal.</p>
</section>
<div class="card">
    <h3>Empleados</h3>
    <div class="table-wrapper">
        <table>
            <tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Departamento</th></tr>
            <?php while ($row = $empleados->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['rol']); ?></td>
                    <td><?php echo htmlspecialchars($row['departamento']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<div class="card">
    <h3>Mantenimiento</h3>
    <p>Última copia local detectada: <code>/portal_pyme/backup/portal_backup.sql</code></p>
    <p>Modo compatibilidad legacy activo.</p>
</div>
<?php include '../includes/footer.php'; ?>
