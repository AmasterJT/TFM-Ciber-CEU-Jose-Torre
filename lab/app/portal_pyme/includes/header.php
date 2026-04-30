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

    <!-- FAVICON (varias opciones para asegurar que cargue) -->
    <link rel="icon" href="/portal_pyme/favicon.ico">
    <link rel="icon" type="image/png" href="/portal_pyme/assets/img/favicon.png">

    <!-- CSS -->
    <link rel="stylesheet" href="/portal_pyme/assets/css/style.css">
</head>

<body>
<header class="topbar">

    <!-- LOGO CLICABLE SIN CAMBIAR ESTILO -->
    <h1 style="margin:0;">
        <a href="/portal_pyme/index.php"
           style="color: inherit; text-decoration: none;">
            Portal PYME · Gestión y Soporte
        </a>
    </h1>

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