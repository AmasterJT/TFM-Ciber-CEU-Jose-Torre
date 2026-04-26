<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$ticket_creado = false;
$codigo_generado = '';
$popup_message = '';
$popup_type = 'success';

function generarCodigoTicket() {
    $anio = date('Y');
    $random = random_int(10000, 99999);
    return "INC-" . $anio . "-" . $random;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    /*
     * RATE LIMIT BÁSICO
     * Máximo 3 tickets cada 60 segundos por sesión.
     * Vulnerable/bypasseable a propósito si se cambia o reinicia la sesión.
     */
    if (!isset($_SESSION['ticket_times'])) {
        $_SESSION['ticket_times'] = [];
    }

    $ahora = time();

    $_SESSION['ticket_times'] = array_filter(
        $_SESSION['ticket_times'],
        function ($t) use ($ahora) {
            return ($ahora - $t) < 60;
        }
    );

    if (count($_SESSION['ticket_times']) >= 3) {
        $popup_message = "Demasiadas solicitudes. Inténtelo de nuevo más tarde.";
        $popup_type = "error";
    } elseif ($nombre === '' || $email === '' || $asunto === '' || $descripcion === '') {
        $popup_message = "Todos los campos son obligatorios.";
        $popup_type = "error";
    } else {
        $_SESSION['ticket_times'][] = $ahora;

        $codigo_generado = generarCodigoTicket();

        /*
         * Vulnerable a propósito:
         * - SQL directo
         * - sin prepared statements
         * - sin sanitización estricta
         */
        $query = "
            INSERT INTO tickets
            (codigo, nombre_cliente, email_cliente, asunto, descripcion, estado, prioridad, empleado_asignado)
            VALUES
            (
                '$codigo_generado',
                '$nombre',
                '$email',
                '$asunto',
                '$descripcion',
                'Pendiente de clasificación',
                'Sin asignar',
                'Sin asignar'
            )
        ";

        if ($conn->query($query)) {
            $ticket_creado = true;
            $popup_message = "Ticket creado correctamente.";
            $popup_type = "success";
        } else {
            $popup_message = "Error al crear el ticket.";
            $popup_type = "error";
        }
    }
}
?>

<section class="page-header">
    <h2>Soporte técnico</h2>
    <p>Desde este apartado puede registrar una incidencia y consultar posteriormente su estado.</p>
</section>

<div class="card">
    <h3>Gestión de incidencias</h3>
    <p>Pulse el botón para abrir el formulario de creación de ticket.</p>

    <button type="button" onclick="openTicketModal()">Crear nuevo ticket</button>
</div>

<div id="createTicketModal" class="modal-overlay" style="display: <?php echo ($_SERVER["REQUEST_METHOD"] === "POST" && !$ticket_creado) ? 'flex' : 'none'; ?>;">
    <div class="modal-box ticket-modal-box">

        <div class="modal-title-row">
            <div>
                <h3>Crear ticket de soporte</h3>
                <p>Complete los datos de la incidencia.</p>
            </div>

            <button type="button" class="modal-close-x" onclick="closeTicketModal()">×</button>
        </div>

        <form method="POST" class="ticket-form">
            <label for="nombre">Nombre</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
            >

            <label for="email">Correo electrónico</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            >

            <label for="asunto">Asunto</label>
            <input
                type="text"
                id="asunto"
                name="asunto"
                value="<?php echo htmlspecialchars($_POST['asunto'] ?? ''); ?>"
            >

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>

            <div class="modal-actions">
                <button type="button" class="secondary-btn" onclick="closeTicketModal()">Cancelar</button>
                <input type="submit" value="Crear ticket">
            </div>
        </form>
    </div>
</div>

<?php if ($ticket_creado): ?>
<div id="ticketSuccessModal" class="alert-overlay">
    <div class="alert-box alert-success">
        <div class="alert-header">✔ Ticket registrado</div>
        <div class="alert-body">
            <p>El ticket se ha creado correctamente.</p>
            <p>
                Código de seguimiento:<br>
                <strong><?php echo htmlspecialchars($codigo_generado); ?></strong>
            </p>
            <p>
                <a href="/portal_pyme/estado_ticket.php?codigo=<?php echo urlencode($codigo_generado); ?>">
                    Consultar estado del ticket
                </a>
            </p>
        </div>
        <div class="alert-footer">
            <button type="button" onclick="closeSuccessModal()">Aceptar</button>
        </div>
    </div>
</div>
<?php elseif ($popup_message !== ''): ?>
<div id="ticketErrorModal" class="alert-overlay">
    <div class="alert-box alert-error">
        <div class="alert-header">✖ Error</div>
        <div class="alert-body">
            <?php echo htmlspecialchars($popup_message); ?>
        </div>
        <div class="alert-footer">
            <button type="button" onclick="closeErrorModal()">Aceptar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function openTicketModal() {
    const modal = document.getElementById('createTicketModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeTicketModal() {
    const modal = document.getElementById('createTicketModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('ticketSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function closeErrorModal() {
    const modal = document.getElementById('ticketErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTicketModal();
        closeSuccessModal();
        closeErrorModal();
    }
});

window.addEventListener('click', function(e) {
    const createModal = document.getElementById('createTicketModal');
    const successModal = document.getElementById('ticketSuccessModal');
    const errorModal = document.getElementById('ticketErrorModal');

    if (e.target === createModal) {
        closeTicketModal();
    }

    if (e.target === successModal) {
        closeSuccessModal();
    }

    if (e.target === errorModal) {
        closeErrorModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>