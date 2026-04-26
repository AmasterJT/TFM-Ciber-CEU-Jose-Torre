<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$codigo = $_GET['codigo'] ?? '';

$ticket_flash = $_SESSION['ticket_flash'] ?? null;
$ticket_errors = $_SESSION['ticket_errors'] ?? [];
$ticket_old = $_SESSION['ticket_form_old'] ?? [];

unset($_SESSION['ticket_flash'], $_SESSION['ticket_errors'], $_SESSION['ticket_form_old']);
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
        <input type="text" name="codigo" placeholder="Código de ticket">
        <input type="submit" value="Consultar">
    </form>
</div>

<!-- MODAL -->
<div id="createTicketModal" class="modal-overlay" style="display: <?php echo !empty($ticket_errors) ? 'flex' : 'none'; ?>;">
    <div class="modal-box ticket-modal-box">

        <div class="modal-title-row">
            <div>
                <h3>Crear ticket de soporte</h3>
                <p>Complete los datos de la incidencia.</p>
            </div>
            <button type="button" class="modal-close-x" onclick="closeTicketModal()">×</button>
        </div>

        <form method="POST" action="/portal_pyme/crear_ticket.php" class="ticket-form">
            <div class="field-group">
                <label>Nombre</label>
                <?php if (isset($ticket_errors['nombre'])): ?>
                    <small class="field-error"><?php echo $ticket_errors['nombre']; ?></small>
                <?php endif; ?>
            </div>

            <input type="text" name="nombre"
                class="<?php echo isset($ticket_errors['nombre']) ? 'input-error' : ''; ?>"
                value="<?php echo htmlspecialchars($ticket_old['nombre'] ?? ''); ?>"
                onfocus="clearInputError(this)">


            <div class="field-group">
                <label>Email</label>
                <?php if (isset($ticket_errors['email'])): ?>
                    <small class="field-error"><?php echo $ticket_errors['email']; ?></small>
                <?php endif; ?>
            </div>
            <input type="text" name="email"
                class="<?php echo isset($ticket_errors['email']) ? 'input-error' : ''; ?>"
                value="<?php echo htmlspecialchars($ticket_old['email'] ?? ''); ?>"
                onfocus="clearInputError(this)">


            <div class="field-group">
                <label>Asunto</label>
                <?php if (isset($ticket_errors['asunto'])): ?>
                    <small class="field-error"><?php echo $ticket_errors['asunto']; ?></small>
                <?php endif; ?>
            </div>                
            <input type="text" name="asunto"
                class="<?php echo isset($ticket_errors['asunto']) ? 'input-error' : ''; ?>"
                value="<?php echo htmlspecialchars($ticket_old['asunto'] ?? ''); ?>"
                onfocus="clearInputError(this)">

            <div class="field-group">
                <label>Descripción</label>
                <?php if (isset($ticket_errors['descripcion'])): ?>
                    <small class="field-error"><?php echo $ticket_errors['descripcion']; ?></small>
                <?php endif; ?>
            </div>
        
            <textarea name="descripcion"
                class="<?php echo isset($ticket_errors['descripcion']) ? 'input-error' : ''; ?>"
                onfocus="clearInputError(this)"><?php echo htmlspecialchars($ticket_old['descripcion'] ?? ''); ?></textarea>

            <div class="modal-actions">
                <button type="button" onclick="closeTicketModal()">Cancelar</button>
                <input type="submit" value="Crear ticket">
            </div>

        </form>
    </div>
</div>

<?php if ($ticket_flash): ?>
<script>
alert("<?php echo htmlspecialchars($ticket_flash['message']); ?>");
</script>
<?php endif; ?>

<script>
function openTicketModal() {
    document.getElementById('createTicketModal').style.display = 'flex';
}

function closeTicketModal() {
    document.getElementById('createTicketModal').style.display = 'none';
}

function clearInputError(input) {
    input.classList.remove('input-error');
    const err = input.parentElement.querySelector('.field-error');
    if (err) err.style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>