<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

/*
 * Control de acceso mal implementado a propósito:
 * - si hay sesión con rol admin, entra
 * - PERO también entra si el parámetro GET role=admin
 * Esto permite bypass por URL:
 * /portal_pyme/backend/panel.php?role=admin
 */

$role = $_SESSION['rol'] ?? '';
$role_param = $_GET['role'] ?? '';

if ($role !== 'admin' && $role_param !== 'admin') {
    echo '<section class="page-header">';
    echo '<h2>Acceso restringido</h2>';
    echo '<p>No dispone de permisos suficientes para acceder a esta sección.</p>';
    echo '</section>';

    echo '<div class="card">';
    echo '<h3>Acceso denegado</h3>';
    echo '<p>Se requiere perfil administrativo para visualizar este contenido.</p>';
    echo '</div>';

    include '../includes/footer.php';
    exit;
}
?>

<section class="page-header">
    <h2>Panel interno</h2>
    <p>Zona reservada para administración operativa.</p>
</section>

<div class="card">
    <h3>Usuarios internos</h3>

    <?php
    $query = "SELECT id, username, rol, email FROM empleados";
    $result = $conn->query($query);
    ?>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Email</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['rol']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>




/*

// Alternativa todavía más simple: página olvidada sin protección

// Una versión más básica, totalmente “olvidada”, sería esta:

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<section class="page-header">
    <h2>Panel interno</h2>
    <p>Zona reservada para administración operativa.</p>
</section>

<div class="card">
    <h3>Usuarios internos</h3>

    <?php
    $query = "SELECT id, username, rol, email FROM empleados";
    $result = $conn->query($query);
    ?>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Email</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['rol']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

// Esta version es más fácil de explotar, pero también más obvia.

*/
