<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal PYME</title>
    <link rel="stylesheet" href="/portal_pyme/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <h1>Portal PYME · Gestión y Soporte</h1>
    <div class="topbar-text">
        <?php if (isset($_SESSION['username'])): ?>
            Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> |
            <a href="/portal_pyme/logout.php" class="logout-btn">Cerrar sesión</a>
        <?php else: ?>
            Plataforma corporativa de clientes e incidencias
        <?php endif; ?>
    </div>
</header>
<div class="layout">
