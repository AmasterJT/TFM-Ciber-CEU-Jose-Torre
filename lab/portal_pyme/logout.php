<?php
session_start();

/* Borrar variables */
$_SESSION = [];

/* Borrar cookie de sesión */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Destruir sesión */
session_destroy();

/* Redirigir */
header("Location: /portal_pyme/login.php");
exit;
?>
