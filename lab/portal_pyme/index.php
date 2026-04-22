<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<section class="page-header">
    <h2>Dashboard</h2>
    <p>Resumen general del sistema.</p>
</section>

<div class="grid">
    <div class="stat">
        <div class="label">Clientes</div>
        <div class="value">--</div>
    </div>

    <div class="stat">
        <div class="label">Pedidos</div>
        <div class="value">--</div>
    </div>

    <div class="stat">
        <div class="label">Incidencias</div>
        <div class="value">--</div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
