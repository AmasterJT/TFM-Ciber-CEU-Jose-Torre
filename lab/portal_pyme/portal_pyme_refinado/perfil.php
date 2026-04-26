<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['empleado_id'])) {
    header("Location: /portal_pyme/login.php");
    exit;
}

include 'config/db.php';

$popup_message = '';
$popup_type = 'success';

$empleado_id = $_SESSION['empleado_id'];

/*
 * SUBIDA DE ARCHIVOS
 * Vulnerable a propósito:
 * - no valida extensión real
 * - no valida MIME
 * - conserva el nombre original
 */
if (isset($_POST['upload']) && isset($_FILES['foto'])) {
    $nombre_original = basename($_FILES['foto']['name']);
    $tmp = $_FILES['foto']['tmp_name'];

    if (!empty($nombre_original) && is_uploaded_file($tmp)) {
        $uploads_dir_fs = __DIR__ . "/uploads/";
        $nombre_destino = $nombre_original;
        $ruta_destino_fs = $uploads_dir_fs . $nombre_destino;

        /*
         * Evita sobreescritura si ya existe un fichero con el mismo nombre.
         * Mantiene el nombre original y añade sufijo incremental.
         */
        $contador = 1;
        while (file_exists($ruta_destino_fs)) {
            $info = pathinfo($nombre_original);
            $filename = $info['filename'] ?? 'archivo';
            $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

            $nombre_destino = $filename . "_" . $contador . $extension;
            $ruta_destino_fs = $uploads_dir_fs . $nombre_destino;
            $contador++;
        }

        if (move_uploaded_file($tmp, $ruta_destino_fs)) {
            $ruta_web = "/portal_pyme/uploads/" . $nombre_destino;

            /* Desactivar fotos anteriores */
            $query_update = "UPDATE perfil_fotos SET activa = 0 WHERE empleado_id = '$empleado_id'";
            $conn->query($query_update);

            /* Insertar nueva foto */
            $query_insert = "
                INSERT INTO perfil_fotos (empleado_id, nombre_original, nombre_guardado, ruta_web, activa)
                VALUES ('$empleado_id', '$nombre_original', '$nombre_destino', '$ruta_web', 1)
            ";
            $conn->query($query_insert);

            $popup_message = "Archivo subido correctamente.";
            $popup_type = "success";
        } else {
            $popup_message = "Error al subir el archivo.";
            $popup_type = "error";
        }
    } else {
        $popup_message = "No se ha seleccionado ningún archivo válido.";
        $popup_type = "error";
    }
}

/*
 * DATOS DEL EMPLEADO
 */
$query = "SELECT * FROM empleados WHERE id = '$empleado_id'";
$result = $conn->query($query);
$empleado = $result->fetch_assoc();

/*
 * FOTO DE PERFIL ACTIVA
 */
$foto_perfil_web = '';
$foto_perfil_nombre = '';

$query_foto = "
    SELECT nombre_original, nombre_guardado, ruta_web
    FROM perfil_fotos
    WHERE empleado_id = '$empleado_id' AND activa = 1
    ORDER BY fecha_subida DESC
    LIMIT 1
";
$result_foto = $conn->query($query_foto);

if ($result_foto && $result_foto->num_rows > 0) {
    $foto = $result_foto->fetch_assoc();
    $foto_perfil_web = $foto['ruta_web'];
    $foto_perfil_nombre = $foto['nombre_original'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<section class="page-header">
    <h2>Perfil del empleado</h2>
    <p>Información de la cuenta autenticada en el portal.</p>
</section>

<div class="grid">
    <div class="card">
        <h3>Imagen de perfil</h3>

        <div class="profile-photo-box">
            <?php if ($foto_perfil_web !== ''): ?>
                <img
                    src="<?php echo htmlspecialchars($foto_perfil_web); ?>"
                    alt="Foto de perfil"
                    class="profile-photo"
                >
                <p class="profile-photo-name"><?php echo htmlspecialchars($foto_perfil_nombre); ?></p>
            <?php else: ?>
                <div class="profile-photo-placeholder">Sin imagen</div>
                <p class="profile-photo-name">No se ha subido ninguna foto de perfil.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h3>Subir foto de perfil</h3>

        <form method="POST" enctype="multipart/form-data">
            <label for="foto">Seleccionar archivo</label>
            <input type="file" id="foto" name="foto">
            <input type="submit" name="upload" value="Subir archivo">
        </form>
    </div>
</div>

<div class="card">
    <h3>Datos del empleado</h3>

    <?php if ($empleado): ?>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <td><?php echo htmlspecialchars($empleado['id']); ?></td>
                </tr>
                <tr>
                    <th>Usuario</th>
                    <td><?php echo htmlspecialchars($empleado['username']); ?></td>
                </tr>
                <tr>
                    <th>Rol</th>
                    <td><?php echo htmlspecialchars($empleado['rol']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                </tr>
            </table>
        </div>
    <?php else: ?>
        <div class="notice">No se ha encontrado información del empleado.</div>
    <?php endif; ?>
</div>

<?php if ($popup_message !== ''): ?>
<div id="uploadModal" class="alert-overlay">
    <div class="alert-box <?php echo $popup_type === 'success' ? 'alert-success' : 'alert-error'; ?>">
        <div class="alert-header">
            <?php echo $popup_type === 'success' ? '✔ Éxito' : '✖ Error'; ?>
        </div>
        <div class="alert-body">
            <?php echo htmlspecialchars($popup_message); ?>
        </div>
        <div class="alert-footer">
            <button type="button" onclick="closeAlert()">Aceptar</button>
        </div>
    </div>
</div>

<script>
function closeAlert() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAlert();
    }
});

window.addEventListener('click', function(e) {
    const modal = document.getElementById('uploadModal');
    if (e.target === modal) {
        closeAlert();
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
