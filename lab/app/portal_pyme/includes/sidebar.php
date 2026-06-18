<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<aside class="sidebar">
    <h2>Navegación</h2>
    <nav>
        <a href="/index.php">Inicio</a>
        <a href="/estado_ticket.php">Estado ticket</a>

        <?php if (!isset($_SESSION['empleado_id'])): ?>
            <a href="/login.php">Acceso empleados</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['empleado_id'])): ?>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/clientes.php">Clientes</a>
            <a href="/tickets.php">Tickets</a>
            <a href="/documentos.php">Documentos</a>
            <a href="/perfil.php">Perfil</a>
            <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                <a href="/admin/panel.php">Administración</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>
<main class="content">
