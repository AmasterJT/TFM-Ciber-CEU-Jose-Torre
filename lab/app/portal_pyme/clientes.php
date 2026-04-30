<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['empleado_id'])) { header("Location: /portal_pyme/login.php"); exit; }
include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$empleado_id = $_SESSION['empleado_id'];
$q = $_GET['q'] ?? '';

if ($q !== '') {
    // Vulnerabilidad didáctica: búsqueda vulnerable a SQLi dentro de zona autenticada.
    $query = "SELECT * FROM clientes WHERE nombre LIKE '%$q%' OR empresa LIKE '%$q%'";
} else {
    $query = "SELECT * FROM clientes WHERE owner_id = $empleado_id ORDER BY id ASC";
}
$result = $conn->query($query);
?>
<section class="page-header">
    <h2>Clientes</h2>
    <p>Clientes asignados al empleado autenticado.</p>
</section>
<div class="card">
    <form method="GET">
        <label for="q">Buscar cliente</label>
        <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($q); ?>">
        <input type="submit" value="Buscar">
    </form>
    <div class="table-wrapper">
        <table>
            <tr><th>ID</th><th>Empresa</th><th>Contacto</th><th>Email</th><th>Acción</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><a href="/portal_pyme/cliente_detalle.php?id=<?php echo $row['id']; ?>">Ver ficha</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
