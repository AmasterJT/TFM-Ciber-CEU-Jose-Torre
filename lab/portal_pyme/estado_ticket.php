<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$codigo = $_GET['codigo'] ?? '';
?>

<section class="page-header">
    <h2>Consulta pública de ticket</h2>
    <p>Consulte el estado de una incidencia mediante su código de seguimiento.</p>
</section>

<div class="card">
    <h3>Estado de incidencia</h3>

    <p>
        Si todavía no tiene un código de seguimiento,
        <a href="#" onclick="openTicketModal(); return false;">cree un nuevo ticket</a>.
    </p>

    <form method="GET">
        <label for="codigo">Código de seguimiento</label>
        <input
            type="text"
            id="codigo"
            name="codigo"
            value="<?php echo htmlspecialchars($codigo); ?>"
            placeholder="Introduzca su código de seguimiento"
        >
        <input type="submit" value="Consultar">
    </form>

    <?php if ($codigo !== ''): ?>
        <?php
        $codigo_filtrado = str_replace(["--", "/*", "*/"], "", $codigo);

        $query = "
            SELECT codigo, asunto, estado, prioridad, empleado_asignado
            FROM tickets
            WHERE codigo = '$codigo_filtrado'
        ";

        $result = $conn->query($query);
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Código</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Empleado asignado</th>
                    </tr>

                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['asunto']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['prioridad']); ?></td>
                            <td><?php echo htmlspecialchars($row['empleado_asignado']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php else: ?>
            <div class="notice">No se encontró ningún ticket con ese código.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- MODAL CREAR TICKET -->
<div id="createTicketModal" class="modal-overlay" style="display: none;">
    <div class="modal-box ticket-modal-box">

        <div class="modal-title-row">
            <div>
                <h3>Crear ticket de soporte</h3>
                <p>Complete los datos de la incidencia.</p>
            </div>

            <button type="button" class="modal-close-x" onclick="closeTicketModal()">×</button>
        </div>

        <form method="POST" action="/portal_pyme/crear_ticket.php" class="ticket-form">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre">

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email">

            <label for="asunto">Asunto</label>
            <input type="text" id="asunto" name="asunto">

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"></textarea>

            <div class="modal-actions">
                <button type="button" class="secondary-btn" onclick="closeTicketModal()">Cancelar</button>
                <input type="submit" value="Crear ticket">
            </div>
        </form>
    </div>
</div>

<!-- JS -->
<script>
function openTicketModal() {
    document.getElementById('createTicketModal').style.display = 'flex';
}

function closeTicketModal() {
    document.getElementById('createTicketModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTicketModal();
    }
});

window.addEventListener('click', function(e) {
    const modal = document.getElementById('createTicketModal');

    if (e.target === modal) {
        closeTicketModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>