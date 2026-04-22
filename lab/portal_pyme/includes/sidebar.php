<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<aside class="sidebar">
    <h2>Navegación</h2>
    <nav>
        <a href="/portal_pyme/index.php">Inicio</a>

        <?php if (!isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/login.php">Acceso</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="#" onclick="alert('Debe iniciar sesión para acceder a Dashboard'); return false;">
                Dashboard
            </a>
        <?php endif; ?> 

        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/clientes.php">Clientes</a>
        <?php else: ?>
            <a href="#" onclick="alert('Debe iniciar sesión para acceder a Clientes'); return false;">
                Clientes
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/ticket.php">Tickets</a>
        <?php else: ?>
            <a href="#" onclick="alert('Debe iniciar sesión para acceder a Tickets'); return false;">
                Tickets
            </a>
        <?php endif; ?>


        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/perfil.php">Perfil</a>
        <?php else: ?>
            <a href="#" onclick="alert('Debe iniciar sesión para acceder al perfil'); return false;">
                Perfil
            </a>
        <?php endif; ?>
    </nav>
</aside>
<main class="content">
