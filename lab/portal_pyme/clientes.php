<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['empleado_id'])) {
    echo "<script>alert('Debes iniciar sesión'); window.location='/portal_pyme/login.php';</script>";
    exit;
}

include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$q = $_GET['q'] ?? '';
?>

<section class="page-header">
    <h2>Clientes</h2>
</section>

<div class="card">
    <form method="GET">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>">
        <input type="submit" value="Buscar">
    </form>

    <?php if ($q !== ''):
        $query = "SELECT * FROM clientes WHERE nombre LIKE '%$q%' OR empresa LIKE '%$q%'";
        $result = $conn->query($query);
    ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['nombre']; ?></td>
            <td><?php echo $row['email']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
