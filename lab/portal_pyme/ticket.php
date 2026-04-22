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

$id = $_GET['id'] ?? '';
?>

<section class="page-header">
    <h2>Tickets y pedidos</h2>
    <p>Consulta de incidencias y operaciones asociadas.</p>
</section>

<div class="card">
    <h3>Consulta por identificador</h3>

    <form method="GET">
        <label for="id">ID</label>
        <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="submit" value="Consultar">
    </form>

    <?php if ($id !== ''): ?>
        <?php
        $query = "SELECT * FROM pedidos WHERE id = '$id'";
        $result = $conn->query($query);
        ?>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Cliente ID</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Importe</th>
                    <th>Owner</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['cliente_id']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo $row['estado']; ?></td>
                        <td><?php echo $row['importe']; ?></td>
                        <td><?php echo $row['owner_id']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
