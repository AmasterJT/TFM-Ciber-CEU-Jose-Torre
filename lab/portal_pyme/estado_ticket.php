<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$codigo = $_GET['codigo'] ?? '';
?>
<section class="page-header">
    <h2>Consulta pública de ticket</h2>
    <p>Introduzca el código de seguimiento facilitado por soporte.</p>
</section>
<div class="card">
    <h3>Estado de incidencia</h3>
    <form method="GET">
        <label for="codigo">Código de ticket</label>
        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($codigo); ?>" placeholder="TCK-2026-001">
        <input type="submit" value="Consultar">
    </form>

    <?php if ($codigo !== ''): ?>
        <?php
        // Filtro incompleto a propósito: bloquea payloads básicos, pero sigue siendo vulnerable.
        $codigo_filtrado = str_replace(["--", "/*", "*/"], "", $codigo);
        $query = "SELECT codigo, asunto, estado, prioridad, empleado_asignado FROM tickets WHERE codigo = '$codigo_filtrado'";
        $result = $conn->query($query);
        ?>
        <div class="table-wrapper">
            <table>
                <tr><th>Código</th><th>Asunto</th><th>Estado</th><th>Prioridad</th><th>Empleado asignado</th></tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($row['asunto']); ?></td>
                        <td><?php echo htmlspecialchars($row['estado']); ?></td>
                        <td><?php echo htmlspecialchars($row['prioridad']); ?></td>
                        <td><?php echo htmlspecialchars($row['empleado_asignado']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
