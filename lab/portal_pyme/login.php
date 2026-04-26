<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config/db.php';
include 'includes/header.php';
?>
<main class="content login-content">
    <div class="login-page">
        <div class="login-card">
            <h3>Inicio de sesión</h3>
            <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $user = $_POST['username'] ?? '';
                $pass = $_POST['password'] ?? '';

                // Vulnerabilidad didáctica: comprobación separada que permite enumeración por tiempo.
                $result = $conn->query("SELECT * FROM empleados WHERE username = '$user'");

                if ($result && $result->num_rows > 0) {
                    usleep(600000);

                    // Vulnerabilidad didáctica: SQLi en login.
                    $query = "SELECT * FROM empleados WHERE username = '$user' AND password = '$pass'";
                    $login = $conn->query($query);

                    if ($login && $login->num_rows > 0) {
                        $empleado = $login->fetch_assoc();
                        $_SESSION['empleado_id'] = $empleado['id'];
                        $_SESSION['username'] = $empleado['username'];
                        $_SESSION['rol'] = $empleado['rol'];
                        $_SESSION['email'] = $empleado['email'];
                        header("Location: /portal_pyme/perfil.php");
                        exit;
                    }
                }
                echo '<div class="notice">Usuario o contraseña incorrectos</div>';
            }
            ?>
            <form method="POST">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" autocomplete="off">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password">
                <input type="submit" value="Entrar">
            </form>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
