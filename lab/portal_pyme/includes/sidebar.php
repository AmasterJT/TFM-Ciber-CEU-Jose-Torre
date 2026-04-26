<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<aside class="sidebar">
    <h2>Navegación</h2>
    <nav>
        <a href="/portal_pyme/index.php">Inicio</a>
        <a href="/portal_pyme/estado_ticket.php">Estado ticket</a>

        <?php if (!isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/login.php">Acceso empleados</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/portal_pyme/dashboard.php">Dashboard</a>
            <a href="/portal_pyme/clientes.php">Clientes</a>
            <a href="/portal_pyme/tickets.php">Tickets</a>
            <a href="/portal_pyme/documentos.php">Documentos</a>
            <a href="/portal_pyme/perfil.php">Perfil</a>
            <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                <a href="/portal_pyme/admin/panel.php">Administración</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>
<main class="content">
